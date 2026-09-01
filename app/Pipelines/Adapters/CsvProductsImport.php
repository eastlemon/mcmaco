<?php

namespace App\Pipelines\Adapters;

use App\Models\Ad;
use App\Models\Category;
use App\Pipelines\Contracts\ImportAdapter;
use App\Services\PhotoAttacher;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CsvProductsImport implements ImportAdapter
{
    public function read(array $config): \Generator
    {
        $path = $this->resolveCsvPath($config);

        $delimiter = $config['delimiter'] ?? ',';
        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle, 0, $delimiter);

        if (!$headers) {
            fclose($handle);
            throw new \RuntimeException('Empty CSV file');
        }

        // Normalize headers: lowercase, trim
        $headers = array_map(fn ($h) => trim(strtolower($h)), $headers);

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($row) === count($headers)) {
                yield array_combine($headers, $row);
            }
        }

        fclose($handle);
    }

    public function process(array $row, array $config): array
    {
        $title = $row['title'] ?? $row['name'] ?? null;
        if (!$title) {
            return ['action' => 'error', 'message' => 'Missing title'];
        }

        $sku = $row['sku'] ?? $row['article'] ?? null;
        $price = (int) ($row['price'] ?? 0);
        $stock = (int) ($row['stock'] ?? $row['quantity'] ?? 0);
        $description = $row['description'] ?? null;
        $condition = ($row['condition'] ?? 'new') === 'used' ? 'used' : 'new';
        $city = $row['city'] ?? null;
        $categoryName = $row['category'] ?? null;
        $weight = (int) ($row['weight'] ?? 0);

        // Resolve category
        $categoryId = null;
        if ($categoryName) {
            $categoryId = $this->resolveCategory($categoryName, $config);
        }

        // Find by SKU or create
        $ad = null;
        if ($sku) {
            $ad = Ad::where('sku', $sku)->first();
        }

        $data = [
            'title' => $title,
            'description' => $description ?? '',
            'price' => $price,
            'stock' => $stock,
            'condition' => $condition,
            'city' => $city ?? '',
            'category_id' => $categoryId,
            'weight' => $weight,
            'status' => 'active',
        ];

        if ($ad) {
            $ad->update(array_filter($data, fn ($v) => $v !== null));
            $action = 'updated';
        } else {
            $data['sku'] = $sku;
            $data['user_id'] = $config['default_user_id'] ?? 1;
            $ad = Ad::create($data);
            $action = 'created';
        }

        $message = ucfirst($action) . ": {$title} (SKU: {$sku})";
        $result = ['action' => $action, 'message' => $message, 'photos' => 0];

        // Attach photos from the extracted run directory (photos/{SKU}/...)
        $photosDir = $config['photos_dir'] ?? null;

        if ($sku && $photosDir) {
            $dir = $this->findProductPhotosDir($photosDir, $sku);

            if ($dir !== null) {
                $photos = app(PhotoAttacher::class)->attach(
                    $ad,
                    $dir,
                    $config['photo_strategy'] ?? PhotoAttacher::STRATEGY_REPLACE,
                );

                $result['photos'] = $photos['attached'];

                if ($photos['attached'] > 0) {
                    $result['message'] .= ", фото: +{$photos['attached']}";
                }

                if ($photos['skipped'] !== []) {
                    $count = count($photos['skipped']);
                    $result['message'] .= " (пропущено фото: {$count})";
                }
            }
        }

        return $result;
    }

    public function configSchema(): array
    {
        return [
            'csv_file' => [
                'type' => 'file',
                'label' => 'CSV со списком товаров',
                'accepted' => ['.csv'],
                'max_size' => 20480, // KB
                'directory' => 'pipeline-uploads',
                'help' => 'title, sku, price, stock, description, category, condition, city, weight',
            ],
            'file_path' => [
                'type' => 'text',
                'label' => 'Или путь к CSV на сервере',
                'placeholder' => '/var/www/html/storage/app/imports/products.csv',
            ],
            'photos_zip' => [
                'type' => 'file',
                'label' => 'ZIP-архив с фото',
                'accepted' => ['.zip'],
                'max_size' => 204800, // KB (200 MB)
                'directory' => 'pipeline-uploads',
                'help' => 'photos/{SKU}/1-cover.jpg — номер задаёт порядок, суффикс «-cover» обложку',
            ],
            'photo_strategy' => [
                'type' => 'select',
                'label' => 'Стратегия фото при повторном импорте',
                'options' => [
                    PhotoAttacher::STRATEGY_REPLACE => 'Заменять набор фото',
                    PhotoAttacher::STRATEGY_SKIP => 'Не трогать фото существующих товаров',
                ],
                'default' => PhotoAttacher::STRATEGY_REPLACE,
            ],
            'delimiter' => [
                'type' => 'select',
                'label' => 'Разделитель',
                'options' => [';' => ';', ',' => ',', '\t' => 'Tab'],
                'default' => ',',
            ],
            'default_user_id' => [
                'type' => 'text',
                'label' => 'ID продавца по умолчанию',
                'default' => 1,
            ],
            'auto_create_categories' => [
                'type' => 'toggle',
                'label' => 'Автосоздание категорий',
                'default' => true,
            ],
        ];
    }

    /**
     * Resolve the CSV location: uploaded through the pipeline form
     * (stored on the "local" disk) or a plain server path.
     */
    private function resolveCsvPath(array $config): string
    {
        $csvFile = $config['csv_file'] ?? null;

        if ($csvFile) {
            $disk = Storage::disk('local');

            if (! $disk->exists($csvFile)) {
                throw new \RuntimeException("CSV file not found: {$csvFile}");
            }

            return $disk->path($csvFile);
        }

        $path = $config['file_path'] ?? null;

        if (!$path || !file_exists($path)) {
            throw new \RuntimeException("CSV file not found: {$path}");
        }

        return $path;
    }

    /**
     * Find the photos/{SKU} folder; exact match first, case-insensitive fallback.
     */
    private function findProductPhotosDir(string $baseDir, string $sku): ?string
    {
        $direct = rtrim($baseDir, '/') . '/' . $sku;

        if (is_dir($direct)) {
            return $direct;
        }

        $lower = mb_strtolower($sku);

        foreach (glob(rtrim($baseDir, '/') . '/*', GLOB_ONLYDIR) ?: [] as $dir) {
            if (mb_strtolower(basename($dir)) === $lower) {
                return $dir;
            }
        }

        return null;
    }

    private function resolveCategory(string $name, array $config): ?int
    {
        $category = Category::where('name', $name)->first();
        if ($category) {
            return $category->id;
        }

        // Auto-create category if configured
        if (($config['auto_create_categories'] ?? false)) {
            return Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
            ])->id;
        }

        return null;
    }
}
<?php

namespace App\Pipelines\Adapters;

use App\Models\Ad;
use App\Models\Category;
use App\Pipelines\Contracts\ImportAdapter;
use Illuminate\Support\Str;

class CsvProductsImport implements ImportAdapter
{
    public function read(array $config): \Generator
    {
        $path = $config['file_path'] ?? null;
        if (!$path || !file_exists($path)) {
            throw new \RuntimeException("CSV file not found: {$path}");
        }

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

        if ($ad) {
            $ad->update(array_filter([
                'title' => $title,
                'description' => $description,
                'price' => $price,
                'stock' => $stock,
                'condition' => $condition,
                'city' => $city,
                'category_id' => $categoryId,
                'weight' => $weight,
                'status' => 'active',
            ], fn ($v) => $v !== null));

            return ['action' => 'updated', 'message' => "Updated: {$title} (SKU: {$sku})"];
        }

        Ad::create(array_filter([
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'stock' => $stock,
            'sku' => $sku,
            'condition' => $condition,
            'city' => $city,
            'category_id' => $categoryId,
            'weight' => $weight,
            'status' => 'active',
            'user_id' => $config['default_user_id'] ?? 1,
        ], fn ($v) => $v !== null));

        return ['action' => 'created', 'message' => "Created: {$title} (SKU: {$sku})"];
    }

    public function configSchema(): array
    {
        return [
            'file_path' => [
                'type' => 'text',
                'label' => 'Путь к CSV-файлу',
                'placeholder' => '/storage/imports/products.csv',
                'required' => true,
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
        ];
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
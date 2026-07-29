<?php

namespace App\Pipelines\Adapters;

use App\Models\Order;
use App\Pipelines\Contracts\ExportAdapter;
use Illuminate\Support\Collection;

class OrdersExport implements ExportAdapter
{
    public function query(array $config): Collection
    {
        $statuses = $config['statuses'] ?? null;
        $daysBack = (int) ($config['days_back'] ?? 30);

        $q = Order::with('items', 'deliveryMethod')
            ->where('created_at', '>=', now()->subDays($daysBack));

        if ($statuses) {
            $q->whereIn('status', (array) $statuses);
        }

        return $q->orderBy('created_at')->get();
    }

    public function format(mixed $order, array $config): array
    {
        return [
            'order_number' => $order->order_number,
            'date' => $order->created_at?->format('Y-m-d H:i:s'),
            'status' => $order->status,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_email' => $order->customer_email ?? '',
            'delivery' => $order->delivery_method_label,
            'delivery_address' => $order->delivery_address ?? '',
            'tracking' => $order->tracking_number ?? '',
            'items_total' => $order->items_total,
            'delivery_cost' => $order->delivery_cost,
            'total' => $order->total,
            'comment' => str_replace(["\n", "\r"], ' ', $order->comment ?? ''),
        ];
    }

    public function write(Collection $items, array $config): string
    {
        $format = $config['format'] ?? 'csv';
        $dir = storage_path('app/exports');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'orders_' . now()->format('Y-m-d_His') . '.' . $format;
        $path = "{$dir}/{$filename}";

        if ($items->isEmpty()) {
            file_put_contents($path, '');
            return $path;
        }

        $rows = $items->map(fn ($item) => $this->format($item, $config))->all();
        $headers = array_keys($rows[0]);

        $handle = fopen($path, 'w');

        if ($format === 'csv') {
            $delimiter = $config['delimiter'] ?? ';';
            fputcsv($handle, $headers, $delimiter);
            foreach ($rows as $row) {
                fputcsv($handle, $row, $delimiter);
            }
        }

        fclose($handle);

        return $path;
    }

    public function configSchema(): array
    {
        return [
            'statuses' => [
                'type' => 'multiselect',
                'label' => 'Статусы заказов',
                'options' => Order::STATUSES,
                'default' => null,
            ],
            'days_back' => [
                'type' => 'number',
                'label' => 'За сколько дней',
                'default' => 30,
            ],
            'delimiter' => [
                'type' => 'select',
                'label' => 'Разделитель CSV',
                'options' => [';' => ';', ',' => ','],
                'default' => ';',
            ],
        ];
    }
}
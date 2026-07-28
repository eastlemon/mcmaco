<?php

namespace Database\Seeders;

use App\Models\DeliveryMethod;
use Illuminate\Database\Seeder;

class DeliveryMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'code' => 'pickup',
                'name' => 'Самовывоз',
                'type' => 'pickup',
                'base_price' => 0,
                'price_per_kg' => 0,
                'tracking_url' => null,
                'description' => 'Самовывоз со склада мcmaco. Бесплатно.',
                'sort_order' => 1,
            ],
            [
                'code' => 'cdek_pvz',
                'name' => 'СДЭК — пункт выдачи',
                'type' => 'warehouse',
                'base_price' => 250,
                'price_per_kg' => 30,
                'tracking_url' => 'https://www.cdek.ru/ru/tracking?order_number={track}',
                'description' => 'Доставка в пункт выдачи СДЭК. 2–5 дней.',
                'sort_order' => 2,
            ],
            [
                'code' => 'cdek_courier',
                'name' => 'СДЭК — курьер',
                'type' => 'courier',
                'base_price' => 400,
                'price_per_kg' => 40,
                'tracking_url' => 'https://www.cdek.ru/ru/tracking?order_number={track}',
                'description' => 'Курьерская доставка СДЭК до двери. 2–5 дней.',
                'sort_order' => 3,
            ],
            [
                'code' => 'post',
                'name' => 'Почта России',
                'type' => 'warehouse',
                'base_price' => 200,
                'price_per_kg' => 25,
                'tracking_url' => 'https://www.pochta.ru/tracking#{track}',
                'description' => 'Доставка Почтой России. 5–14 дней.',
                'sort_order' => 4,
            ],
            [
                'code' => 'local_courier',
                'name' => 'Курьер по городу',
                'type' => 'courier',
                'base_price' => 350,
                'price_per_kg' => 0,
                'tracking_url' => null,
                'description' => 'Доставка курьером в пределах города. 1–2 дня.',
                'sort_order' => 5,
            ],
        ];

        foreach ($methods as $method) {
            DeliveryMethod::updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}
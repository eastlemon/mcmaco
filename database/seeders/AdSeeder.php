<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->where('email', 'demo@example.com')->first();
        $categories = Category::query()->pluck('id')->all();

        if (! $user || empty($categories)) {
            return;
        }

        Ad::factory()->count(20)->create([
            'user_id' => $user->id,
            'category_id' => $categories[array_rand($categories)],
            'status' => 'active',
            'condition' => 'used',
            'city' => 'Tomsk',
        ]);
    }
}

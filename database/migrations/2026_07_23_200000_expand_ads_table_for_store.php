<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title');
            $table->string('sku')->nullable()->unique()->after('slug');
            $table->unsignedInteger('stock')->default(1)->after('price');
            $table->boolean('is_featured')->default(false)->after('status');
            $table->string('weight', 20)->nullable()->after('city');
            $table->string('dimensions', 40)->nullable()->after('weight');
            $table->string('meta_title')->nullable()->after('views');
            $table->text('meta_description')->nullable()->after('meta_title');
        });

        // Generate slugs for existing ads
        \App\Models\Ad::withTrashed()->each(function (\App\Models\Ad $ad) {
            $ad->update(['slug' => \Illuminate\Support\Str::slug($ad->title) . '-' . $ad->id]);
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn([
                'slug', 'sku', 'stock', 'is_featured',
                'weight', 'dimensions', 'meta_title', 'meta_description',
            ]);
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type')->default('warehouse');
            $table->unsignedInteger('base_price')->default(0);
            $table->unsignedInteger('price_per_kg')->default(0);
            $table->string('tracking_url')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Add delivery fields to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tracking_number')->nullable()->after('delivery_address');
            $table->foreignId('delivery_method_id')->nullable()->after('delivery_method')
                ->constrained('delivery_methods')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['delivery_method_id']);
            $table->dropColumn(['tracking_number', 'delivery_method_id']);
        });

        Schema::dropIfExists('delivery_methods');
    }
};
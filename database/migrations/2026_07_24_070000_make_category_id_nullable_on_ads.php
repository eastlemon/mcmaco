<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Can't easily revert to NOT NULL if there are null values
    }
};

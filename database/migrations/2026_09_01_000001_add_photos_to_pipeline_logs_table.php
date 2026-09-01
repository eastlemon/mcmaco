<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pipeline_logs', function (Blueprint $table) {
            $table->unsignedInteger('photos')->default(0)->after('errors');
        });
    }

    public function down(): void
    {
        Schema::table('pipeline_logs', function (Blueprint $table) {
            $table->dropColumn('photos');
        });
    }
};
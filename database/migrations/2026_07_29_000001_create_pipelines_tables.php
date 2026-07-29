<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipelines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('import'); // import | export
            $table->string('adapter'); // adapter class code: csv_products, orders_export, etc.
            $table->string('format')->default('csv'); // csv | xml | json
            $table->json('config')->nullable(); // adapter-specific config
            $table->string('schedule')->nullable(); // cron expression
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });

        Schema::create('pipeline_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pipeline_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->text('message')->nullable();
            $table->unsignedInteger('processed')->default(0);
            $table->unsignedInteger('created')->default(0);
            $table->unsignedInteger('updated')->default(0);
            $table->unsignedInteger('errors')->default(0);
            $table->json('details')->nullable();
            $table->timestamps();

            $table->index(['pipeline_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_logs');
        Schema::dropIfExists('pipelines');
    }
};
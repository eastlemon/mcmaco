<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Log of all simulated events — every bot visit, message, order creation
     * writes a row here. This powers the real-time admin dashboard and
     * debugging of the simulation engine itself.
     */
    public function up(): void
    {
        Schema::create('simulated_events', function (Blueprint $table) {
            $table->id();
            $table->string('type');              // visit, message, order, status_change, ...
            $table->foreignId('simulated_user_id')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->foreignId('ad_id')->nullable()
                  ->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()
                  ->constrained()->nullOnDelete();
            $table->foreignId('chat_id')->nullable()
                  ->constrained()->nullOnDelete();
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['type', 'occurred_at']);
            $table->index('simulated_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulated_events');
    }
};

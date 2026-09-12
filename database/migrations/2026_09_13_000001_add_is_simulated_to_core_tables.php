<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add is_simulated flag to user-facing tables so simulated data never
     * pollutes real production data. Indexes support efficient filtering
     * in admin queries ("only real" / "only simulated").
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_simulated')->default(false)->index()->after('is_admin');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_simulated')->default(false)->index()->after('is_quick_order');
        });

        Schema::table('chats', function (Blueprint $table) {
            $table->boolean('is_simulated')->default(false)->index()->after('last_message_at');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('is_simulated')->default(false)->index()->after('read_at');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['is_simulated']);
            $table->dropColumn('is_simulated');
        });

        Schema::table('chats', function (Blueprint $table) {
            $table->dropIndex(['is_simulated']);
            $table->dropColumn('is_simulated');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['is_simulated']);
            $table->dropColumn('is_simulated');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_simulated']);
            $table->dropColumn('is_simulated');
        });
    }
};

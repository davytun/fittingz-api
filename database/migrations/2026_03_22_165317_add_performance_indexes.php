<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Users table
        Schema::table('users', function (Blueprint $table) {
            $table->index('email');
        });

        // Clients table
        Schema::table('clients', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
        });

        // Measurements table
        Schema::table('measurements', function (Blueprint $table) {
            $table->index(['client_id', 'measurement_date']);
            $table->index(['user_id', 'created_at']);
        });

        // Orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
            $table->index(['client_id', 'status']);
            $table->index('created_at');
        });

        // Payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['user_id', 'payment_date']);
            $table->index(['order_id', 'created_at']);
        });

        // Styles table
        Schema::table('styles', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('measurements', function (Blueprint $table) {
            $table->dropIndex(['client_id', 'measurement_date']);
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['client_id', 'status']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'payment_date']);
            $table->dropIndex(['order_id', 'created_at']);
        });

        Schema::table('styles', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
        });
    }
};
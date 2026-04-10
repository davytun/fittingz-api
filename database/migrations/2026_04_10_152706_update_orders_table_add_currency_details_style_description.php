<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate existing rows before altering the enum
        DB::table('orders')->where('status', 'pending')->update(['status' => 'pending_payment']);

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('currency', 3)->default('NGN')->after('total_amount');
            $table->json('details')->nullable()->after('description');
            $table->text('style_description')->nullable()->after('details');
            $table->enum('status', ['pending_payment', 'in_progress', 'completed', 'delivered', 'cancelled'])
                  ->default('pending_payment')
                  ->after('style_description');
        });
    }

    public function down(): void
    {
        DB::table('orders')->where('status', 'pending_payment')->update(['status' => 'pending']);

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['currency', 'details', 'style_description', 'status']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'in_progress', 'completed', 'delivered', 'cancelled'])
                  ->default('pending')
                  ->after('total_amount');
        });
    }
};

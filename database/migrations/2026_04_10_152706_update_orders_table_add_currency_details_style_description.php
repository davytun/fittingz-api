<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('currency', 3)->default('NGN')->after('total_amount');
            $table->json('details')->nullable()->after('description');
            $table->text('style_description')->nullable()->after('details');
        });

        // Expand the enum to include the legacy 'pending' value alongside the new
        // 'pending_payment' value, so existing rows remain valid during migration.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(
                "ALTER TABLE orders MODIFY COLUMN status
                ENUM('pending','pending_payment','in_progress','completed','delivered','cancelled')
                NOT NULL DEFAULT 'pending_payment'"
            );
        }

        // Atomically migrate any legacy 'pending' rows to 'pending_payment'.
        DB::transaction(function () {
            DB::table('orders')->where('status', 'pending')->update(['status' => 'pending_payment']);
        });

        // Contract the enum to its final shape, removing the legacy 'pending' value.
        // MODIFY COLUMN preserves all existing indexes on the column — no re-creation needed.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(
                "ALTER TABLE orders MODIFY COLUMN status
                ENUM('pending_payment','in_progress','completed','delivered','cancelled')
                NOT NULL DEFAULT 'pending_payment'"
            );
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['currency', 'details', 'style_description']);
        });

        // Expand enum so both 'pending' and 'pending_payment' are valid before the rollback.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(
                "ALTER TABLE orders MODIFY COLUMN status
                ENUM('pending','pending_payment','in_progress','completed','delivered','cancelled')
                NOT NULL DEFAULT 'pending'"
            );
        }

        // Atomically restore 'pending_payment' rows back to the legacy 'pending' value.
        DB::transaction(function () {
            DB::table('orders')->where('status', 'pending_payment')->update(['status' => 'pending']);
        });

        // Contract the enum back to the original shape, removing 'pending_payment'.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(
                "ALTER TABLE orders MODIFY COLUMN status
                ENUM('pending','in_progress','completed','delivered','cancelled')
                NOT NULL DEFAULT 'pending'"
            );
        }
    }
};

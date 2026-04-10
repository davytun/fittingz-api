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
            $table->enum('status_new', ['pending_payment', 'in_progress', 'completed', 'delivered', 'cancelled'])
                  ->default('pending_payment')
                  ->after('style_description');
        });

        DB::table('orders')->where('status', 'pending')->update(['status_new' => 'pending_payment']);
        DB::table('orders')->whereNotIn('status', ['pending'])->update(['status_new' => DB::raw('status')]);

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['currency', 'details', 'style_description']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status_old', ['pending', 'in_progress', 'completed', 'delivered', 'cancelled'])
                  ->default('pending')
                  ->after('total_amount');
        });

        DB::table('orders')->where('status', 'pending_payment')->update(['status_old' => 'pending']);
        DB::table('orders')->whereNotIn('status', ['pending_payment'])->update(['status_old' => DB::raw('status')]);

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('status_old', 'status');
        });
    }
};

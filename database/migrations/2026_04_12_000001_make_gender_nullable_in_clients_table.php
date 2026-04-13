<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable()->change();
        });
    }

    public function down(): void
    {
        // Backfill NULLs before reinstating NOT NULL constraint
        DB::table('clients')->whereNull('gender')->update(['gender' => 'Other']);

        Schema::table('clients', function (Blueprint $table) {
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable(false)->change();
        });
    }
};

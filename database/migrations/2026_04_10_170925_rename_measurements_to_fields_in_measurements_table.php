<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('measurements', function (Blueprint $table) {
            $table->renameColumn('measurements', 'fields');
        });
    }

    public function down(): void
    {
        Schema::table('measurements', function (Blueprint $table) {
            $table->renameColumn('fields', 'measurements');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('measurements', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('measurement_date');
            $table->index(['client_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::table('measurements', function (Blueprint $table) {
            $table->dropIndex(['client_id', 'is_default']);
            $table->dropColumn('is_default');
        });
    }
};
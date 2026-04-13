<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('style_images', function (Blueprint $table) {
            $table->string('id', 30)->primary();
            $table->string('admin_id')->nullable();
            $table->string('client_id')->nullable();
            $table->string('image_url');
            $table->string('public_id')->unique();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');

            $table->index('admin_id');
            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('style_images');
    }
};

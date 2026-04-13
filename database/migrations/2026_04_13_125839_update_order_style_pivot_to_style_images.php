<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old pivot that references the styles table
        Schema::dropIfExists('order_style');

        // Create new pivot referencing style_images
        Schema::create('order_style_image', function (Blueprint $table) {
            $table->uuid('order_id');
            $table->string('style_image_id', 30);
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('style_image_id')->references('id')->on('style_images')->onDelete('cascade');

            $table->primary(['order_id', 'style_image_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_style_image');

        Schema::create('order_style', function (Blueprint $table) {
            $table->uuid('order_id');
            $table->uuid('style_id');
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('style_id')->references('id')->on('styles')->onDelete('cascade');

            $table->primary(['order_id', 'style_id']);
        });
    }
};

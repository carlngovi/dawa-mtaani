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
        Schema::create('saved_cart_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('saved_cart_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('wholesale_facility_id');
            $table->integer('quantity');
            $table->timestamps();

            $table->foreign('saved_cart_id')->references('id')->on('saved_carts')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_cart_lines');
    }
};

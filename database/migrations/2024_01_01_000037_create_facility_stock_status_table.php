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
        Schema::create('facility_stock_status', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('wholesale_facility_id');
            $table->unsignedBigInteger('product_id');
            $table->enum('stock_status', ['IN_STOCK', 'LOW_STOCK', 'OUT_OF_STOCK'])->default('IN_STOCK');
            $table->integer('stock_quantity')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();

            $table->unique(['wholesale_facility_id', 'product_id']);
            $table->foreign('wholesale_facility_id')->references('id')->on('facilities');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_stock_status');
    }
};

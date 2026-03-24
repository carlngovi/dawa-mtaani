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
        Schema::create('wholesale_price_lists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('wholesale_facility_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('unit_price', 10, 2);
            $table->date('effective_from');
            $table->date('expires_at')->nullable();
            $table->enum('stock_status', ['IN_STOCK', 'LOW_STOCK', 'OUT_OF_STOCK'])->default('IN_STOCK')->index();
            $table->integer('stock_quantity')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->foreign('wholesale_facility_id')->references('id')->on('facilities');
            $table->foreign('product_id')->references('id')->on('products');
            $table->index(['wholesale_facility_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wholesale_price_lists');
    }
};

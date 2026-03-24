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
        Schema::create('order_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('wholesale_facility_id')->index();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('price_list_id');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->boolean('premium_applied')->default(false);
            $table->decimal('premium_amount', 10, 2)->default(0);
            $table->decimal('line_total', 12, 2);
            $table->enum('payment_type', ['CREDIT', 'CASH', 'OFF_NETWORK_CASH']);
            $table->unsignedBigInteger('tranche_id')->nullable();
            $table->unsignedBigInteger('tier_id')->nullable();
            $table->unsignedBigInteger('placer_user_id');
            $table->unsignedBigInteger('delivery_facility_id')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_lines');
    }
};

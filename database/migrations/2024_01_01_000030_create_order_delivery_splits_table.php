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
        Schema::create('order_delivery_splits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('order_line_id');
            $table->unsignedBigInteger('delivery_facility_id');
            $table->text('delivery_address');
            $table->enum('status', ['PENDING', 'DISPATCHED', 'DELIVERED', 'DISPUTED'])->default('PENDING');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('order_line_id')->references('id')->on('order_lines')->cascadeOnDelete();
            $table->foreign('delivery_facility_id')->references('id')->on('facilities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_delivery_splits');
    }
};

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
        Schema::create('dispatch_triggers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('split_line_id')->nullable()->index();
            $table->unsignedBigInteger('triggered_by_facility_id');
            $table->unsignedBigInteger('triggered_by_user_id');
            $table->timestamp('triggered_at');
            $table->unsignedBigInteger('courier_facility_id')->nullable();
            $table->timestamp('courier_notified_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders');
            $table->foreign('split_line_id')->references('id')->on('order_delivery_splits');
            $table->foreign('triggered_by_facility_id')->references('id')->on('facilities');
            $table->foreign('triggered_by_user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispatch_triggers');
    }
};

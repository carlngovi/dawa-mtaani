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
        Schema::create('delivery_confirmations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('split_line_id')->nullable()->index();
            $table->unsignedBigInteger('logistics_facility_id');
            $table->timestamp('delivered_at');
            $table->string('pod_photo_path', 500);
            $table->timestamp('confirmation_clock_started_at');
            $table->timestamp('confirmation_deadline_at')->index();
            $table->timestamp('confirmed_at')->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->enum('confirmation_type', ['RETAIL_CONFIRMED', 'AUTO_TRIGGERED', 'DISPUTED_RESOLVED'])->nullable();
            $table->timestamp('auto_trigger_fired_at')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders');
            $table->foreign('split_line_id')->references('id')->on('order_delivery_splits');
            $table->foreign('logistics_facility_id')->references('id')->on('facilities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_confirmations');
    }
};

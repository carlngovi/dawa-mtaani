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
        Schema::create('offline_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('facility_id')->index();
            $table->string('qr_code', 255)->unique();
            $table->json('order_data');
            $table->enum('status', ['PRINTED', 'FULFILLED', 'SYNCED', 'CANCELLED'])->default('PRINTED');
            $table->timestamp('printed_at')->useCurrent();
            $table->timestamp('synced_at')->nullable();
            $table->unsignedBigInteger('synced_by')->nullable();
            $table->unsignedBigInteger('synced_order_id')->nullable();
            $table->timestamp('sync_confirmation_sent_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_orders');
    }
};

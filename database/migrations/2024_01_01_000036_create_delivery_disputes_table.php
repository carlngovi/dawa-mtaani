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
        Schema::create('delivery_disputes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('delivery_confirmation_id');
            $table->unsignedBigInteger('raised_by');
            $table->timestamp('raised_at');
            $table->enum('reason', ['NOT_RECEIVED', 'PARTIAL_DELIVERY', 'WRONG_ITEMS', 'DAMAGED_GOODS']);
            $table->string('photo_path', 500)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['OPEN', 'UNDER_REVIEW', 'RESOLVED'])->default('OPEN')->index();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->enum('resolution', ['PAYMENT_CONFIRMED', 'ORDER_CANCELLED', 'PARTIAL_PAYMENT'])->nullable();
            $table->decimal('resolved_amount', 12, 2)->nullable();
            $table->timestamp('sla_deadline_at')->index();
            $table->boolean('sla_breached')->default(false);
            $table->timestamps();

            $table->foreign('delivery_confirmation_id')->references('id')->on('delivery_confirmations');
            $table->foreign('raised_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_disputes');
    }
};

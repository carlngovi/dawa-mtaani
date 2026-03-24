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
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('ulid', 26)->unique();
            $table->unsignedBigInteger('retail_facility_id');
            $table->unsignedBigInteger('placed_by_user_id');
            $table->boolean('is_group_order')->default(false);
            $table->boolean('is_network_member');
            $table->enum('order_type', ['CREDIT', 'CASH', 'MIXED', 'OFF_NETWORK_CASH']);
            $table->enum('source_channel', ['WEB', 'WHATSAPP', 'OFFLINE_QR'])->default('WEB');
            $table->enum('status', ['PENDING', 'CONFIRMED', 'PICKING', 'PACKED', 'DISPATCHED', 'DELIVERED', 'DISPUTED', 'CANCELLED']);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('credit_amount', 12, 2)->default(0);
            $table->decimal('cash_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->enum('copay_status', ['NOT_REQUIRED', 'PENDING', 'FAILED', 'PAID', 'ESCALATED'])->default('NOT_REQUIRED');
            $table->timestamp('copay_escalated_at')->nullable();
            $table->unsignedBigInteger('copay_override_by')->nullable();
            $table->text('copay_override_reason')->nullable();
            $table->integer('copay_override_additional_attempts')->nullable();
            $table->string('manual_payment_reference', 100)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('retail_facility_id')->references('id')->on('facilities');
            $table->foreign('placed_by_user_id')->references('id')->on('users');
            $table->index(['retail_facility_id', 'status', 'created_at']);
            $table->index('source_channel');
            $table->index('is_network_member');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

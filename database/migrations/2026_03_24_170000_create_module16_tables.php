<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 50)->unique();
            $table->enum('discount_type', ['PERCENTAGE_OFF', 'FIXED_AMOUNT_OFF', 'BUY_X_GET_Y']);
            $table->decimal('discount_value', 8, 2);
            $table->decimal('min_order_value', 8, 2)->nullable();
            $table->integer('buy_quantity')->nullable();
            $table->integer('get_quantity')->nullable();
            $table->boolean('is_automatic')->default(false);
            $table->json('auto_trigger_condition')->nullable();
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->integer('usage_cap_total')->nullable();
            $table->integer('usage_cap_per_patient')->nullable();
            $table->boolean('stackable')->default(false);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
        });

        Schema::create('patient_baskets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('patient_phone', 20);
            $table->unsignedBigInteger('facility_id');
            $table->string('session_token', 100)->unique();
            $table->timestamp('reserved_until')->nullable();
            $table->timestamps();

            $table->foreign('facility_id')->references('id')->on('facilities');
            $table->index(['patient_phone', 'facility_id']);
        });

        Schema::create('patient_basket_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('basket_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->timestamp('added_at');
            $table->timestamps();

            $table->foreign('basket_id')->references('id')->on('patient_baskets')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products');
        });

        Schema::create('patient_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('ulid', 26)->unique();
            $table->string('patient_phone', 20);
            $table->string('patient_name', 255)->nullable();
            $table->unsignedBigInteger('facility_id');
            $table->enum('status', [
                'PAYMENT_PENDING', 'CONFIRMED', 'PREPARING',
                'READY', 'COLLECTED', 'CANCELLED', 'REJECTED',
            ])->default('PAYMENT_PENDING');
            $table->decimal('subtotal_amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('platform_fee_pct', 5, 2);
            $table->decimal('platform_fee_amount', 8, 2);
            $table->decimal('facility_net_amount', 10, 2);
            $table->unsignedBigInteger('promo_code_id')->nullable();
            $table->timestamp('collection_window_start')->nullable();
            $table->timestamp('collection_window_end')->nullable();
            $table->string('mpesa_checkout_request_id', 100)->unique()->nullable();
            $table->string('mpesa_receipt_number', 100)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->foreign('facility_id')->references('id')->on('facilities');
            $table->foreign('promo_code_id')->references('id')->on('promo_codes')->nullOnDelete();
            $table->index(['facility_id', 'status']);
            $table->index(['patient_phone', 'status']);
        });

        Schema::create('patient_order_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('patient_order_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->decimal('unit_price', 8, 2);
            $table->decimal('line_discount', 8, 2)->default(0);
            $table->decimal('line_total', 10, 2);
            $table->timestamps();

            $table->foreign('patient_order_id')->references('id')->on('patient_orders')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products');
        });

        Schema::create('promo_code_usages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('promo_code_id');
            $table->string('patient_phone', 20);
            $table->unsignedBigInteger('patient_order_id');
            $table->timestamp('used_at');
            $table->timestamps();

            $table->foreign('promo_code_id')->references('id')->on('promo_codes');
            $table->foreign('patient_order_id')->references('id')->on('patient_orders');
        });

        Schema::create('patient_favourites', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('patient_phone', 20);
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('facility_id')->nullable();
            $table->timestamp('added_at');

            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('facility_id')->references('id')->on('facilities')->nullOnDelete();
            $table->unique(['patient_phone', 'product_id']);
        });

        Schema::create('basket_abandonment_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('patient_phone', 20)->nullable();
            $table->unsignedBigInteger('facility_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->timestamp('abandoned_at');

            $table->foreign('facility_id')->references('id')->on('facilities');
            $table->foreign('product_id')->references('id')->on('products');
        });

        Schema::create('settlement_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('facility_id');
            $table->date('settlement_date');
            $table->decimal('gross_amount', 12, 2);
            $table->decimal('platform_fee', 10, 2);
            $table->decimal('net_amount', 12, 2);
            $table->integer('order_count');
            $table->boolean('is_network_member')->default(false);
            $table->string('mpesa_b2c_reference', 100)->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->foreign('facility_id')->references('id')->on('facilities');
            $table->index(['facility_id', 'settlement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_records');
        Schema::dropIfExists('basket_abandonment_log');
        Schema::dropIfExists('patient_favourites');
        Schema::dropIfExists('promo_code_usages');
        Schema::dropIfExists('patient_order_lines');
        Schema::dropIfExists('patient_orders');
        Schema::dropIfExists('patient_basket_lines');
        Schema::dropIfExists('patient_baskets');
        Schema::dropIfExists('promo_codes');
    }
};

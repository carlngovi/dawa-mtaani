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
        Schema::table('patient_orders', function (Blueprint $table) {
            // M-Pesa payment tracking fields
            if (!Schema::hasColumn('patient_orders', 'mpesa_merchant_request_id')) {
                $table->string('mpesa_merchant_request_id', 100)->nullable()->after('status');
            }
            
            if (!Schema::hasColumn('patient_orders', 'mpesa_checkout_request_id')) {
                $table->string('mpesa_checkout_request_id', 100)->nullable()->after('mpesa_merchant_request_id');
            }
            
            if (!Schema::hasColumn('patient_orders', 'mpesa_receipt_number')) {
                $table->string('mpesa_receipt_number', 50)->nullable()->after('mpesa_checkout_request_id');
            }
            
            if (!Schema::hasColumn('patient_orders', 'mpesa_amount')) {
                $table->decimal('mpesa_amount', 12, 2)->nullable()->after('mpesa_receipt_number');
            }
            
            if (!Schema::hasColumn('patient_orders', 'mpesa_phone')) {
                $table->string('mpesa_phone', 20)->nullable()->after('mpesa_amount');
            }
            
            if (!Schema::hasColumn('patient_orders', 'mpesa_result_code')) {
                $table->integer('mpesa_result_code')->nullable()->after('mpesa_phone');
            }
            
            if (!Schema::hasColumn('patient_orders', 'mpesa_result_desc')) {
                $table->text('mpesa_result_desc')->nullable()->after('mpesa_result_code');
            }
            
            if (!Schema::hasColumn('patient_orders', 'mpesa_paid_at')) {
                $table->timestamp('mpesa_paid_at')->nullable()->after('mpesa_result_desc');
            }
            
            // Payment status fields
            if (!Schema::hasColumn('patient_orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('mpesa_paid_at');
            }
            
            if (!Schema::hasColumn('patient_orders', 'failed_at')) {
                $table->timestamp('failed_at')->nullable()->after('paid_at');
            }
            
            if (!Schema::hasColumn('patient_orders', 'payment_failure_reason')) {
                $table->text('payment_failure_reason')->nullable()->after('failed_at');
            }
            
            // Order financial fields
            if (!Schema::hasColumn('patient_orders', 'subtotal_amount')) {
                $table->decimal('subtotal_amount', 12, 2)->nullable()->after('payment_failure_reason');
            }
            
            if (!Schema::hasColumn('patient_orders', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('subtotal_amount');
            }
            
            if (!Schema::hasColumn('patient_orders', 'total_amount')) {
                $table->decimal('total_amount', 12, 2)->nullable()->after('discount_amount');
            }
            
            if (!Schema::hasColumn('patient_orders', 'platform_fee_pct')) {
                $table->decimal('platform_fee_pct', 5, 2)->default(3.00)->after('total_amount');
            }
            
            if (!Schema::hasColumn('patient_orders', 'platform_fee_amount')) {
                $table->decimal('platform_fee_amount', 12, 2)->default(0)->after('platform_fee_pct');
            }
            
            if (!Schema::hasColumn('patient_orders', 'facility_net_amount')) {
                $table->decimal('facility_net_amount', 12, 2)->default(0)->after('platform_fee_amount');
            }
            
            // Promo code field
            if (!Schema::hasColumn('patient_orders', 'promo_code_id')) {
                $table->unsignedBigInteger('promo_code_id')->nullable()->after('facility_net_amount');
                $table->foreign('promo_code_id')->references('id')->on('promo_codes')->onDelete('set null');
            }
            
            // Collected timestamp
            if (!Schema::hasColumn('patient_orders', 'collected_at')) {
                $table->timestamp('collected_at')->nullable()->after('promo_code_id');
            }
            
            // Add indexes for better performance
            $table->index('mpesa_checkout_request_id');
            $table->index('mpesa_merchant_request_id');
            $table->index('mpesa_receipt_number');
            $table->index('status');
            $table->index('patient_phone');
            $table->index('ulid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_orders', function (Blueprint $table) {
            // Drop foreign key first
            if (Schema::hasColumn('patient_orders', 'promo_code_id')) {
                $table->dropForeign(['promo_code_id']);
            }
            
            // Drop columns
            $columns = [
                'mpesa_merchant_request_id',
                'mpesa_checkout_request_id',
                'mpesa_receipt_number',
                'mpesa_amount',
                'mpesa_phone',
                'mpesa_result_code',
                'mpesa_result_desc',
                'mpesa_paid_at',
                'paid_at',
                'failed_at',
                'payment_failure_reason',
                'subtotal_amount',
                'discount_amount',
                'total_amount',
                'platform_fee_pct',
                'platform_fee_amount',
                'facility_net_amount',
                'promo_code_id',
                'collected_at',
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('patient_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
            
            // Drop indexes
            $table->dropIndex(['mpesa_checkout_request_id']);
            $table->dropIndex(['mpesa_merchant_request_id']);
            $table->dropIndex(['mpesa_receipt_number']);
            $table->dropIndex(['status']);
            $table->dropIndex(['patient_phone']);
            $table->dropIndex(['ulid']);
        });
    }
};
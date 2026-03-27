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
        Schema::table('orders', function (Blueprint $table) {
            // M-Pesa payment tracking fields
            if (!Schema::hasColumn('orders', 'mpesa_merchant_request_id')) {
                $table->string('mpesa_merchant_request_id', 100)->nullable()->after('copay_status');
            }
            
            if (!Schema::hasColumn('orders', 'mpesa_checkout_request_id')) {
                $table->string('mpesa_checkout_request_id', 100)->nullable()->after('mpesa_merchant_request_id');
            }
            
            if (!Schema::hasColumn('orders', 'mpesa_receipt_number')) {
                $table->string('mpesa_receipt_number', 50)->nullable()->after('mpesa_checkout_request_id');
            }
            
            if (!Schema::hasColumn('orders', 'mpesa_amount')) {
                $table->decimal('mpesa_amount', 12, 2)->nullable()->after('mpesa_receipt_number');
            }
            
            if (!Schema::hasColumn('orders', 'mpesa_phone')) {
                $table->string('mpesa_phone', 20)->nullable()->after('mpesa_amount');
            }
            
            if (!Schema::hasColumn('orders', 'mpesa_result_code')) {
                $table->integer('mpesa_result_code')->nullable()->after('mpesa_phone');
            }
            
            if (!Schema::hasColumn('orders', 'mpesa_result_desc')) {
                $table->text('mpesa_result_desc')->nullable()->after('mpesa_result_code');
            }
            
            if (!Schema::hasColumn('orders', 'mpesa_paid_at')) {
                $table->timestamp('mpesa_paid_at')->nullable()->after('mpesa_result_desc');
            }
            
            // Payment status fields
            if (!Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('mpesa_paid_at');
            }
            
            if (!Schema::hasColumn('orders', 'failed_at')) {
                $table->timestamp('failed_at')->nullable()->after('paid_at');
            }
            
            if (!Schema::hasColumn('orders', 'payment_failure_reason')) {
                $table->text('payment_failure_reason')->nullable()->after('failed_at');
            }
            
            // Add indexes
            $table->index('mpesa_checkout_request_id');
            $table->index('mpesa_merchant_request_id');
            $table->index('mpesa_receipt_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
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
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
            
            // Drop indexes
            $table->dropIndex(['mpesa_checkout_request_id']);
            $table->dropIndex(['mpesa_merchant_request_id']);
            $table->dropIndex(['mpesa_receipt_number']);
        });
    }
};
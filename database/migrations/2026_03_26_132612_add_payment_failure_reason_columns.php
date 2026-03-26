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
            $table->string('payment_failure_reason')->nullable()->after('mpesa_paid_at');
        });

        Schema::table('patient_orders', function (Blueprint $table) {
            $table->timestamp('mpesa_paid_at')->nullable()->after('paid_at');
            $table->string('payment_failure_reason')->nullable()->after('mpesa_paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_failure_reason');
        });
        Schema::table('patient_orders', function (Blueprint $table) {
            $table->dropColumn(['mpesa_paid_at', 'payment_failure_reason']);
        });
    }
};

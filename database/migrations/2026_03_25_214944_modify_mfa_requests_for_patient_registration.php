<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Make user_id nullable for patient registration (no user yet at OTP send time)
        Schema::table('mfa_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        // Expand operation_type enum to include PATIENT_REGISTRATION
        DB::statement("ALTER TABLE mfa_requests MODIFY operation_type ENUM('CREDIT_DRAW','PAYMENT_APPROVAL','PRICE_LIST_CHANGE','ROLE_CHANGE','FACILITY_STATUS_CHANGE','DSAR_VERIFICATION','PATIENT_REGISTRATION') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE mfa_requests MODIFY operation_type ENUM('CREDIT_DRAW','PAYMENT_APPROVAL','PRICE_LIST_CHANGE','ROLE_CHANGE','FACILITY_STATUS_CHANGE','DSAR_VERIFICATION') NOT NULL");

        Schema::table('mfa_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};

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
        // 1. Rename tables
        Schema::rename('patient_baskets', 'customer_baskets');
        Schema::rename('patient_basket_lines', 'customer_basket_lines');
        Schema::rename('patient_orders', 'customer_orders');
        Schema::rename('patient_order_lines', 'customer_order_lines');
        Schema::rename('patient_favourites', 'customer_favourites');
        Schema::rename('patient_counterfeit_reports', 'customer_counterfeit_reports');
        Schema::rename('patient_dsar_requests', 'customer_dsar_requests');

        // 2. Rename columns (after table renames)
        Schema::table('customer_baskets', function (Blueprint $table) {
            $table->renameColumn('patient_phone', 'customer_phone');
        });

        Schema::table('customer_orders', function (Blueprint $table) {
            $table->renameColumn('patient_phone', 'customer_phone');
            $table->renameColumn('patient_name', 'customer_name');
        });

        Schema::table('customer_order_lines', function (Blueprint $table) {
            $table->renameColumn('patient_order_id', 'customer_order_id');
        });

        Schema::table('customer_counterfeit_reports', function (Blueprint $table) {
            $table->renameColumn('patient_phone', 'customer_phone');
        });

        Schema::table('customer_dsar_requests', function (Blueprint $table) {
            $table->renameColumn('patient_phone_hash', 'customer_phone_hash');
        });

        Schema::table('promo_code_usages', function (Blueprint $table) {
            $table->renameColumn('patient_phone', 'customer_phone');
            $table->renameColumn('patient_order_id', 'customer_order_id');
        });

        Schema::table('basket_abandonment_log', function (Blueprint $table) {
            $table->renameColumn('patient_phone', 'customer_phone');
        });

        Schema::table('customer_favourites', function (Blueprint $table) {
            $table->renameColumn('patient_phone', 'customer_phone');
        });

        Schema::table('promo_codes', function (Blueprint $table) {
            $table->renameColumn('usage_cap_per_patient', 'usage_cap_per_customer');
        });

        // 3. Update enum value in mfa_requests
        DB::statement("ALTER TABLE mfa_requests MODIFY operation_type ENUM('CREDIT_DRAW','PAYMENT_APPROVAL','PRICE_LIST_CHANGE','ROLE_CHANGE','FACILITY_STATUS_CHANGE','DSAR_VERIFICATION','PATIENT_REGISTRATION','CUSTOMER_REGISTRATION') NOT NULL");
        DB::statement("UPDATE mfa_requests SET operation_type = 'CUSTOMER_REGISTRATION' WHERE operation_type = 'PATIENT_REGISTRATION'");
        DB::statement("ALTER TABLE mfa_requests MODIFY operation_type ENUM('CREDIT_DRAW','PAYMENT_APPROVAL','PRICE_LIST_CHANGE','ROLE_CHANGE','FACILITY_STATUS_CHANGE','DSAR_VERIFICATION','CUSTOMER_REGISTRATION') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 3. Revert enum value in mfa_requests
        DB::statement("ALTER TABLE mfa_requests MODIFY operation_type ENUM('CREDIT_DRAW','PAYMENT_APPROVAL','PRICE_LIST_CHANGE','ROLE_CHANGE','FACILITY_STATUS_CHANGE','DSAR_VERIFICATION','CUSTOMER_REGISTRATION','PATIENT_REGISTRATION') NOT NULL");
        DB::statement("UPDATE mfa_requests SET operation_type = 'PATIENT_REGISTRATION' WHERE operation_type = 'CUSTOMER_REGISTRATION'");
        DB::statement("ALTER TABLE mfa_requests MODIFY operation_type ENUM('CREDIT_DRAW','PAYMENT_APPROVAL','PRICE_LIST_CHANGE','ROLE_CHANGE','FACILITY_STATUS_CHANGE','DSAR_VERIFICATION','PATIENT_REGISTRATION') NOT NULL");

        // 2. Revert column renames
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->renameColumn('usage_cap_per_customer', 'usage_cap_per_patient');
        });

        Schema::table('customer_favourites', function (Blueprint $table) {
            $table->renameColumn('customer_phone', 'patient_phone');
        });

        Schema::table('basket_abandonment_log', function (Blueprint $table) {
            $table->renameColumn('customer_phone', 'patient_phone');
        });

        Schema::table('promo_code_usages', function (Blueprint $table) {
            $table->renameColumn('customer_phone', 'patient_phone');
            $table->renameColumn('customer_order_id', 'patient_order_id');
        });

        Schema::table('customer_dsar_requests', function (Blueprint $table) {
            $table->renameColumn('customer_phone_hash', 'patient_phone_hash');
        });

        Schema::table('customer_counterfeit_reports', function (Blueprint $table) {
            $table->renameColumn('customer_phone', 'patient_phone');
        });

        Schema::table('customer_order_lines', function (Blueprint $table) {
            $table->renameColumn('customer_order_id', 'patient_order_id');
        });

        Schema::table('customer_orders', function (Blueprint $table) {
            $table->renameColumn('customer_phone', 'patient_phone');
            $table->renameColumn('customer_name', 'patient_name');
        });

        Schema::table('customer_baskets', function (Blueprint $table) {
            $table->renameColumn('customer_phone', 'patient_phone');
        });

        // 1. Revert table renames
        Schema::rename('customer_dsar_requests', 'patient_dsar_requests');
        Schema::rename('customer_counterfeit_reports', 'patient_counterfeit_reports');
        Schema::rename('customer_favourites', 'patient_favourites');
        Schema::rename('customer_order_lines', 'patient_order_lines');
        Schema::rename('customer_orders', 'patient_orders');
        Schema::rename('customer_basket_lines', 'patient_basket_lines');
        Schema::rename('customer_baskets', 'patient_baskets');
    }
};

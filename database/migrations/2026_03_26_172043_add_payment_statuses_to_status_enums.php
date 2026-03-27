<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add PAYMENT_FAILED to patient_orders status enum
        DB::statement("ALTER TABLE patient_orders MODIFY COLUMN status ENUM(
            'PAYMENT_PENDING','PAYMENT_FAILED',
            'CONFIRMED','PREPARING','READY','COLLECTED','CANCELLED','REJECTED'
        ) NOT NULL DEFAULT 'PAYMENT_PENDING'");

        // Add PAYMENT_PENDING and PAYMENT_FAILED to orders status enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'PENDING','PAYMENT_PENDING','PAYMENT_FAILED',
            'CONFIRMED','PICKING','PACKED','DISPATCHED','DELIVERED','DISPUTED','CANCELLED'
        ) NOT NULL DEFAULT 'PENDING'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE patient_orders MODIFY COLUMN status ENUM(
            'PAYMENT_PENDING','CONFIRMED','PREPARING','READY','COLLECTED','CANCELLED','REJECTED'
        ) NOT NULL DEFAULT 'PAYMENT_PENDING'");

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'PENDING','CONFIRMED','PICKING','PACKED','DISPATCHED','DELIVERED','DISPUTED','CANCELLED'
        ) NOT NULL DEFAULT 'PENDING'");
    }
};

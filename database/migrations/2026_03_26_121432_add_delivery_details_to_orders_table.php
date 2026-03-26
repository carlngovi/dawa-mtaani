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
            $table->string('customer_first_name', 100)->nullable()->after('notes');
            $table->string('customer_last_name', 100)->nullable()->after('customer_first_name');
            $table->string('customer_email', 255)->nullable()->after('customer_last_name');
            $table->string('delivery_address', 500)->nullable()->after('customer_email');
            $table->text('delivery_instructions')->nullable()->after('delivery_address');
            $table->string('mpesa_phone', 20)->nullable()->after('delivery_instructions');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['customer_first_name', 'customer_last_name', 'customer_email', 'delivery_address', 'delivery_instructions', 'mpesa_phone']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('delivery_lat', 10, 7)->nullable()->after('delivery_instructions');
            $table->decimal('delivery_lng', 10, 7)->nullable()->after('delivery_lat');
            $table->string('delivery_place_id')->nullable()->after('delivery_lng');
        });

        Schema::table('patient_orders', function (Blueprint $table) {
            $table->decimal('delivery_lat', 10, 7)->nullable()->after('delivery_instructions');
            $table->decimal('delivery_lng', 10, 7)->nullable()->after('delivery_lat');
            $table->string('delivery_place_id')->nullable()->after('delivery_lng');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_lat', 'delivery_lng', 'delivery_place_id']);
        });
        Schema::table('patient_orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_lat', 'delivery_lng', 'delivery_place_id']);
        });
    }
};
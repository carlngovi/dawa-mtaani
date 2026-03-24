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
        Schema::create('recruiter_commission_triggers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('firm_id');
            $table->enum('trigger_event', ['PHARMACY_REGISTRATION', 'FIRST_ORDER_PLACED', 'NTH_ORDER_PLACED', 'CREDIT_ACTIVATED', 'MANUAL_CONFIRMATION']);
            $table->integer('threshold_value')->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreign('firm_id')->references('id')->on('recruiter_firms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruiter_commission_triggers');
    }
};

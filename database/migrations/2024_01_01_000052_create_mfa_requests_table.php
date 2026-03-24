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
        Schema::create('mfa_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->enum('operation_type', ['CREDIT_DRAW', 'PAYMENT_APPROVAL', 'PRICE_LIST_CHANGE', 'ROLE_CHANGE', 'FACILITY_STATUS_CHANGE', 'DSAR_VERIFICATION']);
            $table->enum('verification_method', ['OTP_SMS', 'OTP_WHATSAPP']);
            $table->string('verification_code', 10)->nullable();
            $table->string('verification_token', 100)->unique();
            $table->timestamp('expires_at')->index();
            $table->timestamp('verified_at')->nullable();
            $table->string('verified_by_ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index('verification_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mfa_requests');
    }
};

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
        Schema::create('whatsapp_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('facility_id')->nullable();
            $table->string('whatsapp_phone', 20)->unique();
            $table->enum('session_state', ['IDLE', 'AWAITING_AUTH', 'ORDER_BUILDING', 'ORDER_CONFIRMING', 'STOCK_QUERY', 'CREDIT_QUERY'])->default('IDLE');
            $table->json('session_context')->nullable();
            $table->timestamp('last_activity_at')->useCurrent();
            $table->timestamp('expires_at');
            $table->timestamp('authenticated_at')->nullable();
            $table->enum('authentication_method', ['OTP', 'LINKED_PHONE'])->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('facility_id')->references('id')->on('facilities')->nullOnDelete();
            $table->index(['session_state', 'expires_at'], 'idx_state_expires');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_sessions');
    }
};

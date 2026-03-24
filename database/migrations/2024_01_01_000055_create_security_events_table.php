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
        Schema::create('security_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('facility_id')->nullable()->index();
            $table->enum('event_type', [
                'LOGIN_FAILURE',
                'LOGIN_SUCCESS_UNUSUAL_HOUR',
                'LOGIN_SUCCESS_UNUSUAL_LOCATION',
                'RAPID_ORDER_SUBMISSION',
                'SUSPICIOUS_PRICE_CHANGE',
                'UNUSUAL_CREDIT_DRAW',
                'API_KEY_EXPOSED',
                'RATE_LIMIT_EXCEEDED',
                'SESSION_FINGERPRINT_MISMATCH',
                'MFA_BACKUP_CODE_USED',
            ]);
            $table->enum('severity', ['INFO', 'LOW', 'MEDIUM', 'HIGH', 'CRITICAL']);
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['severity', 'resolved_at'], 'idx_severity_resolved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_events');
    }
};

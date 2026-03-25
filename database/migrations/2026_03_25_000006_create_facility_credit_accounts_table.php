<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('facility_credit_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->foreignId('tranche_id')->constrained('credit_tranches');
            $table->enum('account_status', [
                'PENDING_ASSESSMENT', 'ACTIVE', 'SUSPENDED', 'CLOSED'
            ])->default('PENDING_ASSESSMENT');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->text('suspension_reason')->nullable();
            $table->date('next_assessment_due')->nullable();
            $table->timestamps();
            $table->unique('facility_id');
            $table->index('account_status');
        });
    }

    public function down(): void { Schema::dropIfExists('facility_credit_accounts'); }
};

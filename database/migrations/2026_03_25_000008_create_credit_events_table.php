<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('credit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_account_id')->constrained('facility_credit_accounts')->cascadeOnDelete();
            $table->foreignId('tier_id')->nullable()->constrained('credit_tiers')->nullOnDelete();
            $table->enum('event_type', [
                'DRAW', 'REPAYMENT', 'ALLOCATION', 'SUSPENSION',
                'REINSTATEMENT', 'PROGRESSION', 'ADJUSTMENT'
            ]);
            $table->decimal('amount', 15, 2);
            $table->decimal('running_balance', 15, 2);
            $table->string('reference', 100)->nullable();
            $table->unsignedBigInteger('triggered_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('occurred_at');
            $table->index(['credit_account_id', 'occurred_at']);
            $table->index('event_type');
        });
    }

    public function down(): void { Schema::dropIfExists('credit_events'); }
};

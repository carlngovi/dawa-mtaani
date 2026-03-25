<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_events', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('facility_id')->constrained('facilities');
            $table->foreignId('tranche_id')->constrained('credit_tranches');
            $table->foreignId('tier_id')->nullable()->constrained('credit_tiers');
            $table->foreignId('order_id')->nullable()->constrained('orders');
            $table->enum('event_type', [
                'DRAW', 'REPAYMENT', 'PROGRESSION', 'TIER_UNLOCK',
                'SUSPENSION', 'REINSTATEMENT', 'RETURN_DISTRIBUTION'
            ]);
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->json('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_events');
    }
};

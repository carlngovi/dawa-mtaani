<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('facility_tranche_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_account_id')->constrained('facility_credit_accounts')->cascadeOnDelete();
            $table->foreignId('tier_id')->constrained('credit_tiers');
            $table->decimal('allocated_amount', 15, 2)->default(0);
            $table->decimal('drawn_amount', 15, 2)->default(0);
            $table->decimal('available_amount', 15, 2)->default(0);
            $table->timestamp('last_drawn_at')->nullable();
            $table->timestamp('last_repaid_at')->nullable();
            $table->timestamps();
            $table->unique(['credit_account_id', 'tier_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('facility_tranche_balances'); }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_tranche_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained('facilities');
            $table->foreignId('tranche_id')->constrained('credit_tranches');
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->decimal('entry_balance', 12, 2);
            $table->timestamp('last_progression_at')->nullable();
            $table->timestamp('last_repayment_at')->nullable();
            $table->timestamps();
            $table->unique(['facility_id', 'tranche_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_tranche_balances');
    }
};

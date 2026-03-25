<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('credit_tranche_parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tranche_id')->constrained('credit_tranches')->cascadeOnDelete();
            $table->string('party_name', 255);
            $table->string('party_type', 100);
            $table->string('banking_party_binding', 255)->nullable();
            $table->decimal('risk_percentage', 5, 2)->default(0);
            $table->decimal('return_percentage', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['tranche_id', 'is_active']);
        });
    }

    public function down(): void { Schema::dropIfExists('credit_tranche_parties'); }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('return_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tranche_id')->constrained('credit_tranches')->cascadeOnDelete();
            $table->foreignId('party_id')->constrained('credit_tranche_parties')->cascadeOnDelete();
            $table->foreignId('source_repayment_id')->constrained('repayment_records')->cascadeOnDelete();
            $table->decimal('party_return_percentage', 5, 2);
            $table->decimal('distributed_amount', 12, 2);
            $table->timestamp('distributed_at');
            $table->timestamps();

            $table->index(['tranche_id', 'party_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_distributions');
    }
};

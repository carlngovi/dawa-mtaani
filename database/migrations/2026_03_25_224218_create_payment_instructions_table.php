<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_instructions', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tranche_id')->constrained('credit_tranches')->cascadeOnDelete();
            $table->foreignId('party_id')->constrained('credit_tranche_parties')->cascadeOnDelete();
            $table->decimal('instruction_amount', 12, 2);
            $table->decimal('party_risk_percentage', 5, 2);
            $table->string('idempotency_key', 100)->unique();
            $table->enum('status', ['PENDING','SENT','ACKNOWLEDGED','PROCESSED','FAILED','MANUAL_REVIEW'])->default('PENDING');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->integer('retry_count')->default(0);
            $table->string('party_reference', 255)->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'party_id']);
            $table->index(['status', 'created_at'], 'idx_status_created');
            $table->index(['party_id', 'status'], 'idx_party_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_instructions');
    }
};

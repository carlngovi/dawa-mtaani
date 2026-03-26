<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('repayment_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tranche_id')->constrained('credit_tranches')->cascadeOnDelete();
            $table->decimal('amount_due', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->date('due_at');
            $table->timestamp('paid_at')->nullable();
            $table->enum('payment_method', ['MPESA','BANK_TRANSFER','MANUAL']);
            $table->string('mpesa_reference', 100)->nullable();
            $table->integer('days_to_repay')->nullable();
            $table->boolean('progression_applied')->default(false);
            $table->enum('status', ['PENDING','PARTIAL','PAID','OVERDUE'])->default('PENDING');
            $table->timestamps();

            $table->index(['facility_id', 'status']);
            $table->index(['status', 'due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repayment_records');
    }
};

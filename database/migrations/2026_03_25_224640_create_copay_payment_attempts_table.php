<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('copay_payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->integer('attempt_number');
            $table->string('mpesa_checkout_request_id', 100);
            $table->string('mpesa_result_code', 20)->nullable();
            $table->string('failure_reason', 255)->nullable();
            $table->enum('status', ['INITIATED','SUCCESS','FAILED','EXPIRED'])->default('INITIATED');
            $table->timestamp('initiated_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'initiated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('copay_payment_attempts');
    }
};

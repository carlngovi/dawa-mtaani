<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recruiter_ledger_entries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('firm_id')->index();
            $table->unsignedBigInteger('agent_id')->index();
            $table->unsignedBigInteger('activation_event_id');
            $table->enum('entry_type', ['ACCRUAL', 'ADJUSTMENT', 'WRITE_OFF']);
            $table->decimal('amount_kes', 10, 2);
            $table->decimal('running_balance_kes', 10, 2);
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('firm_id')->references('id')->on('recruiter_firms');
            $table->foreign('activation_event_id')->references('id')->on('recruiter_activation_events');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruiter_ledger_entries');
    }
};

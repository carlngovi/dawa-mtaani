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
        Schema::create('recruiter_activation_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('firm_id')->index();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('facility_id')->index();
            $table->string('trigger_event', 50);
            $table->decimal('gross_amount_kes', 10, 2)->default(0.00);
            $table->json('cascade_breakdown')->nullable();
            $table->enum('reconciliation_status', ['PENDING', 'RECONCILED', 'DISPUTED', 'ADJUSTED'])->default('PENDING')->index();
            $table->text('reconciliation_note')->nullable();
            $table->unsignedBigInteger('reconciled_by')->nullable();
            $table->timestamp('reconciled_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('firm_id')->references('id')->on('recruiter_firms');
            $table->foreign('agent_id')->references('id')->on('recruiter_agents');
            $table->foreign('facility_id')->references('id')->on('facilities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruiter_activation_events');
    }
};

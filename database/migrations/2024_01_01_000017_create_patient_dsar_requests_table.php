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
        Schema::create('patient_dsar_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('ulid', 26)->unique();
            $table->string('patient_phone_hash', 64)->index();
            $table->enum('request_type', ['ACCESS', 'EXPORT', 'DELETION']);
            $table->date('date_range_start')->nullable();
            $table->date('date_range_end')->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'PROCESSING', 'COMPLETED'])->default('PENDING')->index();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('sla_deadline_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_dsar_requests');
    }
};

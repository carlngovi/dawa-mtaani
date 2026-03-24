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
        Schema::create('data_deletion_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('ulid', 26)->unique();
            $table->unsignedBigInteger('facility_id')->index();
            $table->unsignedBigInteger('requested_by');
            $table->enum('request_method', ['PLATFORM', 'WRITTEN']);
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'PROCESSING', 'COMPLETED'])->default('PENDING')->index();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('records_anonymised')->nullable();
            $table->integer('records_deleted')->nullable();
            $table->timestamp('sla_deadline_at');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_deletion_requests');
    }
};

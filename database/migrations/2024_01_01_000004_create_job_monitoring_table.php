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
        Schema::create('job_monitoring', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('job_name', 100);
            $table->enum('status', ['STARTED', 'COMPLETED', 'FAILED']);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->integer('records_processed')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at');

            $table->index(['job_name', 'started_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_monitoring');
    }
};

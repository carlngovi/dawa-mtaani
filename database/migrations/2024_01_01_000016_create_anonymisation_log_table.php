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
        Schema::create('anonymisation_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('batch_id', 100)->index();
            $table->string('data_category', 100)->index();
            $table->integer('records_processed');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->enum('triggered_by', ['RETENTION_SCHEDULE', 'DELETION_REQUEST', 'MANUAL']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anonymisation_log');
    }
};

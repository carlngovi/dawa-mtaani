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
        Schema::create('business_metric_alerts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('metric_name', 100);
            $table->decimal('expected_value', 14, 2);
            $table->decimal('actual_value', 14, 2);
            $table->decimal('deviation_pct', 8, 2);
            $table->string('county', 100)->nullable();
            $table->enum('severity', ['INFO', 'WARNING', 'CRITICAL']);
            $table->timestamp('acknowledged_at')->nullable();
            $table->unsignedBigInteger('acknowledged_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['severity', 'acknowledged_at'], 'idx_severity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_metric_alerts');
    }
};

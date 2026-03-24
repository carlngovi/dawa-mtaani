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
        Schema::create('business_metric_snapshots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('metric_name', 100);
            $table->decimal('metric_value', 14, 2);
            $table->string('county', 100)->nullable();
            $table->string('segment', 100)->nullable();
            $table->timestamp('window_start');
            $table->timestamp('window_end');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['metric_name', 'window_start', 'county'], 'idx_metric_window');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_metric_snapshots');
    }
};

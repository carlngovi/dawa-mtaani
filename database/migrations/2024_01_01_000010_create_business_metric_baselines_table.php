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
        Schema::create('business_metric_baselines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('metric_name', 100);
            $table->tinyInteger('day_of_week');
            $table->tinyInteger('hour_of_day');
            $table->string('county', 100)->nullable();
            $table->decimal('baseline_value', 14, 2);
            $table->integer('sample_count')->default(0);
            $table->timestamp('last_recalculated_at')->nullable();
            $table->timestamps();

            $table->unique(['metric_name', 'day_of_week', 'hour_of_day', 'county'], 'uniq_baseline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_metric_baselines');
    }
};

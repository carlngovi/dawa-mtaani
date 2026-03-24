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
        Schema::create('slo_compliance_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sli_name', 100);
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('total_events');
            $table->integer('successful_events');
            $table->decimal('compliance_pct', 8, 4);
            $table->decimal('slo_target_pct', 5, 2);
            $table->boolean('is_compliant');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slo_compliance_records');
    }
};

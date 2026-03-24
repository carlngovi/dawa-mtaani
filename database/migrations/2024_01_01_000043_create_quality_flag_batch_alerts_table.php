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
        Schema::create('quality_flag_batch_alerts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('quality_flag_id')->index();
            $table->unsignedBigInteger('alerted_facility_id')->index();
            $table->timestamp('alerted_at');
            $table->string('notification_id', 100)->nullable();
            $table->timestamps();

            $table->foreign('quality_flag_id')->references('id')->on('quality_flags')->cascadeOnDelete();
            $table->foreign('alerted_facility_id')->references('id')->on('facilities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_flag_batch_alerts');
    }
};

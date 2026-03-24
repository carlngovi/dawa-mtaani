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
        Schema::create('ppb_verification_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('facility_id')->index();
            $table->timestamp('checked_at');
            $table->string('licence_status_returned', 50);
            $table->json('response_json')->nullable();
            $table->enum('triggered_by', ['ONBOARDING', 'SCHEDULED', 'MANUAL']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppb_verification_logs');
    }
};

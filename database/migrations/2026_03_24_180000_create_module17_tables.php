<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_counterfeit_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('facility_id');
            $table->unsignedBigInteger('product_id');
            $table->string('patient_phone', 255);
            $table->text('report_notes')->nullable();
            $table->enum('status', ['OPEN', 'UNDER_REVIEW', 'CLOSED'])->default('OPEN');
            $table->timestamp('notified_ppb_at')->nullable();
            $table->timestamps();

            $table->foreign('facility_id')->references('id')->on('facilities');
            $table->foreign('product_id')->references('id')->on('products');
        });

        // Laravel notifications table (required for database channel)
        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_counterfeit_reports');
        Schema::dropIfExists('notifications');
    }
};

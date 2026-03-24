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
        Schema::create('quality_flags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('ulid', 26)->unique();
            $table->unsignedBigInteger('facility_id');
            $table->unsignedBigInteger('product_id')->index();
            $table->string('batch_reference', 100)->nullable();
            $table->enum('flag_type', ['SUSPECTED_COUNTERFEIT', 'PACKAGING_ANOMALY', 'LABELLING_CONCERN', 'QUALITY_DEGRADATION', 'OTHER']);
            $table->text('notes')->nullable();
            $table->string('photo_path', 500)->nullable();
            $table->enum('status', ['OPEN', 'UNDER_REVIEW', 'CONFIRMED', 'DISMISSED'])->default('OPEN')->index();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamp('supplier_notified_at')->nullable();
            $table->timestamp('batch_alert_sent_at')->nullable();
            $table->integer('batch_alert_facility_count')->nullable();
            $table->timestamps();

            $table->foreign('facility_id')->references('id')->on('facilities');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_flags');
    }
};

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
        Schema::create('product_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('ulid', 26)->unique();
            $table->unsignedBigInteger('facility_id')->index();
            $table->string('product_name', 255);
            $table->string('brand_name', 255)->nullable();
            $table->string('dosage_form', 100)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['RECEIVED', 'UNDER_REVIEW', 'ADDED', 'REJECTED'])->default('RECEIVED')->index();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->text('review_notes')->nullable();
            $table->unsignedBigInteger('matched_product_id')->nullable();
            $table->timestamps();
        });

        Schema::create('facility_restock_subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('facility_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('wholesale_facility_id')->nullable();
            $table->timestamp('subscribed_at');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->unique(['facility_id', 'product_id', 'wholesale_facility_id'], 'restock_sub_unique');
            $table->foreign('facility_id')->references('id')->on('facilities')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_restock_subscriptions');
        Schema::dropIfExists('product_requests');
    }
};

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
        Schema::create('saved_carts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('ulid', 26)->unique();
            $table->string('name', 255);
            $table->unsignedBigInteger('owner_facility_id')->nullable();
            $table->unsignedBigInteger('owner_group_id')->nullable();
            $table->boolean('is_group_cart')->default(false);
            $table->unsignedBigInteger('conflict_source_order_id')->nullable();
            $table->enum('conflict_resolution_status', ['PENDING', 'RESUBMITTED', 'CANCELLED'])->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_carts');
    }
};

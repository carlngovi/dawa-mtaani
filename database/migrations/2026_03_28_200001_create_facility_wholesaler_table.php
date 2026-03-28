<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_wholesaler', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('retail_facility_id');
            $table->unsignedBigInteger('wholesale_facility_id');
            $table->boolean('is_preferred')->default(false);
            $table->timestamps();

            $table->foreign('retail_facility_id')->references('id')->on('facilities');
            $table->foreign('wholesale_facility_id')->references('id')->on('facilities');
            $table->unique(['retail_facility_id', 'wholesale_facility_id'], 'facility_ws_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_wholesaler');
    }
};

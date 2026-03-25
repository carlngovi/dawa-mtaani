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
        Schema::create('online_store_eligible_facilities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('facility_id')->unique();
            $table->timestamp('qualified_at');
            $table->integer('pos_data_days');
            $table->decimal('variance_score', 5, 2);
            $table->enum('branding_mode', ['OWN_BRAND', 'DAWA_MTAANI'])->default('OWN_BRAND');
            $table->boolean('is_network_member')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('facility_id')->references('id')->on('facilities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_store_eligible_facilities');
    }
};

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
        Schema::create('facility_pricing_agreements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('facility_id')->unique();
            $table->enum('premium_type', ['PERCENTAGE', 'FIXED_MARGIN']);
            $table->decimal('premium_value', 8, 4);
            $table->date('effective_from');
            $table->date('expires_at')->nullable();
            $table->unsignedBigInteger('agreed_by');
            $table->timestamps();

            $table->foreign('facility_id')
                ->references('id')
                ->on('facilities')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_pricing_agreements');
    }
};

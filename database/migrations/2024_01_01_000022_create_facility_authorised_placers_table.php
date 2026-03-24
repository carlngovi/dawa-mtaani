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
        Schema::create('facility_authorised_placers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('facility_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('added_by');
            $table->timestamp('added_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['facility_id', 'user_id']);

            $table->foreign('facility_id')
                ->references('id')
                ->on('facilities')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facility_authorised_placers');
    }
};

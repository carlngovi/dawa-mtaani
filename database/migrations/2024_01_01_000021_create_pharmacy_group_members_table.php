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
        Schema::create('pharmacy_group_members', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('group_id')->index();
            $table->unsignedBigInteger('facility_id')->unique();
            $table->unsignedBigInteger('added_by');
            $table->timestamp('added_at');
            $table->timestamps();

            $table->foreign('group_id')
                ->references('id')
                ->on('pharmacy_groups')
                ->cascadeOnDelete();

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
        Schema::dropIfExists('pharmacy_group_members');
    }
};

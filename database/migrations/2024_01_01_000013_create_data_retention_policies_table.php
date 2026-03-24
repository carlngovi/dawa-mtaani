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
        Schema::create('data_retention_policies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('data_category', 100)->unique();
            $table->integer('retention_years');
            $table->enum('action_on_expiry', ['ANONYMISE', 'DELETE']);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_retention_policies');
    }
};

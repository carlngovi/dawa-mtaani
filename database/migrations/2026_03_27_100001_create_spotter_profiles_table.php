<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spotter_profiles', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('county');
            $table->string('ward');
            $table->unsignedBigInteger('sales_rep_user_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('sales_rep_user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('county');
            $table->index('ward');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spotter_profiles');
    }
};

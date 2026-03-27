<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spotter_duplicate_reviews', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('spotter_submission_id')->constrained('spotter_submissions');
            $table->string('matched_submission_id', 26)->nullable();
            $table->foreign('matched_submission_id')->references('id')->on('spotter_submissions');
            $table->enum('tier', ['sr', 'cc', 'admin']);
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users');
            $table->enum('decision', ['confirmed_duplicate', 'not_duplicate', 'pending'])->default('pending');
            $table->decimal('gps_distance_metres', 8, 2)->nullable();
            $table->tinyInteger('name_edit_distance')->nullable();
            $table->string('match_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spotter_duplicate_reviews');
    }
};

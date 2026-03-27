<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spotter_follow_ups', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('spotter_submission_id')->constrained('spotter_submissions');
            $table->foreignId('spotter_user_id')->constrained('users');
            $table->string('next_step');
            $table->date('follow_up_date');
            $table->text('rep_notes')->nullable();
            $table->enum('status', ['open', 'completed', 'overdue'])->default('open');
            $table->text('outcome_note')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('overdue_alerted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spotter_follow_ups');
    }
};

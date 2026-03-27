<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spotter_submissions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('local_id', 36)->unique();
            $table->foreignId('spotter_user_id')->constrained('users');
            $table->enum('status', ['draft', 'submitted', 'held', 'sr_reviewed', 'cc_verified', 'accepted', 'rejected'])->default('draft');
            $table->string('county');
            $table->string('ward');
            $table->string('town');
            $table->string('address');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->decimal('gps_accuracy', 8, 2)->nullable();
            $table->string('pharmacy');
            $table->string('open_time', 8);
            $table->string('close_time', 8);
            $table->tinyInteger('days_per_week');
            $table->date('visit_date');
            $table->string('owner_name');
            $table->string('owner_phone');
            $table->string('pharmacy_phone')->nullable();
            $table->string('owner_email')->nullable();
            $table->boolean('owner_present');
            $table->enum('foot_traffic', ['high', 'medium', 'low'])->nullable();
            $table->enum('stock_level', ['well_stocked', 'moderate', 'sparse', 'not_observed'])->nullable();
            $table->text('notes')->nullable();
            $table->enum('potential', ['high', 'medium', 'low']);
            $table->boolean('follow_up')->default(false);
            $table->string('callback_time')->nullable();
            $table->enum('next_step', ['sales_rep', 'spotter_followup', 'owner_absent', 'no_action'])->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('rep_notes')->nullable();
            $table->boolean('brochure')->default(false);
            $table->string('photo_path')->nullable();
            $table->string('photo_name')->nullable();
            $table->integer('photo_size_bytes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spotter_submissions');
    }
};

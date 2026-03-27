<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spotter_attendances', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('spotter_user_id')->constrained('users');
            $table->string('server_id', 26)->unique();
            $table->date('date');
            $table->timestamp('clock_in_at');
            $table->decimal('clock_in_lat', 10, 7)->nullable();
            $table->decimal('clock_in_lng', 10, 7)->nullable();
            $table->timestamp('clock_out_at')->nullable();
            $table->decimal('clock_out_lat', 10, 7)->nullable();
            $table->decimal('clock_out_lng', 10, 7)->nullable();
            $table->boolean('auto_closed')->default(false);
            $table->tinyInteger('split_shift_index')->default(1);
            $table->timestamps();

            $table->unique(['spotter_user_id', 'date', 'split_shift_index'], 'spotter_attend_user_date_shift_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spotter_attendances');
    }
};

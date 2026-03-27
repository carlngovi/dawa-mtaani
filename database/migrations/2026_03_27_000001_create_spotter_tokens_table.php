<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spotter_tokens', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('spotter_user_id')->constrained('users');
            $table->string('token_hash', 128)->unique();
            $table->string('refresh_token_hash', 128)->unique()->nullable();
            $table->string('device_fingerprint', 255)->nullable();
            $table->string('county');
            $table->string('ward');
            $table->string('sales_rep_name')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('refresh_expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spotter_tokens');
    }
};

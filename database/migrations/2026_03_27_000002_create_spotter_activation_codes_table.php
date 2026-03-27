<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spotter_activation_codes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('spotter_user_id')->constrained('users');
            $table->string('code', 64)->unique();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamp('expires_at');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spotter_activation_codes');
    }
};

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
        Schema::create('integration_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('integration_name', 100);
            $table->enum('direction', ['OUTBOUND', 'INBOUND']);
            $table->string('endpoint', 500)->default('');
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->integer('http_status')->nullable();
            $table->boolean('success');
            $table->text('error_message')->nullable();
            $table->integer('duration_ms');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['integration_name', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_logs');
    }
};

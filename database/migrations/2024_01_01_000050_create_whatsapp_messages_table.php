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
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('whatsapp_message_id', 100)->unique()->nullable();
            $table->unsignedBigInteger('facility_id')->nullable()->index();
            $table->unsignedBigInteger('session_id')->nullable()->index();
            $table->enum('direction', ['INBOUND', 'OUTBOUND']);
            $table->enum('message_type', ['TEXT', 'IMAGE', 'DOCUMENT', 'INTERACTIVE', 'BUTTON']);
            $table->text('content')->nullable();
            $table->string('intent_detected', 50)->nullable();
            $table->boolean('processed_successfully')->default(true);
            $table->text('error_message')->nullable();
            $table->integer('processing_time_ms')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('facility_id')->references('id')->on('facilities')->nullOnDelete();
            $table->foreign('session_id')->references('id')->on('whatsapp_sessions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};

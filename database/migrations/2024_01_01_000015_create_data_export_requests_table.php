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
        Schema::create('data_export_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('ulid', 26)->unique();
            $table->unsignedBigInteger('facility_id')->index();
            $table->unsignedBigInteger('requested_by');
            $table->enum('status', ['PENDING', 'APPROVED', 'GENERATING', 'READY', 'EXPIRED', 'REJECTED'])->default('PENDING');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->string('download_url', 500)->nullable();
            $table->timestamp('download_expires_at')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_export_requests');
    }
};

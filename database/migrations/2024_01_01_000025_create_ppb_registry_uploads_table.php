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
        Schema::create('ppb_registry_uploads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('uploaded_by')->index();
            $table->string('file_name', 255);
            $table->string('file_hash', 64);
            $table->integer('row_count')->default(0);
            $table->integer('rows_inserted')->default(0);
            $table->integer('rows_updated')->default(0);
            $table->integer('rows_rejected')->default(0);
            $table->enum('status', ['PROCESSING', 'COMPLETED', 'FAILED'])->default('PROCESSING')->index();
            $table->json('error_report')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppb_registry_uploads');
    }
};

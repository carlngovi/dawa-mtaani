<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reporting_exports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('exported_by')->index();
            $table->string('export_type', 100);
            $table->json('metric_definitions');
            $table->json('parameters')->nullable();
            $table->string('file_path', 500)->nullable();
            $table->integer('row_count')->default(0);
            $table->enum('status', ['QUEUED','GENERATING','READY','FAILED'])->default('QUEUED')->index();
            $table->string('download_url', 500)->nullable();
            $table->timestamp('download_expires_at')->nullable();
            $table->timestamps();

            $table->foreign('exported_by')->references('id')->on('users');
        });
    }

    public function down(): void {
        Schema::dropIfExists('reporting_exports');
    }
};

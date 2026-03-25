<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('facility_flags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('facility_id')->index();
            $table->unsignedBigInteger('flagged_by');
            $table->enum('reason', ['LATE_PAYMENT','LOW_ORDER_FREQUENCY','DISPUTE_PATTERN','OTHER']);
            $table->text('notes')->nullable();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamps();

            $table->foreign('facility_id')->references('id')->on('facilities')->cascadeOnDelete();
            $table->foreign('flagged_by')->references('id')->on('users');
        });
    }

    public function down(): void {
        Schema::dropIfExists('facility_flags');
    }
};

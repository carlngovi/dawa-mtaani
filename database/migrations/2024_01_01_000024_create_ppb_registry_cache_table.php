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
        Schema::create('ppb_registry_cache', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('licence_number', 100)->unique();
            $table->string('facility_name', 255);
            $table->enum('ppb_type', ['RETAIL', 'WHOLESALE', 'HOSPITAL', 'MANUFACTURER']);
            $table->enum('licence_status', ['VALID', 'EXPIRED', 'SUSPENDED']);
            $table->text('registered_address')->nullable();
            $table->date('licence_expiry_date')->nullable();
            $table->timestamp('last_uploaded_at');
            $table->unsignedBigInteger('upload_batch_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppb_registry_cache');
    }
};

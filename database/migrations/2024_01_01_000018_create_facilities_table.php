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
        Schema::create('facilities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('ulid', 26)->unique();
            $table->string('owner_name', 255);
            $table->string('ppb_licence_number', 100)->unique();
            $table->enum('ppb_facility_type', ['RETAIL', 'WHOLESALE', 'HOSPITAL', 'MANUFACTURER']);
            $table->enum('ppb_licence_status', ['VALID', 'EXPIRED', 'SUSPENDED']);
            $table->timestamp('ppb_verified_at')->nullable();
            $table->json('ppb_raw_response')->nullable();
            $table->string('facility_name', 255);
            $table->string('phone', 20);
            $table->string('email', 255)->nullable();
            $table->string('county', 100)->index();
            $table->string('sub_county', 100);
            $table->string('ward', 100);
            $table->text('physical_address');
            $table->string('banking_account_number', 100)->nullable();
            $table->timestamp('banking_account_validated_at')->nullable();
            $table->enum('network_membership', ['NETWORK', 'OFF_NETWORK'])->default('NETWORK')->index();
            $table->enum('onboarding_status', ['APPLIED', 'PPB_VERIFIED', 'ACCOUNT_LINKED', 'ACTIVE']);
            $table->enum('facility_status', ['ACTIVE', 'SUSPENDED', 'PAUSED', 'CHURNED'])->index();
            $table->timestamp('activated_at')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('gps_accuracy_meters')->nullable();
            $table->timestamp('gps_captured_at')->nullable();
            $table->unsignedBigInteger('gps_captured_by')->nullable();
            $table->enum('gps_capture_method', ['DEVICE_AUTO', 'MANUAL_ENTRY', 'MAP_PIN', 'ADMIN_UPLOAD'])->nullable();
            $table->boolean('phone_uniqueness_override')->default(false);
            $table->text('phone_override_reason')->nullable();
            $table->unsignedBigInteger('phone_override_by')->nullable();
            $table->boolean('is_anonymised')->default(false);
            $table->timestamp('anonymised_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('ppb_facility_type');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('facility_id')
                ->references('id')
                ->on('facilities')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
        });

        Schema::dropIfExists('facilities');
    }
};

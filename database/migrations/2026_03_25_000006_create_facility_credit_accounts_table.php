<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_credit_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->unique()->constrained('facilities');
            $table->enum('account_status', ['ACTIVE', 'SUSPENDED', 'PENDING_ASSESSMENT'])
                  ->default('PENDING_ASSESSMENT');
            $table->timestamp('suspended_at')->nullable();
            $table->text('suspended_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_credit_accounts');
    }
};

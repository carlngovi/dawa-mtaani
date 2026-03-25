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
        Schema::create('recruiter_firms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('firm_name', 255);
            $table->decimal('commission_rate_kes', 10, 2)->default(0.00);
            $table->json('cascade_config')->nullable();
            $table->text('bank_account_details')->nullable();
            $table->enum('status', ['ACTIVE', 'SUSPENDED'])->default('ACTIVE');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruiter_firms');
    }
};

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
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('template_name', 100)->unique();
            $table->string('language_code', 10)->default('en');
            $table->enum('category', ['ORDER_CONFIRMATION', 'DELIVERY_UPDATE', 'PAYMENT_REMINDER', 'CREDIT_ALERT', 'WELCOME_ONBOARDED', 'COPAY_FAILED']);
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};

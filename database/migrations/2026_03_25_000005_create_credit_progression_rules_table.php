<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_progression_rules', function (Blueprint $table) {
            $table->id();
            $table->string('label', 100);
            $table->integer('max_days_to_qualify');
            $table->decimal('progression_rate_pct', 5, 2);
            $table->boolean('is_suspension_trigger')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_progression_rules');
    }
};

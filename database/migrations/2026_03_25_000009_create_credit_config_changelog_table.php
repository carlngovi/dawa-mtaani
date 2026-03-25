<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_config_changelog', function (Blueprint $table) {
            $table->id();
            $table->foreignId('changed_by')->constrained('users');
            $table->string('model_type', 100);
            $table->unsignedBigInteger('model_id');
            $table->string('field_name', 100);
            $table->text('value_before')->nullable();
            $table->text('value_after')->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_config_changelog');
    }
};

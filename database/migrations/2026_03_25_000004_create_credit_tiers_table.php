<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('credit_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('tranche_id')->constrained('credit_tranches')->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('product_scope_description');
            $table->decimal('unlock_threshold_pct', 5, 2)->default(0);
            $table->decimal('allocation_pct', 5, 2)->default(0);
            $table->boolean('approval_required')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['tranche_id', 'sort_order']);
        });
    }

    public function down(): void { Schema::dropIfExists('credit_tiers'); }
};

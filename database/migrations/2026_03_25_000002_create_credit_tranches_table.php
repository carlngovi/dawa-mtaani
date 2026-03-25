<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('credit_tranches', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->string('name', 100);
            $table->decimal('entry_amount', 15, 2)->default(0);
            $table->decimal('ceiling_amount', 15, 2)->nullable();
            $table->boolean('is_fixed')->default(false);
            $table->enum('approval_pathway', ['AUTOMATIC', 'ASSESSED'])->default('ASSESSED');
            $table->json('product_restriction_scope')->nullable();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('is_active');
        });
    }

    public function down(): void { Schema::dropIfExists('credit_tranches'); }
};

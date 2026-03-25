<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_tranches', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->string('name', 100);
            $table->decimal('entry_amount', 10, 2);
            $table->decimal('ceiling_amount', 10, 2)->nullable();
            $table->boolean('is_fixed')->default(false);
            $table->enum('approval_pathway', ['AUTOMATIC', 'ASSESSED']);
            $table->json('product_restriction_scope')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_tranches');
    }
};

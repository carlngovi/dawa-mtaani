<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ward_adjacencies', function (Blueprint $table) {
            $table->id();
            $table->string('ward_id');
            $table->string('adjacent_ward_id');

            $table->unique(['ward_id', 'adjacent_ward_id']);
            $table->index('ward_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ward_adjacencies');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old geography tables (different schema — no id PK, different column names)
        Schema::dropIfExists('kenya_wards');
        Schema::dropIfExists('kenya_constituencies');
        Schema::dropIfExists('kenya_counties');

        Schema::create('kenya_counties', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('county_code')->unique();
            $table->string('county_name');
            $table->timestamps();
        });

        Schema::create('kenya_constituencies', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('constituency_code')->unique();
            $table->string('constituency_name');
            $table->foreignId('kenya_county_id')->constrained('kenya_counties')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('kenya_wards', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('ward_code')->unique();
            $table->string('ward_name');
            $table->foreignId('kenya_constituency_id')->constrained('kenya_constituencies')->cascadeOnDelete();
            $table->foreignId('kenya_county_id')->constrained('kenya_counties')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kenya_wards');
        Schema::dropIfExists('kenya_constituencies');
        Schema::dropIfExists('kenya_counties');
    }
};

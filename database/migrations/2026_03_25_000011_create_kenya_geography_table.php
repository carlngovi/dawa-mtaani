<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kenya_counties', function (Blueprint $table) {
            $table->unsignedSmallInteger('code')->primary();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        Schema::create('kenya_constituencies', function (Blueprint $table) {
            $table->unsignedSmallInteger('code')->primary();
            $table->unsignedSmallInteger('county_code');
            $table->string('name', 150);
            $table->foreign('county_code')->references('code')->on('kenya_counties')->cascadeOnDelete();
            $table->index('county_code');
        });

        Schema::create('kenya_wards', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('constituency_code');
            $table->unsignedSmallInteger('county_code');
            $table->string('name', 150);
            $table->unsignedInteger('registered_voters')->default(0);
            $table->foreign('constituency_code')->references('code')->on('kenya_constituencies')->cascadeOnDelete();
            $table->foreign('county_code')->references('code')->on('kenya_counties')->cascadeOnDelete();
            $table->index(['county_code', 'constituency_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kenya_wards');
        Schema::dropIfExists('kenya_constituencies');
        Schema::dropIfExists('kenya_counties');
    }
};

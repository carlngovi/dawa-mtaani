<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->string('village_town', 150)->nullable()->after('ward');
            $table->foreignId('kenya_ward_id')->nullable()->after('village_town')->constrained('kenya_wards')->nullOnDelete();
            $table->foreignId('kenya_constituency_id')->nullable()->after('kenya_ward_id')->constrained('kenya_constituencies')->nullOnDelete();
            $table->foreignId('kenya_county_id')->nullable()->after('kenya_constituency_id')->constrained('kenya_counties')->nullOnDelete();
        });

        // Make ppb_licence_number nullable
        Schema::table('facilities', function (Blueprint $table) {
            $table->string('ppb_licence_number')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropForeign(['kenya_ward_id']);
            $table->dropForeign(['kenya_constituency_id']);
            $table->dropForeign(['kenya_county_id']);
            $table->dropColumn(['village_town', 'kenya_ward_id', 'kenya_constituency_id', 'kenya_county_id']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ward_adjacencies', function (Blueprint $table) {
            $table->string('county')->nullable()->after('ward_id');
            $table->index('county');
        });
    }

    public function down(): void
    {
        Schema::table('ward_adjacencies', function (Blueprint $table) {
            $table->dropIndex(['county']);
            $table->dropColumn('county');
        });
    }
};

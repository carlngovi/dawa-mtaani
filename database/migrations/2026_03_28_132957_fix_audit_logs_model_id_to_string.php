<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // model_id was bigint unsigned — ULID strings were silently cast to 0.
        // Change to varchar(50) to store ULIDs and integer IDs alike.
        DB::statement('ALTER TABLE audit_logs MODIFY model_id VARCHAR(50) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE audit_logs MODIFY model_id BIGINT UNSIGNED NULL');
    }
};

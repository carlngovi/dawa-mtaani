<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recruiter_agents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('firm_id');
            $table->unsignedBigInteger('parent_agent_id')->nullable();
            $table->string('agent_name', 255);
            $table->string('agent_phone', 20);
            $table->string('agent_role_label', 100);
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('firm_id')->references('id')->on('recruiter_firms');
            $table->foreign('parent_agent_id')->references('id')->on('recruiter_agents')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruiter_agents');
    }
};

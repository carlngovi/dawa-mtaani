<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('network_daily_summaries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('summary_date');
            $table->string('county', 100)->nullable();
            $table->enum('facility_type', ['RETAIL','WHOLESALE','HOSPITAL','MANUFACTURER'])->nullable();
            $table->enum('network_membership', ['NETWORK','OFF_NETWORK','ALL'])->nullable();
            $table->integer('total_orders')->default(0);
            $table->decimal('total_gmv', 14, 2)->default(0);
            $table->decimal('avg_order_value', 10, 2)->default(0);
            $table->integer('active_facilities')->default(0);
            $table->integer('new_facilities')->default(0);
            $table->decimal('credit_drawn', 14, 2)->default(0);
            $table->decimal('credit_repaid', 14, 2)->default(0);
            $table->integer('overdue_count')->default(0);
            $table->decimal('overdue_value', 14, 2)->default(0);
            $table->timestamp('computed_at');
            $table->timestamps();

            $table->unique(['summary_date','county','network_membership','facility_type'], 'nds_unique_segment');
            $table->index(['summary_date','county','network_membership'], 'nds_date_county_membership');
        });
    }

    public function down(): void {
        Schema::dropIfExists('network_daily_summaries');
    }
};

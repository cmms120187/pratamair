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
        Schema::table('part_erp_stock_movements', function (Blueprint $table) {
            $table->string('reference_type', 80)->nullable()->after('user_id'); // downtime_erp2, downtime_erp, preventive_maintenance_execution, work_order, manual
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('part_erp_stock_movements', function (Blueprint $table) {
            $table->dropColumn(['reference_type', 'reference_id']);
        });
    }
};

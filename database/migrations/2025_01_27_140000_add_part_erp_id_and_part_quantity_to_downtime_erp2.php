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
        Schema::table('downtime_erp2', function (Blueprint $table) {
            $table->unsignedBigInteger('part_erp_id')->nullable()->after('Part');
            $table->unsignedInteger('part_quantity')->default(0)->after('part_erp_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('downtime_erp2', function (Blueprint $table) {
            $table->dropColumn(['part_erp_id', 'part_quantity']);
        });
    }
};

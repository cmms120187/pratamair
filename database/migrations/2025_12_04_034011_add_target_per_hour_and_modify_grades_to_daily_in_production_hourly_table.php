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
        Schema::table('production_hourly', function (Blueprint $table) {
            $table->integer('target_per_hour')->nullable()->after('hour');
            // Change total_production to string to allow "(istirahat)"
            $table->string('total_production')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_hourly', function (Blueprint $table) {
            $table->dropColumn('target_per_hour');
            // Revert total_production back to integer
            $table->integer('total_production')->nullable(false)->change();
        });
    }
};

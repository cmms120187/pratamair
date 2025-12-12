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
        Schema::table('production_daily_grades', function (Blueprint $table) {
            $table->integer('target_per_hour')->nullable()->after('production_date');
            $table->time('start_time')->nullable()->after('target_per_hour');
            $table->time('end_time')->nullable()->after('start_time');
            $table->decimal('break_duration', 3, 1)->nullable()->after('end_time'); // 1.0 or 1.5 hours
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_daily_grades', function (Blueprint $table) {
            $table->dropColumn(['target_per_hour', 'start_time', 'end_time', 'break_duration']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify ENUM to include 'caution'
        // MySQL doesn't support direct ENUM modification, so we use raw SQL
        DB::statement("ALTER TABLE `predictive_maintenance_executions` MODIFY COLUMN `measurement_status` ENUM('normal', 'warning', 'caution', 'critical') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original ENUM values
        DB::statement("ALTER TABLE `predictive_maintenance_executions` MODIFY COLUMN `measurement_status` ENUM('normal', 'warning', 'critical') NULL");
    }
};

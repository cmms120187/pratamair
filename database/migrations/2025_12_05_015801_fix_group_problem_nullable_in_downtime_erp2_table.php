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
        // Use raw SQL to modify column to nullable
        // First, update any empty strings to NULL
        DB::statement("UPDATE `downtime_erp2` SET `groupProblem` = NULL WHERE `groupProblem` = '' OR `groupProblem` IS NULL");
        
        // Then modify the column to be nullable
        DB::statement("ALTER TABLE `downtime_erp2` MODIFY COLUMN `groupProblem` VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to NOT NULL (set empty values first)
        DB::statement("UPDATE `downtime_erp2` SET `groupProblem` = '' WHERE `groupProblem` IS NULL");
        DB::statement("ALTER TABLE `downtime_erp2` MODIFY COLUMN `groupProblem` VARCHAR(255) NOT NULL");
    }
};

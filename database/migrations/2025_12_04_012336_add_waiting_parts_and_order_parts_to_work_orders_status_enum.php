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
        DB::statement("ALTER TABLE `work_orders` MODIFY COLUMN `status` ENUM('pending', 'waiting_parts', 'order_parts', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to the old ENUM values, handling potential 'waiting_parts' and 'order_parts' values
        DB::statement("UPDATE `work_orders` SET `status` = 'pending' WHERE `status` IN ('waiting_parts', 'order_parts')");
        DB::statement("ALTER TABLE `work_orders` MODIFY COLUMN `status` ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending'");
    }
};

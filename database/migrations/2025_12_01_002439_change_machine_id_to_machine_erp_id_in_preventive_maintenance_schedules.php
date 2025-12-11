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
        // First, migrate data if needed (map machine_id to machine_erp_id based on idMachine)
        DB::statement('
            UPDATE preventive_maintenance_schedules pms
            INNER JOIN machines m ON pms.machine_id = m.id
            INNER JOIN machine_erp me ON m.idMachine = me.idMachine
            SET pms.machine_id = me.id
            WHERE EXISTS (
                SELECT 1 FROM machines m2 
                INNER JOIN machine_erp me2 ON m2.idMachine = me2.idMachine 
                WHERE m2.id = pms.machine_id
            )
        ');

        Schema::table('preventive_maintenance_schedules', function (Blueprint $table) {
            // Drop existing foreign key and index
            $table->dropForeign(['machine_id']);
            $table->dropIndex(['machine_id', 'status']);
        });

        // Add new column
        Schema::table('preventive_maintenance_schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('machine_erp_id')->after('id');
        });

        // Copy data from machine_id to machine_erp_id
        DB::statement('UPDATE preventive_maintenance_schedules SET machine_erp_id = machine_id');

        Schema::table('preventive_maintenance_schedules', function (Blueprint $table) {
            // Drop old column
            $table->dropColumn('machine_id');
            
            // Add new foreign key to machine_erp
            $table->foreign('machine_erp_id')->references('id')->on('machine_erp')->onDelete('cascade');
            // Recreate index with new column name
            $table->index(['machine_erp_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('preventive_maintenance_schedules', function (Blueprint $table) {
            // Drop foreign key and index
            $table->dropForeign(['machine_erp_id']);
            $table->dropIndex(['machine_erp_id', 'status']);
        });

        // Add old column back
        Schema::table('preventive_maintenance_schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('machine_id')->after('id');
        });

        // Copy data back (this might lose data if MachineErp doesn't have corresponding Machine)
        DB::statement('
            UPDATE preventive_maintenance_schedules pms
            INNER JOIN machine_erp me ON pms.machine_erp_id = me.id
            INNER JOIN machines m ON me.idMachine = m.idMachine
            SET pms.machine_id = m.id
        ');

        Schema::table('preventive_maintenance_schedules', function (Blueprint $table) {
            // Drop new column
            $table->dropColumn('machine_erp_id');
            
            // Restore original foreign key
            $table->foreign('machine_id')->references('id')->on('machines')->onDelete('cascade');
            $table->index(['machine_id', 'status']);
        });
    }
};

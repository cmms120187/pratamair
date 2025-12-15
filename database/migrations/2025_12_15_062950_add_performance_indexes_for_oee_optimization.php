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
        // Index untuk downtime_erp2 - untuk OEE queries
        if (Schema::hasTable('downtime_erp2')) {
            Schema::table('downtime_erp2', function (Blueprint $table) {
                // Check if column exists before adding index
                if (Schema::hasColumn('downtime_erp2', 'include_oee')) {
                    $table->index(['date', 'include_oee'], 'idx_downtime_erp2_date_include_oee');
                }
                $table->index(['date', 'plant', 'process', 'line'], 'idx_downtime_erp2_location_date');
                $table->index(['idMachine', 'date'], 'idx_downtime_erp2_machine_date');
                $table->index(['date', 'problemDowntime'], 'idx_downtime_erp2_date_problem');
                $table->index(['nameMekanik', 'date'], 'idx_downtime_erp2_mechanic_date');
            });
        }
        
        // Index untuk production_daily_grades - untuk OEE queries
        if (Schema::hasTable('production_daily_grades')) {
            Schema::table('production_daily_grades', function (Blueprint $table) {
                // Composite index untuk filter date range dengan line dan process
                $table->index(['production_date', 'line_id', 'process_id'], 'idx_prod_daily_date_line_process');
            });
        }
        
        // Index untuk production_hourly - untuk OEE queries
        if (Schema::hasTable('production_hourly')) {
            Schema::table('production_hourly', function (Blueprint $table) {
                // Composite index untuk query berdasarkan date, line, process, dan hour
                $table->index(['production_date', 'line_id', 'process_id', 'hour'], 'idx_prod_hourly_composite');
                // Index untuk query total production
                $table->index(['line_id', 'process_id', 'production_date'], 'idx_prod_hourly_line_process_date');
            });
        }
        
        // Index untuk production_daily_downtimes - untuk OEE queries
        if (Schema::hasTable('production_daily_downtimes')) {
            Schema::table('production_daily_downtimes', function (Blueprint $table) {
                $table->index(['production_daily_grade_id', 'include_oee'], 'idx_prod_daily_dt_grade_include');
            });
        }
        
        // Index untuk preventive_maintenance_schedules - untuk dashboard queries
        if (Schema::hasTable('preventive_maintenance_schedules')) {
            Schema::table('preventive_maintenance_schedules', function (Blueprint $table) {
                $table->index(['start_date', 'status'], 'idx_pm_schedule_date_status');
                $table->index(['machine_erp_id', 'start_date'], 'idx_pm_schedule_machine_date');
            });
        }
        
        // Index untuk predictive_maintenance_schedules - untuk dashboard queries
        if (Schema::hasTable('predictive_maintenance_schedules')) {
            Schema::table('predictive_maintenance_schedules', function (Blueprint $table) {
                $table->index(['start_date', 'status'], 'idx_pdm_schedule_date_status');
                $table->index(['machine_erp_id', 'start_date'], 'idx_pdm_schedule_machine_date');
            });
        }
        
        // Index untuk work_orders - untuk dashboard queries
        if (Schema::hasTable('work_orders')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->index(['status'], 'idx_work_orders_status');
                $table->index(['order_date'], 'idx_work_orders_order_date');
            });
        }
        
        // Index untuk downtimes (jika menggunakan tabel ini)
        if (Schema::hasTable('downtimes')) {
            Schema::table('downtimes', function (Blueprint $table) {
                $table->index(['date', 'machine_id'], 'idx_downtime_date_machine');
                $table->index(['mekanik_id', 'date'], 'idx_downtime_mechanic_date');
            });
        }
        
        // Index untuk downtime_erp (jika menggunakan)
        if (Schema::hasTable('downtime_erp')) {
            Schema::table('downtime_erp', function (Blueprint $table) {
                $table->index(['date', 'idMachine'], 'idx_downtime_erp_date_machine');
                $table->index(['nameMekanik', 'date'], 'idx_downtime_erp_mechanic_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes for downtime_erp2
        if (Schema::hasTable('downtime_erp2')) {
            Schema::table('downtime_erp2', function (Blueprint $table) {
                $table->dropIndex('idx_downtime_erp2_date_include_oee');
                $table->dropIndex('idx_downtime_erp2_location_date');
                $table->dropIndex('idx_downtime_erp2_machine_date');
                $table->dropIndex('idx_downtime_erp2_date_problem');
                $table->dropIndex('idx_downtime_erp2_mechanic_date');
            });
        }
        
        // Drop indexes for production_daily_grades
        if (Schema::hasTable('production_daily_grades')) {
            Schema::table('production_daily_grades', function (Blueprint $table) {
                $table->dropIndex('idx_prod_daily_date_line_process');
            });
        }
        
        // Drop indexes for production_hourly
        if (Schema::hasTable('production_hourly')) {
            Schema::table('production_hourly', function (Blueprint $table) {
                $table->dropIndex('idx_prod_hourly_composite');
                $table->dropIndex('idx_prod_hourly_line_process_date');
            });
        }
        
        // Drop indexes for production_daily_downtimes
        if (Schema::hasTable('production_daily_downtimes')) {
            Schema::table('production_daily_downtimes', function (Blueprint $table) {
                $table->dropIndex('idx_prod_daily_dt_grade_include');
            });
        }
        
        // Drop indexes for preventive_maintenance_schedules
        if (Schema::hasTable('preventive_maintenance_schedules')) {
            Schema::table('preventive_maintenance_schedules', function (Blueprint $table) {
                $table->dropIndex('idx_pm_schedule_date_status');
                $table->dropIndex('idx_pm_schedule_machine_date');
            });
        }
        
        // Drop indexes for predictive_maintenance_schedules
        if (Schema::hasTable('predictive_maintenance_schedules')) {
            Schema::table('predictive_maintenance_schedules', function (Blueprint $table) {
                $table->dropIndex('idx_pdm_schedule_date_status');
                $table->dropIndex('idx_pdm_schedule_machine_date');
            });
        }
        
        // Drop indexes for work_orders
        if (Schema::hasTable('work_orders')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->dropIndex('idx_work_orders_status');
                $table->dropIndex('idx_work_orders_order_date');
            });
        }
        
        // Drop indexes for downtimes
        if (Schema::hasTable('downtimes')) {
            Schema::table('downtimes', function (Blueprint $table) {
                $table->dropIndex('idx_downtime_date_machine');
                $table->dropIndex('idx_downtime_mechanic_date');
            });
        }
        
        // Drop indexes for downtime_erp
        if (Schema::hasTable('downtime_erp')) {
            Schema::table('downtime_erp', function (Blueprint $table) {
                $table->dropIndex('idx_downtime_erp_date_machine');
                $table->dropIndex('idx_downtime_erp_mechanic_date');
            });
        }
    }
};

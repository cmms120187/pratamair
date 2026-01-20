<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DowntimeErp;
use App\Models\DowntimeErp2;
use Illuminate\Support\Facades\DB;

class UpdateAllDowntimeIncludeOeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Update all existing downtime records to have include_oee = true
     */
    public function run(): void
    {
        $this->command->info("Updating all downtime records to include_oee = Yes...");
        
        try {
            // Update downtime_erp2
            $this->command->info("Updating downtime_erp2...");
            $erp2Count = DowntimeErp2::where('include_oee', false)
                ->orWhereNull('include_oee')
                ->count();
            
            $erp2Updated = DowntimeErp2::where('include_oee', false)
                ->orWhereNull('include_oee')
                ->update(['include_oee' => true]);
            
            $this->command->info("Updated {$erp2Updated} downtime_erp2 records (Total: {$erp2Count})");
            
            // Update downtime_erp (if column exists)
            if (DB::getSchemaBuilder()->hasColumn('downtime_erp', 'include_oee')) {
                $this->command->info("Updating downtime_erp...");
                $erpCount = DB::table('downtime_erp')
                    ->where(function($query) {
                        $query->where('include_oee', false)
                              ->orWhereNull('include_oee');
                    })
                    ->count();
                
                $erpUpdated = DB::table('downtime_erp')
                    ->where(function($query) {
                        $query->where('include_oee', false)
                              ->orWhereNull('include_oee');
                    })
                    ->update(['include_oee' => true]);
                
                $this->command->info("Updated {$erpUpdated} downtime_erp records (Total: {$erpCount})");
            } else {
                $this->command->warn("Column include_oee does not exist in downtime_erp table. Skipping...");
            }
            
            $this->command->info("\n=== Update Summary ===");
            $this->command->info("Downtime ERP2 updated: {$erp2Updated}");
            if (DB::getSchemaBuilder()->hasColumn('downtime_erp', 'include_oee')) {
                $this->command->info("Downtime ERP updated: {$erpUpdated}");
            }
            $this->command->info("\nAll downtime records now have include_oee = Yes!");
            
        } catch (\Exception $e) {
            $this->command->error("Error updating downtime records: " . $e->getMessage());
            throw $e;
        }
    }
}

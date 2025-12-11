<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PredictiveMaintenanceSchedule;
use App\Models\MaintenancePoint;
use Illuminate\Support\Facades\DB;

class FixPredictiveScheduleStandards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'predictive:fix-standards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix standard_id in existing Predictive Maintenance schedules based on maintenance point standard_id';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai perbaikan standard_id pada Predictive Maintenance schedules...');
        
        // Get all schedules with their maintenance points
        $schedules = PredictiveMaintenanceSchedule::with('maintenancePoint')
            ->whereNotNull('maintenance_point_id')
            ->get();
        
        $total = $schedules->count();
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        
        $this->info("Ditemukan {$total} schedule(s) untuk diperiksa.");
        
        DB::beginTransaction();
        
        try {
            foreach ($schedules as $schedule) {
                if (!$schedule->maintenancePoint) {
                    $this->warn("Schedule ID {$schedule->id}: Maintenance point tidak ditemukan, dilewati.");
                    $skipped++;
                    continue;
                }
                
                $maintenancePointStandardId = $schedule->maintenancePoint->standard_id;
                
                if (!$maintenancePointStandardId) {
                    $this->warn("Schedule ID {$schedule->id}: Maintenance point '{$schedule->maintenancePoint->name}' tidak memiliki standard_id, dilewati.");
                    $skipped++;
                    continue;
                }
                
                // Check if standard_id needs to be updated
                if ($schedule->standard_id == $maintenancePointStandardId) {
                    // Already correct, skip
                    continue;
                }
                
                // Update the schedule's standard_id
                $schedule->standard_id = $maintenancePointStandardId;
                $schedule->save();
                
                $updated++;
                $this->line("Schedule ID {$schedule->id}: Updated standard_id from {$schedule->getOriginal('standard_id')} to {$maintenancePointStandardId} (Maintenance Point: {$schedule->maintenancePoint->name})");
            }
            
            DB::commit();
            
            $this->info("\n=== Hasil ===");
            $this->info("Total schedule: {$total}");
            $this->info("Berhasil diupdate: {$updated}");
            $this->info("Dilewati: {$skipped}");
            $this->info("Error: {$errors}");
            $this->info("\nPerbaikan selesai!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: " . $e->getMessage());
            $this->error("Rollback dilakukan. Tidak ada perubahan yang disimpan.");
            return 1;
        }
        
        return 0;
    }
}


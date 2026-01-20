<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Problem;
use App\Models\Reason;
use App\Models\Action;
use Illuminate\Support\Facades\DB;

class ClearProblemReasonActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder will clear all Problem, Reason, and Action data
     * that were created from MASTER PROBLEM.csv
     */
    public function run(): void
    {
        $this->command->info("Clearing Problem, Reason, and Action data...");
        
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        try {
            // Clear pivot table first
            $this->command->info("Clearing problem_system pivot table...");
            DB::table('problem_system')->truncate();
            
            // Clear Actions
            $this->command->info("Clearing Actions...");
            $actionCount = Action::count();
            Action::truncate();
            $this->command->info("Deleted {$actionCount} actions");
            
            // Clear Reasons
            $this->command->info("Clearing Reasons...");
            $reasonCount = Reason::count();
            Reason::truncate();
            $this->command->info("Deleted {$reasonCount} reasons");
            
            // Clear Problems
            $this->command->info("Clearing Problems...");
            $problemCount = Problem::count();
            Problem::truncate();
            $this->command->info("Deleted {$problemCount} problems");
            
            $this->command->info("\n=== Clear Summary ===");
            $this->command->info("Problems deleted: {$problemCount}");
            $this->command->info("Reasons deleted: {$reasonCount}");
            $this->command->info("Actions deleted: {$actionCount}");
            $this->command->info("\nData cleared successfully!");
            
        } catch (\Exception $e) {
            $this->command->error("Error clearing data: " . $e->getMessage());
            throw $e;
        } finally {
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}

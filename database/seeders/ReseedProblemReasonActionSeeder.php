<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ReseedProblemReasonActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder will:
     * 1. Clear all existing Problem, Reason, and Action data
     * 2. Reseed from MASTER PROBLEM.csv with proper system mapping
     */
    public function run(): void
    {
        $this->command->info("=== Starting Reseed Process ===");
        $this->command->info("");
        
        // Step 1: Clear existing data
        $this->command->info("Step 1: Clearing existing data...");
        $this->call(ClearProblemReasonActionSeeder::class);
        
        $this->command->info("");
        $this->command->info("Step 2: Seeding from MASTER PROBLEM.csv...");
        $this->command->info("");
        
        // Step 2: Seed from CSV
        $this->call(MasterProblemSeeder::class);
        
        $this->command->info("");
        $this->command->info("=== Reseed Process Completed ===");
    }
}

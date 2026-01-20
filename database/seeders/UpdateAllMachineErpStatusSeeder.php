<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MachineErp;

class UpdateAllMachineErpStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info("Updating all Machine ERP records to status = Running...");

        // Update all Machine ERP records to status = 'Running'
        $updatedCount = MachineErp::where(function($query) {
            $query->whereNull('status')
                  ->orWhere('status', '!=', 'Running');
        })->update(['status' => 'Running']);

        $totalCount = MachineErp::count();
        $this->command->info("Updated {$updatedCount} Machine ERP records to status = Running (Total: {$totalCount})");
        $this->command->info("\nAll Machine ERP records now have status = Running!");
    }
}

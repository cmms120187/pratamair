<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Basic data
            BasicDataSeeder::class,
            MachineErpSeeder::class,
            StandardsSeeder::class,
            MaintenancePointsSeeder::class,
            UsersSeeder::class,
            RolePermissionsSeeder::class,
            ActivitiesSeeder::class,
            PredictiveMaintenanceSeeder::class,
            PartsErpSeeder::class,
            ProblemsSeeder::class,
            // Existing seeders (keep for backward compatibility)
            SystemSeeder::class,
            InsulationClassElectricMotorSeeder::class,
            ISO10816StandardSeeder::class,
        ]);
    }
}


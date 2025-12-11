<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaintenancePointsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $maintenancePoints = [
            ['id' => 1, 'machine_type_id' => 8, 'category' => 'predictive', 'standard_id' => 2, 'frequency_type' => 'monthly', 'frequency_value' => 1, 'name' => 'Temperatur Motor Penggerak', 'instruction' => 'Cek Suhu Motor, agar motor selalu bekerja sesuai spek Motor', 'photo' => 'maintenance-points/qUh8Z0Y21QFJ6gQzPnrwtcMStvjuDEtZJgAYYL1c.jpg', 'sequence' => 1, 'duration' => null, 'created_at' => '2025-11-27 22:34:06', 'updated_at' => '2025-11-28 17:04:44'],
            ['id' => 2, 'machine_type_id' => 8, 'category' => 'predictive', 'standard_id' => 9, 'frequency_type' => 'monthly', 'frequency_value' => 1, 'name' => 'Getaran Bearing Motor Penggerak (bagian belakang / NDE)', 'instruction' => 'Cek Getaran Motor, agar motor bekerja sesuai standard', 'photo' => 'maintenance-points/pEo55n2AC3iGcKlzDP25trhbruAuOx7U7tB6s2kj.jpg', 'sequence' => 2, 'duration' => null, 'created_at' => '2025-11-28 16:38:15', 'updated_at' => '2025-11-28 17:05:04'],
            ['id' => 3, 'machine_type_id' => 8, 'category' => 'predictive', 'standard_id' => 9, 'frequency_type' => 'monthly', 'frequency_value' => 1, 'name' => 'Getaran Bearing Motor Penggerak (bagian depan / DE)', 'instruction' => 'Cek Getaran Motor, agar motor bekerja sesuai standard', 'photo' => 'maintenance-points/UrtdBGbi2VxSbsUMeQLpC5fWiAkwHutP5CsOtN0P.jpg', 'sequence' => 2, 'duration' => null, 'created_at' => '2025-11-28 16:40:09', 'updated_at' => '2025-11-28 17:05:18'],
            ['id' => 4, 'machine_type_id' => 8, 'category' => 'predictive', 'standard_id' => 13, 'frequency_type' => 'monthly', 'frequency_value' => 1, 'name' => 'Penyebaran Suhu Motor', 'instruction' => 'Cek selisih suhu tertinggi dan terendah', 'photo' => 'maintenance-points/VErNEQRDHYXi31FJgXv7SVxB5cX8AuNaFBEjty61.jpg', 'sequence' => 4, 'duration' => 3, 'created_at' => '2025-11-28 21:14:56', 'updated_at' => '2025-11-30 19:33:02'],
        ];
        DB::table('maintenance_points')->insert($maintenancePoints);
    }
}


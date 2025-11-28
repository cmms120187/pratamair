<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\System;

class SystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systems = [
            ['nama_sistem' => 'Mechanical', 'deskripsi' => 'Mechanical system components including gears, bearings, belts, chains, etc.'],
            ['nama_sistem' => 'Electrical', 'deskripsi' => 'Electrical system components including motors, contactors, sensors, cables, etc.'],
            ['nama_sistem' => 'Hydraulic', 'deskripsi' => 'Hydraulic system components including pumps, cylinders, valves, hoses, etc.'],
            ['nama_sistem' => 'Pneumatic', 'deskripsi' => 'Pneumatic system components including cylinders, valves, filters, compressors, etc.'],
            ['nama_sistem' => 'Electronic', 'deskripsi' => 'Electronic system components including PLC, HMI, encoders, servo drives, etc.'],
            ['nama_sistem' => 'Cooling', 'deskripsi' => 'Cooling system components including fans, heat exchangers, chillers, etc.'],
            ['nama_sistem' => 'Lubrication', 'deskripsi' => 'Lubrication system components including oil pumps, filters, grease systems, etc.'],
            ['nama_sistem' => 'Heating', 'deskripsi' => 'Heating system components including burners, heating elements, thermocouples, temperature controllers, etc.'],
        ];

        foreach ($systems as $system) {
            System::firstOrCreate(
                ['nama_sistem' => $system['nama_sistem']],
                $system
            );
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PartsErpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parts = [
            ['id' => 1, 'part_number' => 'X00616823200', 'name' => 'RELAY', 'description' => 'LY-4  220V "OMRON"', 'category' => 'Electrical / Instrument', 'brand' => 'OMRON', 'unit' => 'EA', 'stock' => 0, 'price' => null, 'location' => null, 'created_at' => '2025-11-27 02:20:34', 'updated_at' => '2025-11-27 18:54:24'],
            ['id' => 2, 'part_number' => 'X006168DG600', 'name' => 'RELAY', 'description' => 'LY-4N 220V  (ada lampu) "OMRON"', 'category' => 'Electrical / Instrument', 'brand' => 'OMRON', 'unit' => 'EA', 'stock' => 0, 'price' => null, 'location' => null, 'created_at' => '2025-11-27 02:20:34', 'updated_at' => '2025-11-27 18:54:33'],
            ['id' => 3, 'part_number' => 'X006071G7O00', 'name' => 'CURRENT TRANSFORMER', 'description' => '200/5 A', 'category' => 'Electronic', 'brand' => '-', 'unit' => 'EA', 'stock' => 0, 'price' => null, 'location' => null, 'created_at' => '2025-11-27 02:20:34', 'updated_at' => '2025-11-27 18:52:24'],
            ['id' => 4, 'part_number' => 'X006131E5K00', 'name' => 'MAGNETIC CONTACTOR', 'description' => 'MC 100A METASOL', 'category' => 'Electrical / Instrument', 'brand' => 'METASOL', 'unit' => 'EA', 'stock' => 0, 'price' => null, 'location' => null, 'created_at' => '2025-11-27 02:20:34', 'updated_at' => '2025-11-27 18:53:51'],
            ['id' => 5, 'part_number' => 'X006131DS100', 'name' => 'MAGNETIC CONTACTOR', 'description' => 'MC 130A METASOL', 'category' => 'Electrical / Instrument', 'brand' => 'METASOL', 'unit' => 'EA', 'stock' => 0, 'price' => null, 'location' => null, 'created_at' => '2025-11-27 02:20:34', 'updated_at' => '2025-11-27 18:52:52'],
            ['id' => 6, 'part_number' => 'X008049K3200', 'name' => 'FITTING HOSE "SANG-A"', 'description' => 'PC 12-03', 'category' => 'Pneumatic', 'brand' => '-', 'unit' => 'EA', 'stock' => 0, 'price' => null, 'location' => null, 'created_at' => '2025-11-27 02:20:34', 'updated_at' => '2025-11-27 19:04:55'],
            ['id' => 7, 'part_number' => 'X00824012700', 'name' => 'BEARING "NTN"', 'description' => '6204 ZZ', 'category' => 'Mechanical', 'brand' => 'NTN', 'unit' => 'EA', 'stock' => 0, 'price' => null, 'location' => null, 'created_at' => '2025-11-27 02:20:34', 'updated_at' => '2025-11-27 19:05:29'],
            ['id' => 8, 'part_number' => 'X008E76W6A00', 'name' => 'BEARING "FAG"', 'description' => '6316 2RS', 'category' => 'Mechanical', 'brand' => 'FAG', 'unit' => 'EA', 'stock' => 0, 'price' => null, 'location' => null, 'created_at' => '2025-11-27 02:20:34', 'updated_at' => '2025-11-27 19:05:46'],
            ['id' => 9, 'part_number' => 'X008663U1300', 'name' => 'RUBBER COUPLING', 'description' => 'GRH-100 25x20x10t', 'category' => 'Mechanical', 'brand' => 'HANSHIN', 'unit' => 'EA', 'stock' => 0, 'price' => null, 'location' => null, 'created_at' => '2025-11-27 02:20:34', 'updated_at' => '2025-11-27 19:05:37'],
            ['id' => 10, 'part_number' => 'X008146E6F00', 'name' => 'SOLENOIDE VALVE', 'description' => 'HA 212 AC 220 50/60Hz PT 1/4" "HANSHIN"', 'category' => 'Mechanical', 'brand' => 'HANSHIN', 'unit' => 'EA', 'stock' => 0, 'price' => null, 'location' => null, 'created_at' => '2025-11-27 02:20:34', 'updated_at' => '2025-11-27 19:05:14'],
            ['id' => 11, 'part_number' => 'X00607704200', 'name' => 'BREAKER `LG` ABE', 'description' => '225A  3P', 'category' => 'Electrical / Instrument', 'brand' => 'LG', 'unit' => 'EA', 'stock' => 0, 'price' => null, 'location' => null, 'created_at' => '2025-11-27 02:20:34', 'updated_at' => '2025-11-27 18:52:39'],
            ['id' => 12, 'part_number' => 'X006135ZZ900', 'name' => 'MAGNETIC CONTACTOR "LG/LS"', 'description' => 'MC-12B 220V', 'category' => 'Electrical / Instrument', 'brand' => 'LG', 'unit' => 'EA', 'stock' => 0, 'price' => null, 'location' => null, 'created_at' => '2025-11-27 02:20:34', 'updated_at' => '2025-11-27 18:54:14'],
            ['id' => 13, 'part_number' => 'X00904134600', 'name' => 'BALL VALVE', 'description' => 'O 2" `ONDA`', 'category' => 'Mechanical', 'brand' => 'ONDA', 'unit' => 'EA', 'stock' => 0, 'price' => null, 'location' => null, 'created_at' => '2025-11-27 02:20:34', 'updated_at' => '2025-11-27 19:05:59'],
            ['id' => 14, 'part_number' => 'X00824012600', 'name' => 'BEARING "NTN"', 'description' => '6203 ZZ', 'category' => 'Mechanical', 'brand' => 'NTN', 'unit' => 'EA', 'stock' => 0, 'price' => null, 'location' => null, 'created_at' => '2025-11-27 02:20:34', 'updated_at' => '2025-11-27 19:05:22'],
            ['id' => 15, 'part_number' => 'X006135AA100', 'name' => 'MAGNETIC CONTACTOR "LG/LS"', 'description' => 'MC-18B 220V', 'category' => 'Electrical / Instrument', 'brand' => 'LG', 'unit' => 'EA', 'stock' => 0, 'price' => null, 'location' => null, 'created_at' => '2025-11-27 02:20:34', 'updated_at' => '2025-11-27 18:54:07'],
            ['id' => 16, 'part_number' => 'X006032Y8H00', 'name' => 'CABLE NYYHY', 'description' => '1.5 SQMM x 3C "SUPREME"', 'category' => 'Electrical / Instrument', 'brand' => 'SUPREME', 'unit' => 'MTR', 'stock' => 0, 'price' => null, 'location' => null, 'created_at' => '2025-11-27 02:20:34', 'updated_at' => '2025-11-27 18:50:32'],
            ['id' => 17, 'part_number' => 'X00623341400', 'name' => 'TIMER `OMRON`', 'description' => 'H3BA-N8', 'category' => 'Electrical / Instrument', 'brand' => 'OMRON', 'unit' => 'EA', 'stock' => 0, 'price' => null, 'location' => null, 'created_at' => '2025-11-27 02:20:34', 'updated_at' => '2025-11-27 18:58:21'],
            ['id' => 18, 'part_number' => 'X006216B2600', 'name' => 'SCUND RING', 'description' => 'SC 25 - 8', 'category' => 'Electrical / Instrument', 'brand' => '-', 'unit' => 'EA', 'stock' => 0, 'price' => null, 'location' => null, 'created_at' => '2025-11-27 02:20:34', 'updated_at' => '2025-11-27 18:54:40'],
            ['id' => 19, 'part_number' => 'X006216DH300', 'name' => 'SCUND RING', 'description' => 'SC 50 - 8', 'category' => 'Electrical / Instrument', 'brand' => '-', 'unit' => 'EA', 'stock' => 0, 'price' => null, 'location' => null, 'created_at' => '2025-11-27 02:20:34', 'updated_at' => '2025-11-27 18:54:50'],
        ];
        DB::table('part_erp')->insert($parts);

        // Part ERP Machine Type
        $partMachineTypes = [
            ['id' => 1, 'part_erp_id' => 16, 'machine_type_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['id' => 2, 'part_erp_id' => 16, 'machine_type_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['id' => 3, 'part_erp_id' => 16, 'machine_type_id' => 7, 'created_at' => null, 'updated_at' => null],
            ['id' => 4, 'part_erp_id' => 3, 'machine_type_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['id' => 5, 'part_erp_id' => 3, 'machine_type_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['id' => 6, 'part_erp_id' => 11, 'machine_type_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['id' => 7, 'part_erp_id' => 11, 'machine_type_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['id' => 8, 'part_erp_id' => 5, 'machine_type_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['id' => 9, 'part_erp_id' => 5, 'machine_type_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['id' => 10, 'part_erp_id' => 4, 'machine_type_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['id' => 11, 'part_erp_id' => 4, 'machine_type_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['id' => 12, 'part_erp_id' => 15, 'machine_type_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['id' => 13, 'part_erp_id' => 15, 'machine_type_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['id' => 14, 'part_erp_id' => 12, 'machine_type_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['id' => 15, 'part_erp_id' => 12, 'machine_type_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['id' => 16, 'part_erp_id' => 1, 'machine_type_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['id' => 17, 'part_erp_id' => 1, 'machine_type_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['id' => 18, 'part_erp_id' => 2, 'machine_type_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['id' => 19, 'part_erp_id' => 2, 'machine_type_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['id' => 20, 'part_erp_id' => 18, 'machine_type_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['id' => 21, 'part_erp_id' => 18, 'machine_type_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['id' => 22, 'part_erp_id' => 19, 'machine_type_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['id' => 23, 'part_erp_id' => 19, 'machine_type_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['id' => 24, 'part_erp_id' => 17, 'machine_type_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['id' => 25, 'part_erp_id' => 17, 'machine_type_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['id' => 26, 'part_erp_id' => 6, 'machine_type_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['id' => 27, 'part_erp_id' => 6, 'machine_type_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['id' => 28, 'part_erp_id' => 10, 'machine_type_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['id' => 29, 'part_erp_id' => 10, 'machine_type_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['id' => 30, 'part_erp_id' => 14, 'machine_type_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['id' => 31, 'part_erp_id' => 14, 'machine_type_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['id' => 32, 'part_erp_id' => 7, 'machine_type_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['id' => 33, 'part_erp_id' => 7, 'machine_type_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['id' => 34, 'part_erp_id' => 9, 'machine_type_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['id' => 35, 'part_erp_id' => 9, 'machine_type_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['id' => 36, 'part_erp_id' => 8, 'machine_type_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['id' => 37, 'part_erp_id' => 8, 'machine_type_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['id' => 38, 'part_erp_id' => 13, 'machine_type_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['id' => 39, 'part_erp_id' => 13, 'machine_type_id' => 6, 'created_at' => null, 'updated_at' => null],
        ];
        DB::table('part_erp_machine_type')->insert($partMachineTypes);
    }
}

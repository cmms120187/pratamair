<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BasicDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Plants
        $plants = [
            ['id' => 1, 'name' => 'Plant A', 'created_at' => '2025-11-26 17:37:32', 'updated_at' => '2025-11-26 17:41:54'],
            ['id' => 2, 'name' => 'Plant B', 'created_at' => '2025-11-26 17:37:50', 'updated_at' => '2025-11-26 17:37:50'],
            ['id' => 3, 'name' => 'Plant C', 'created_at' => '2025-11-26 17:40:22', 'updated_at' => '2025-11-26 17:40:22'],
            ['id' => 4, 'name' => 'Plant D', 'created_at' => '2025-11-26 17:40:28', 'updated_at' => '2025-11-26 17:40:28'],
            ['id' => 5, 'name' => 'Plant E1', 'created_at' => '2025-11-26 17:40:36', 'updated_at' => '2025-11-26 17:40:36'],
            ['id' => 6, 'name' => 'Plant E2', 'created_at' => '2025-11-26 17:40:43', 'updated_at' => '2025-11-26 17:40:43'],
            ['id' => 7, 'name' => 'Plant E3', 'created_at' => '2025-11-26 17:40:47', 'updated_at' => '2025-11-26 17:40:47'],
            ['id' => 8, 'name' => 'Plant F', 'created_at' => '2025-11-26 17:40:52', 'updated_at' => '2025-11-26 17:40:52'],
            ['id' => 9, 'name' => 'Mess Korea', 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 10, 'name' => 'Engineering', 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 11, 'name' => 'PCC', 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
        ];
        DB::table('plants')->insert($plants);

        // Processes
        $processes = [
            ['id' => 1, 'name' => 'Utility', 'created_at' => '2025-11-26 17:41:03', 'updated_at' => '2025-11-26 17:41:03'],
            ['id' => 2, 'name' => 'Supporting', 'created_at' => '2025-11-27 19:44:49', 'updated_at' => '2025-11-27 19:44:49'],
        ];
        DB::table('processes')->insert($processes);

        // Lines
        $lines = [
            ['id' => 1, 'name' => 'Area Utility PA', 'plant_id' => 1, 'process_id' => 1, 'created_at' => '2025-11-26 17:42:09', 'updated_at' => '2025-11-26 17:42:57'],
            ['id' => 2, 'name' => 'Area Utility PB', 'plant_id' => 2, 'process_id' => 1, 'created_at' => '2025-11-26 17:43:07', 'updated_at' => '2025-11-26 17:43:07'],
            ['id' => 3, 'name' => 'Area Utility PC', 'plant_id' => 3, 'process_id' => 1, 'created_at' => '2025-11-26 17:43:16', 'updated_at' => '2025-11-26 17:43:16'],
            ['id' => 4, 'name' => 'Area Utility PD', 'plant_id' => 4, 'process_id' => 1, 'created_at' => '2025-11-26 17:43:29', 'updated_at' => '2025-11-26 17:43:29'],
            ['id' => 5, 'name' => 'Area Utility PE1', 'plant_id' => 5, 'process_id' => 1, 'created_at' => '2025-11-26 17:43:56', 'updated_at' => '2025-11-26 17:43:56'],
            ['id' => 6, 'name' => 'Area Utility PE2', 'plant_id' => 6, 'process_id' => 1, 'created_at' => '2025-11-26 17:44:05', 'updated_at' => '2025-11-26 17:44:05'],
            ['id' => 7, 'name' => 'Area Utility PE3', 'plant_id' => 7, 'process_id' => 1, 'created_at' => '2025-11-26 17:44:17', 'updated_at' => '2025-11-26 17:44:17'],
            ['id' => 8, 'name' => 'Area Utility PF', 'plant_id' => 8, 'process_id' => 1, 'created_at' => '2025-11-26 17:44:25', 'updated_at' => '2025-11-26 17:44:25'],
            ['id' => 9, 'name' => 'Utility', 'plant_id' => 6, 'process_id' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 10, 'name' => 'Utility', 'plant_id' => 1, 'process_id' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 11, 'name' => 'Utility', 'plant_id' => 7, 'process_id' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 12, 'name' => 'Utility', 'plant_id' => 3, 'process_id' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 13, 'name' => 'Utility', 'plant_id' => 2, 'process_id' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 14, 'name' => 'Utility', 'plant_id' => 4, 'process_id' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 15, 'name' => 'Utility', 'plant_id' => 9, 'process_id' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 16, 'name' => 'Utility', 'plant_id' => 10, 'process_id' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 17, 'name' => 'Utility', 'plant_id' => 5, 'process_id' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 18, 'name' => 'Utility', 'plant_id' => 8, 'process_id' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 19, 'name' => 'Utility', 'plant_id' => 11, 'process_id' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
        ];
        DB::table('lines')->insert($lines);

        // Rooms
        $rooms = [
            ['id' => 1, 'name' => 'Compressor Room', 'category' => 'Supporting', 'plant_id' => 1, 'line_id' => 1, 'process_id' => null, 'description' => null, 'created_at' => '2025-11-26 17:45:06', 'updated_at' => '2025-11-26 17:45:06'],
            ['id' => 2, 'name' => 'Utility Mixing', 'category' => 'Utility', 'plant_id' => 6, 'line_id' => 9, 'process_id' => null, 'description' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 3, 'name' => 'Utility Rubber', 'category' => 'Utility', 'plant_id' => 6, 'line_id' => 9, 'process_id' => null, 'description' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 4, 'name' => 'Utility Laminating', 'category' => 'Utility', 'plant_id' => 1, 'line_id' => 10, 'process_id' => null, 'description' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 5, 'name' => 'Panel IP', 'category' => 'Utility', 'plant_id' => 7, 'line_id' => 11, 'process_id' => null, 'description' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 6, 'name' => 'Panel PJ112', 'category' => 'Utility', 'plant_id' => 3, 'line_id' => 12, 'process_id' => null, 'description' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 7, 'name' => 'Utility PA', 'category' => 'Utility', 'plant_id' => 1, 'line_id' => 10, 'process_id' => null, 'description' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 8, 'name' => 'Utility PB', 'category' => 'Utility', 'plant_id' => 2, 'line_id' => 13, 'process_id' => null, 'description' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 9, 'name' => 'Utility PC', 'category' => 'Utility', 'plant_id' => 3, 'line_id' => 12, 'process_id' => null, 'description' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 10, 'name' => 'Utility PD', 'category' => 'Utility', 'plant_id' => 4, 'line_id' => 14, 'process_id' => null, 'description' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 11, 'name' => 'Pompa Banjir', 'category' => 'Utility', 'plant_id' => 9, 'line_id' => 15, 'process_id' => null, 'description' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 12, 'name' => 'Panel MDB', 'category' => 'Utility', 'plant_id' => 10, 'line_id' => 16, 'process_id' => null, 'description' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 13, 'name' => 'Workshop', 'category' => 'Utility', 'plant_id' => 10, 'line_id' => 16, 'process_id' => null, 'description' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 14, 'name' => 'Utility Stockfit', 'category' => 'Utility', 'plant_id' => 5, 'line_id' => 17, 'process_id' => null, 'description' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 15, 'name' => 'Utility Grinding', 'category' => 'Utility', 'plant_id' => 5, 'line_id' => 17, 'process_id' => null, 'description' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 16, 'name' => 'Utility 2nd Process', 'category' => 'Utility', 'plant_id' => 8, 'line_id' => 18, 'process_id' => null, 'description' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 17, 'name' => 'Utility IDC', 'category' => 'Utility', 'plant_id' => 11, 'line_id' => 19, 'process_id' => null, 'description' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
            ['id' => 18, 'name' => 'Utility Press O/S', 'category' => 'Utility', 'plant_id' => 6, 'line_id' => 9, 'process_id' => null, 'description' => null, 'created_at' => '2025-11-27 18:39:28', 'updated_at' => '2025-11-27 18:39:28'],
        ];
        DB::table('rooms')->insert($rooms);

        // Room ERP
        $roomErp = [
            ['id' => 1, 'kode_room' => '*001612*', 'name' => 'Utility Mixing', 'category' => 'Utility', 'plant_name' => 'Plant E2', 'line_name' => 'Utility', 'process_name' => 'Supporting', 'description' => null, 'created_at' => '2025-11-26 20:45:37', 'updated_at' => '2025-11-26 20:45:37'],
            ['id' => 2, 'kode_room' => '*001615*', 'name' => 'Utility Rubber', 'category' => 'Utility', 'plant_name' => 'Plant E2', 'line_name' => 'Utility', 'process_name' => 'Supporting', 'description' => null, 'created_at' => '2025-11-26 20:45:37', 'updated_at' => '2025-11-26 20:45:37'],
            ['id' => 3, 'kode_room' => '*001544*', 'name' => 'Utility Laminating', 'category' => 'Utility', 'plant_name' => 'Plant A', 'line_name' => 'Utility', 'process_name' => 'Supporting', 'description' => null, 'created_at' => '2025-11-26 20:45:37', 'updated_at' => '2025-11-26 20:45:37'],
            ['id' => 4, 'kode_room' => '*001617*', 'name' => 'Panel IP', 'category' => 'Utility', 'plant_name' => 'Plant E3', 'line_name' => 'Utility', 'process_name' => 'Supporting', 'description' => null, 'created_at' => '2025-11-26 20:45:37', 'updated_at' => '2025-11-26 20:45:37'],
            ['id' => 5, 'kode_room' => '*001618*', 'name' => 'Panel PJ112', 'category' => 'Utility', 'plant_name' => 'Plant C', 'line_name' => 'Utility', 'process_name' => 'Supporting', 'description' => null, 'created_at' => '2025-11-26 20:45:37', 'updated_at' => '2025-11-26 20:45:37'],
            ['id' => 6, 'kode_room' => '*001606*', 'name' => 'Utility PA', 'category' => 'Utility', 'plant_name' => 'Plant A', 'line_name' => 'Utility', 'process_name' => 'Supporting', 'description' => null, 'created_at' => '2025-11-26 20:45:37', 'updated_at' => '2025-11-26 20:45:37'],
            ['id' => 7, 'kode_room' => '*001607*', 'name' => 'Utility PB', 'category' => 'Utility', 'plant_name' => 'Plant B', 'line_name' => 'Utility', 'process_name' => 'Supporting', 'description' => null, 'created_at' => '2025-11-26 20:45:37', 'updated_at' => '2025-11-26 20:45:37'],
            ['id' => 8, 'kode_room' => '*001608*', 'name' => 'Utility PC', 'category' => 'Utility', 'plant_name' => 'Plant C', 'line_name' => 'Utility', 'process_name' => 'Supporting', 'description' => null, 'created_at' => '2025-11-26 20:45:37', 'updated_at' => '2025-11-26 20:45:37'],
            ['id' => 9, 'kode_room' => '*001609*', 'name' => 'Utility PD', 'category' => 'Utility', 'plant_name' => 'Plant D', 'line_name' => 'Utility', 'process_name' => 'Supporting', 'description' => null, 'created_at' => '2025-11-26 20:45:37', 'updated_at' => '2025-11-26 20:45:37'],
            ['id' => 10, 'kode_room' => '*001347*', 'name' => 'Pompa Banjir', 'category' => 'Utility', 'plant_name' => 'Mess Korea', 'line_name' => 'Utility', 'process_name' => 'Supporting', 'description' => null, 'created_at' => '2025-11-26 20:45:37', 'updated_at' => '2025-11-26 20:45:37'],
            ['id' => 11, 'kode_room' => '*000078*', 'name' => 'Panel MDB', 'category' => 'Utility', 'plant_name' => 'Engineering', 'line_name' => 'Utility', 'process_name' => 'Supporting', 'description' => null, 'created_at' => '2025-11-26 20:45:37', 'updated_at' => '2025-11-26 20:45:37'],
            ['id' => 12, 'kode_room' => '*000456*', 'name' => 'Workshop', 'category' => 'Utility', 'plant_name' => 'Engineering', 'line_name' => 'Utility', 'process_name' => 'Supporting', 'description' => null, 'created_at' => '2025-11-26 20:45:37', 'updated_at' => '2025-11-26 20:45:37'],
            ['id' => 13, 'kode_room' => '*001616*', 'name' => 'Utility Stockfit', 'category' => 'Utility', 'plant_name' => 'Plant E1', 'line_name' => 'Utility', 'process_name' => 'Supporting', 'description' => null, 'created_at' => '2025-11-26 20:45:37', 'updated_at' => '2025-11-26 20:45:37'],
            ['id' => 14, 'kode_room' => '*001611*', 'name' => 'Utility Grinding', 'category' => 'Utility', 'plant_name' => 'Plant E1', 'line_name' => 'Utility', 'process_name' => 'Supporting', 'description' => null, 'created_at' => '2025-11-26 20:45:37', 'updated_at' => '2025-11-26 20:45:37'],
            ['id' => 15, 'kode_room' => '*001614*', 'name' => 'Utility 2nd Process', 'category' => 'Utility', 'plant_name' => 'Plant F', 'line_name' => 'Utility', 'process_name' => 'Supporting', 'description' => null, 'created_at' => '2025-11-26 20:45:37', 'updated_at' => '2025-11-26 20:45:37'],
            ['id' => 16, 'kode_room' => '*001610*', 'name' => 'Utility IDC', 'category' => 'Utility', 'plant_name' => 'PCC', 'line_name' => 'Utility', 'process_name' => 'Supporting', 'description' => null, 'created_at' => '2025-11-26 20:45:37', 'updated_at' => '2025-11-26 20:45:37'],
            ['id' => 17, 'kode_room' => '*001613*', 'name' => 'Utility Press O/S', 'category' => 'Utility', 'plant_name' => 'Plant E2', 'line_name' => 'Utility', 'process_name' => 'Supporting', 'description' => null, 'created_at' => '2025-11-26 20:45:37', 'updated_at' => '2025-11-26 20:45:37'],
        ];
        DB::table('room_erp')->insert($roomErp);

        // Brands
        $brands = [
            ['id' => 1, 'name' => 'HANSHIN', 'created_at' => '2025-11-27 02:18:53', 'updated_at' => '2025-11-27 02:18:53'],
            ['id' => 2, 'name' => 'ALPHA OMEGA', 'created_at' => '2025-11-27 17:00:52', 'updated_at' => '2025-11-27 17:00:52'],
            ['id' => 3, 'name' => 'SIN POONG', 'created_at' => '2025-11-27 17:00:52', 'updated_at' => '2025-11-27 17:00:52'],
            ['id' => 4, 'name' => 'HANSIN', 'created_at' => '2025-11-27 17:00:52', 'updated_at' => '2025-11-27 17:00:52'],
            ['id' => 5, 'name' => 'CUMMINS', 'created_at' => '2025-11-27 17:00:52', 'updated_at' => '2025-11-27 17:00:52'],
            ['id' => 6, 'name' => 'CATERPILLAR', 'created_at' => '2025-11-27 17:00:52', 'updated_at' => '2025-11-27 17:00:52'],
        ];
        DB::table('brands')->insert($brands);

        // Groups
        $groups = [
            ['id' => 1, 'name' => 'Compressing', 'created_at' => '2025-11-26 23:38:15', 'updated_at' => '2025-11-26 23:38:15'],
            ['id' => 2, 'name' => 'Exhaust', 'created_at' => '2025-11-26 23:38:38', 'updated_at' => '2025-11-26 23:38:38'],
            ['id' => 3, 'name' => 'Boiler', 'created_at' => '2025-11-27 00:33:58', 'updated_at' => '2025-11-27 00:33:58'],
            ['id' => 4, 'name' => 'Genset', 'created_at' => '2025-11-27 00:34:17', 'updated_at' => '2025-11-27 00:34:17'],
        ];
        DB::table('groups')->insert($groups);

        // Systems
        $systems = [
            ['id' => 1, 'nama_sistem' => 'Mechanical', 'deskripsi' => 'Mechanical system components including gears, bearings, belts, chains, etc.', 'created_at' => '2025-11-26 17:35:11', 'updated_at' => '2025-11-26 17:35:11'],
            ['id' => 2, 'nama_sistem' => 'Electrical / Instrument', 'deskripsi' => 'Electrical system components including motors, contactors, sensors, cables, etc.', 'created_at' => '2025-11-26 17:35:11', 'updated_at' => '2025-11-26 17:52:44'],
            ['id' => 3, 'nama_sistem' => 'Hydraulic', 'deskripsi' => 'Hydraulic system components including pumps, cylinders, valves, hoses, etc.', 'created_at' => '2025-11-26 17:35:11', 'updated_at' => '2025-11-26 17:35:11'],
            ['id' => 4, 'nama_sistem' => 'Pneumatic', 'deskripsi' => 'Pneumatic system components including cylinders, valves, filters, compressors, etc.', 'created_at' => '2025-11-26 17:35:11', 'updated_at' => '2025-11-26 17:35:11'],
            ['id' => 5, 'nama_sistem' => 'Electronic', 'deskripsi' => 'Electronic system components including PLC, HMI, encoders, servo drives, etc.', 'created_at' => '2025-11-26 17:35:11', 'updated_at' => '2025-11-26 17:35:11'],
            ['id' => 6, 'nama_sistem' => 'Cooling', 'deskripsi' => 'Cooling system components including fans, heat exchangers, chillers, etc.', 'created_at' => '2025-11-26 17:35:11', 'updated_at' => '2025-11-26 17:35:11'],
            ['id' => 7, 'nama_sistem' => 'Lubrication', 'deskripsi' => 'Lubrication system components including oil pumps, filters, grease systems, etc.', 'created_at' => '2025-11-26 17:35:11', 'updated_at' => '2025-11-26 17:35:11'],
            ['id' => 8, 'nama_sistem' => 'Heating', 'deskripsi' => 'Heating system components including burners, heating elements, thermocouples, temperature controllers, etc.', 'created_at' => '2025-11-26 17:35:11', 'updated_at' => '2025-11-26 17:35:11'],
            ['id' => 9, 'nama_sistem' => 'Handling & Sircutalion (Piping)', 'deskripsi' => 'Sistem yang memindahkan material atau produk di dalam fasilitas, seperti konveyor, crane, forklift, robotics, dan sistem penyimpanan otomatis.', 'created_at' => '2025-11-26 17:53:26', 'updated_at' => '2025-11-27 19:04:30'],
            ['id' => 10, 'nama_sistem' => 'Safety', 'deskripsi' => 'Komponen dan fitur yang dirancang untuk melindungi operator dan mesin, termasuk emergency stop (E-stop), safety interlocks, guarding, dan pressure relief valves.', 'created_at' => '2025-11-26 17:54:11', 'updated_at' => '2025-11-26 17:54:11'],
        ];
        DB::table('systems')->insert($systems);

        // Group System
        $groupSystem = [
            ['id' => 1, 'group_id' => 1, 'system_id' => 2, 'created_at' => null, 'updated_at' => null],
            ['id' => 2, 'group_id' => 1, 'system_id' => 7, 'created_at' => null, 'updated_at' => null],
            ['id' => 3, 'group_id' => 1, 'system_id' => 1, 'created_at' => null, 'updated_at' => null],
            ['id' => 4, 'group_id' => 1, 'system_id' => 4, 'created_at' => null, 'updated_at' => null],
            ['id' => 5, 'group_id' => 2, 'system_id' => 2, 'created_at' => null, 'updated_at' => null],
            ['id' => 6, 'group_id' => 2, 'system_id' => 7, 'created_at' => null, 'updated_at' => null],
            ['id' => 7, 'group_id' => 2, 'system_id' => 1, 'created_at' => null, 'updated_at' => null],
            ['id' => 8, 'group_id' => 3, 'system_id' => 2, 'created_at' => null, 'updated_at' => null],
            ['id' => 9, 'group_id' => 3, 'system_id' => 8, 'created_at' => null, 'updated_at' => null],
            ['id' => 10, 'group_id' => 3, 'system_id' => 7, 'created_at' => null, 'updated_at' => null],
            ['id' => 11, 'group_id' => 3, 'system_id' => 1, 'created_at' => null, 'updated_at' => null],
            ['id' => 12, 'group_id' => 4, 'system_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['id' => 13, 'group_id' => 4, 'system_id' => 2, 'created_at' => null, 'updated_at' => null],
            ['id' => 14, 'group_id' => 4, 'system_id' => 5, 'created_at' => null, 'updated_at' => null],
            ['id' => 15, 'group_id' => 4, 'system_id' => 7, 'created_at' => null, 'updated_at' => null],
            ['id' => 16, 'group_id' => 4, 'system_id' => 1, 'created_at' => null, 'updated_at' => null],
        ];
        DB::table('group_system')->insert($groupSystem);

        // Machine Types
        $machineTypes = [
            ['id' => 6, 'name' => 'ECO AIR COMPRESSOR', 'group_id' => 1, 'model' => 'D-100', 'group' => null, 'brand' => 'HANSIN', 'description' => null, 'photo' => null, 'created_at' => '2025-11-27 00:32:57', 'updated_at' => '2025-11-27 00:33:34'],
            ['id' => 7, 'name' => 'Thermal Oil Heater', 'group_id' => 3, 'model' => 'S2.63.5', 'group' => null, 'brand' => 'ALPHA OMEGA', 'description' => null, 'photo' => 'machine-types/3t2ogL1kQw1N8RBtX1qsUV9ZbvvQY3PG5ZvDMBAW.jpg', 'created_at' => '2025-11-27 00:33:13', 'updated_at' => '2025-11-27 02:12:03'],
            ['id' => 8, 'name' => 'Compressor', 'group_id' => 1, 'model' => 'GRH2-100A', 'group' => null, 'brand' => 'HANSHIN', 'description' => null, 'photo' => 'machine-types/W2fabLVBbCTN4GuCDxZGwGplsVWg4xszqVEuUIgm.jpg', 'created_at' => '2025-11-27 00:33:13', 'updated_at' => '2025-11-27 02:18:01'],
            ['id' => 9, 'name' => 'Generator Set', 'group_id' => 4, 'model' => '200KV ABC C 200 "CUMMINS"', 'group' => null, 'brand' => 'CUMMINS', 'description' => null, 'photo' => 'machine-types/xtaSgg7ON5fodwdQqGKaO9l0nUzrPYe6sMSezdum.jpg', 'created_at' => '2025-11-27 00:33:13', 'updated_at' => '2025-11-27 02:10:58'],
        ];
        DB::table('machine_types')->insert($machineTypes);

        // Models
        $models = [
            ['id' => 1, 'name' => 'GRH2-100A', 'brand_id' => 1, 'type_id' => 8, 'photo' => null, 'created_at' => '2025-11-27 02:18:53', 'updated_at' => '2025-11-27 02:18:53'],
            ['id' => 2, 'name' => 'S2.63.5', 'brand_id' => 2, 'type_id' => 7, 'photo' => null, 'created_at' => '2025-11-27 17:01:05', 'updated_at' => '2025-11-27 17:01:05'],
            ['id' => 3, 'name' => 'SP-H50', 'brand_id' => 3, 'type_id' => 7, 'photo' => null, 'created_at' => '2025-11-27 17:01:05', 'updated_at' => '2025-11-27 17:01:05'],
            ['id' => 5, 'name' => 'GRH3-100A', 'brand_id' => 1, 'type_id' => 8, 'photo' => null, 'created_at' => '2025-11-27 17:01:05', 'updated_at' => '2025-11-27 17:01:23'],
            ['id' => 9, 'name' => 'D-100', 'brand_id' => 1, 'type_id' => 6, 'photo' => null, 'created_at' => '2025-11-27 17:01:06', 'updated_at' => '2025-11-27 17:01:34'],
            ['id' => 11, 'name' => 'GRH-3 30A', 'brand_id' => 1, 'type_id' => 8, 'photo' => null, 'created_at' => '2025-11-27 17:01:06', 'updated_at' => '2025-11-27 17:01:45'],
            ['id' => 12, 'name' => '200KV ABC C 200 "CUMMINS"', 'brand_id' => 5, 'type_id' => 9, 'photo' => null, 'created_at' => '2025-11-27 17:01:06', 'updated_at' => '2025-11-27 17:01:06'],
            ['id' => 13, 'name' => 'CUMMIN KTA 19G4 500KVA/400KW', 'brand_id' => 5, 'type_id' => 9, 'photo' => null, 'created_at' => '2025-11-27 17:01:06', 'updated_at' => '2025-11-27 17:01:06'],
            ['id' => 14, 'name' => 'CAT  3516 ,1.825 KVA', 'brand_id' => 6, 'type_id' => 9, 'photo' => null, 'created_at' => '2025-11-27 17:01:06', 'updated_at' => '2025-11-27 17:01:06'],
        ];
        DB::table('models')->insert($models);

        // Machine Type System
        $machineTypeSystem = [
            ['id' => 1, 'machine_type_id' => 8, 'system_id' => 1, 'created_at' => null, 'updated_at' => null],
            ['id' => 2, 'machine_type_id' => 8, 'system_id' => 2, 'created_at' => null, 'updated_at' => null],
            ['id' => 3, 'machine_type_id' => 8, 'system_id' => 4, 'created_at' => null, 'updated_at' => null],
            ['id' => 4, 'machine_type_id' => 8, 'system_id' => 7, 'created_at' => null, 'updated_at' => null],
            ['id' => 5, 'machine_type_id' => 6, 'system_id' => 1, 'created_at' => null, 'updated_at' => null],
            ['id' => 6, 'machine_type_id' => 6, 'system_id' => 2, 'created_at' => null, 'updated_at' => null],
            ['id' => 7, 'machine_type_id' => 6, 'system_id' => 4, 'created_at' => null, 'updated_at' => null],
            ['id' => 8, 'machine_type_id' => 6, 'system_id' => 7, 'created_at' => null, 'updated_at' => null],
            ['id' => 9, 'machine_type_id' => 9, 'system_id' => 1, 'created_at' => null, 'updated_at' => null],
            ['id' => 10, 'machine_type_id' => 9, 'system_id' => 2, 'created_at' => null, 'updated_at' => null],
            ['id' => 11, 'machine_type_id' => 9, 'system_id' => 5, 'created_at' => null, 'updated_at' => null],
            ['id' => 12, 'machine_type_id' => 9, 'system_id' => 6, 'created_at' => null, 'updated_at' => null],
            ['id' => 13, 'machine_type_id' => 9, 'system_id' => 7, 'created_at' => null, 'updated_at' => null],
            ['id' => 14, 'machine_type_id' => 7, 'system_id' => 1, 'created_at' => null, 'updated_at' => null],
            ['id' => 15, 'machine_type_id' => 7, 'system_id' => 2, 'created_at' => null, 'updated_at' => null],
            ['id' => 16, 'machine_type_id' => 7, 'system_id' => 7, 'created_at' => null, 'updated_at' => null],
            ['id' => 17, 'machine_type_id' => 7, 'system_id' => 8, 'created_at' => null, 'updated_at' => null],
        ];
        DB::table('machine_type_system')->insert($machineTypeSystem);
    }
}


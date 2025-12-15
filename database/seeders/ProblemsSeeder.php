<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProblemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $problems = [
            ['id' => 1, 'group' => null, 'problem_header' => 'PUTARAN MOTOR TIDAK STABIL', 'name' => 'Putaran Motor tidak stabil sehingga kinerja mesin tidak Optimal', 'problem_mm' => 'OTHER', 'created_at' => '2025-11-26 23:41:40', 'updated_at' => '2025-11-26 23:41:40'],
        ];
        DB::table('problems')->insert($problems);

        $problemSystem = [
            ['id' => 1, 'problem_id' => 1, 'system_id' => 1, 'created_at' => null, 'updated_at' => null],
        ];
        DB::table('problem_system')->insert($problemSystem);
    }
}

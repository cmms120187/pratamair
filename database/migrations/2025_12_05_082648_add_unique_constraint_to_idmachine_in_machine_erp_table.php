<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, handle duplicate idMachine entries (keep the first one, delete duplicates)
        $duplicates = DB::table('machine_erp')
            ->select('idMachine', DB::raw('COUNT(*) as count'), DB::raw('MIN(id) as min_id'))
            ->groupBy('idMachine')
            ->having('count', '>', 1)
            ->get();
        
        foreach ($duplicates as $duplicate) {
            // Delete all duplicates except the first one (lowest id)
            DB::table('machine_erp')
                ->where('idMachine', $duplicate->idMachine)
                ->where('id', '!=', $duplicate->min_id)
                ->delete();
        }
        
        // Add unique constraint to idMachine
        Schema::table('machine_erp', function (Blueprint $table) {
            $table->unique('idMachine');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machine_erp', function (Blueprint $table) {
            $table->dropUnique(['idMachine']);
        });
    }
};

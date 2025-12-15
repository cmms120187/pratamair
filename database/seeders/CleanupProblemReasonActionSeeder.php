<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Problem;
use App\Models\Reason;
use App\Models\Action;
use App\Models\System;

class CleanupProblemReasonActionSeeder extends Seeder
{
    /**
     * Clean up and normalize Problem, Reason, and Action data
     */
    public function run(): void
    {
        $this->command->info('Starting cleanup and normalization of Problem, Reason, and Action data...');

        // 1. Clean up Problems without proper headers
        $this->command->info('Cleaning up Problems...');
        $problems = Problem::whereNull('problem_header')->orWhere('problem_header', '')->get();
        foreach ($problems as $problem) {
            $header = $this->extractProblemHeader($problem->name);
            $problem->problem_header = $header;
            $problem->save();
        }
        $this->command->info('Updated ' . $problems->count() . ' problems with headers');

        // 2. Ensure all Reasons have System assigned
        $this->command->info('Ensuring all Reasons have Systems...');
        $reasonsWithoutSystem = Reason::whereNull('system_id')->get();
        foreach ($reasonsWithoutSystem as $reason) {
            // Try to get system from problem
            if ($reason->problem) {
                $system = $reason->problem->systems->first();
                if ($system) {
                    $reason->system_id = $system->id;
                    $reason->save();
                }
            }
            
            // If still no system, try to determine from reason name
            if (!$reason->system_id) {
                $systemName = $this->determineSystemFromName($reason->name);
                $system = System::where('nama_sistem', $systemName)->first();
                if ($system) {
                    $reason->system_id = $system->id;
                    $reason->save();
                }
            }
        }
        $this->command->info('Updated ' . $reasonsWithoutSystem->count() . ' reasons with systems');

        // 3. Ensure all Actions have System assigned
        $this->command->info('Ensuring all Actions have Systems...');
        $actionsWithoutSystem = Action::whereNull('system_id')->get();
        foreach ($actionsWithoutSystem as $action) {
            // Try to get system from problem or reason
            if ($action->problem) {
                $system = $action->problem->systems->first();
                if ($system) {
                    $action->system_id = $system->id;
                    $action->save();
                    continue;
                }
            }
            
            if ($action->reason && $action->reason->system) {
                $action->system_id = $action->reason->system_id;
                $action->save();
                continue;
            }
            
            // If still no system, try to determine from action name
            if (!$action->system_id) {
                $systemName = $this->determineSystemFromName($action->name);
                $system = System::where('nama_sistem', $systemName)->first();
                if ($system) {
                    $action->system_id = $system->id;
                    $action->save();
                }
            }
        }
        $this->command->info('Updated ' . $actionsWithoutSystem->count() . ' actions with systems');

        // 4. Normalize names (trim whitespace)
        $this->command->info('Normalizing names...');
        DB::statement('UPDATE problems SET name = TRIM(name)');
        DB::statement('UPDATE reasons SET name = TRIM(name)');
        DB::statement('UPDATE actions SET name = TRIM(name)');
        $this->command->info('Normalized all names');

        // 5. Remove orphaned Reasons (without Problem)
        $this->command->info('Removing orphaned Reasons...');
        $orphanedReasons = Reason::whereNull('problem_id')->get();
        $count = $orphanedReasons->count();
        foreach ($orphanedReasons as $reason) {
            // Check if used in any Action
            if ($reason->downtimes()->count() > 0 || $reason->downtimes()->count() > 0) {
                continue; // Don't delete if used
            }
            $reason->delete();
        }
        $this->command->info('Removed ' . $count . ' orphaned reasons');

        // 6. Remove orphaned Actions (without Problem or Reason)
        $this->command->info('Removing orphaned Actions...');
        $orphanedActions = Action::where(function($query) {
            $query->whereNull('problem_id')->orWhereNull('reason_id');
        })->get();
        $count = $orphanedActions->count();
        foreach ($orphanedActions as $action) {
            // Check if used in any Downtime
            if ($action->downtimes()->count() > 0) {
                continue; // Don't delete if used
            }
            $action->delete();
        }
        $this->command->info('Removed ' . $count . ' orphaned actions');

        $this->command->info("\n==========================================");
        $this->command->info("Cleanup completed!");
        $this->command->info("==========================================");
    }

    /**
     * Extract problem header from problem name
     */
    private function extractProblemHeader(string $problemName): string
    {
        $problemUpper = strtoupper($problemName);
        
        if (stripos($problemUpper, 'MOLD') !== false) {
            return 'MOLD PROBLEM';
        }
        if (stripos($problemUpper, 'TRIMING') !== false || stripos($problemUpper, 'TRIM') !== false) {
            return 'TRIMMING PROBLEM';
        }
        if (stripos($problemUpper, 'MESIN') !== false || stripos($problemUpper, 'MOTOR') !== false) {
            return 'MACHINE PROBLEM';
        }
        if (stripos($problemUpper, 'INSTRUMENT') !== false) {
            return 'INSTRUMENT PROBLEM';
        }
        if (stripos($problemUpper, 'TEKANAN') !== false || stripos($problemUpper, 'PRESSURE') !== false) {
            return 'PRESSURE PROBLEM';
        }
        if (stripos($problemUpper, 'GRINDING') !== false) {
            return 'GRINDING PROBLEM';
        }
        if (stripos($problemUpper, 'CONVEYOR') !== false) {
            return 'CONVEYOR PROBLEM';
        }
        if (stripos($problemUpper, 'PUTARAN') !== false || stripos($problemUpper, 'ROTATION') !== false) {
            return 'ROTATION PROBLEM';
        }
        if (stripos($problemUpper, 'TEMPERATURE') !== false || stripos($problemUpper, 'TEMPERATUR') !== false) {
            return 'TEMPERATURE PROBLEM';
        }
        
        return 'OTHER';
    }

    /**
     * Determine system from name
     */
    private function determineSystemFromName(string $name): string
    {
        $nameUpper = strtoupper($name);
        
        $mapping = [
            'MOLD' => 'Mechanical',
            'TRIMING' => 'Mechanical',
            'TRIM' => 'Mechanical',
            'PISAU' => 'Mechanical',
            'GRINDING' => 'Mechanical',
            'BEARING' => 'Mechanical',
            'HYDRAULIC' => 'Hydraulic',
            'TEKANAN' => 'Hydraulic',
            'PRESS' => 'Hydraulic',
            'CYLINDER' => 'Hydraulic',
            'SWITCH' => 'Electrical / Instrument',
            'INSTRUMENT' => 'Electrical / Instrument',
            'SENSOR' => 'Electrical / Instrument',
            'MOTOR' => 'Electrical / Instrument',
            'KABEL' => 'Electrical / Instrument',
            'CONTACTOR' => 'Electrical / Instrument',
            'RELAY' => 'Electrical / Instrument',
            'CONVEYOR' => 'Mechanical',
            'MIXING' => 'Mechanical',
        ];
        
        foreach ($mapping as $keyword => $systemName) {
            if (strpos($nameUpper, $keyword) !== false) {
                return $systemName;
            }
        }
        
        return 'Mechanical'; // Default
    }
}


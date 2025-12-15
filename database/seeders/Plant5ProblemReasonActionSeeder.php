<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Problem;
use App\Models\Reason;
use App\Models\Action;
use App\Models\System;

class Plant5ProblemReasonActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = base_path('downtime2024.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error('File not found: ' . $csvFile);
            return;
        }

        $handle = fopen($csvFile, 'r');
        if (!$handle) {
            $this->command->error('Cannot open file: ' . $csvFile);
            return;
        }

        // Read header
        $header = fgetcsv($handle, 0, ',');
        if (!$header) {
            $this->command->error('Cannot read header from CSV file');
            fclose($handle);
            return;
        }

        $this->command->info('Starting import of Problem, Reason, and Action data from PLANT 5 RUBBER PROCESS...');

        // Collect unique data
        $problemsData = [];
        $reasonsData = [];
        $actionsData = [];
        $problemReasonPairs = [];
        $problemReasonActionTriplets = [];
        $typeMachineSystemMap = [];

        // Map typeMachine to System (based on common patterns)
        $systemMapping = [
            'Trimming' => 'Mechanical',
            'Hydraulic Press Automatic' => 'Hydraulic',
            'TDL Press' => 'Hydraulic',
            'Hydraulic plane cutting press' => 'Hydraulic',
            'Mixing Mill' => 'Mechanical',
            'Semi Automatic Sile Edge Grinding Macine' => 'Mechanical',
            'Conveyor Dry (Paint) Chamber' => 'Mechanical',
        ];

        // Map problem keywords to System
        $problemSystemMapping = [
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

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            try {
                if (count($row) !== count($header)) {
                    continue;
                }

                $data = array_combine($header, $row);

                // Filter only PLANT 5 RUBBER PROCESS
                if (trim($data['building'] ?? '') !== 'PLANT 5 RUBBER PROCESS') {
                    continue;
                }

                $problem = trim($data['problemDowntime'] ?? '');
                $reason = trim($data['reasonDowntime'] ?? '');
                $action = trim($data['actionDowntime'] ?? '');
                $typeMachine = trim($data['typeMachine'] ?? '');
                $groupMachine = trim($data['groupMachine'] ?? '');

                if (empty($problem) || empty($reason) || empty($action)) {
                    continue;
                }

                // Determine system based on typeMachine and problem keywords
                $systemName = $systemMapping[$typeMachine] ?? null;
                
                // If not found in typeMachine mapping, try problem keywords
                if (!$systemName) {
                    $problemUpper = strtoupper($problem);
                    foreach ($problemSystemMapping as $keyword => $sysName) {
                        if (strpos($problemUpper, $keyword) !== false) {
                            $systemName = $sysName;
                            break;
                        }
                    }
                }
                
                // Default to Mechanical if still not found
                if (!$systemName) {
                    $systemName = 'Mechanical';
                }
                
                $system = System::where('nama_sistem', $systemName)->first();
                if (!$system) {
                    // Try to find closest match
                    $system = System::where('nama_sistem', 'like', '%Mechanical%')->first() 
                        ?? System::first();
                }
                $systemId = $system ? $system->id : null;

                // Collect Problems
                if (!isset($problemsData[$problem])) {
                    $problemsData[$problem] = [
                        'name' => $problem,
                        'group' => $groupMachine,
                        'problem_header' => $this->extractProblemHeader($problem),
                        'problem_mm' => null,
                        'system_id' => $systemId,
                    ];
                }

                // Collect Reasons with Problem relationship
                $reasonKey = $problem . '|' . $reason;
                if (!isset($reasonsData[$reasonKey])) {
                    $reasonsData[$reasonKey] = [
                        'name' => $reason,
                        'problem_name' => $problem,
                        'system_id' => $systemId,
                    ];
                }

                // Collect Actions with Problem and Reason relationship
                $actionKey = $problem . '|' . $reason . '|' . $action;
                if (!isset($actionsData[$actionKey])) {
                    $actionsData[$actionKey] = [
                        'name' => $action,
                        'problem_name' => $problem,
                        'reason_name' => $reason,
                        'system_id' => $systemId,
                    ];
                }

                // Track relationships
                $problemReasonPairs[$problem . '|' . $reason] = true;
                $problemReasonActionTriplets[$problem . '|' . $reason . '|' . $action] = true;

            } catch (\Exception $e) {
                // Continue on error
            }
        }

        fclose($handle);

        $this->command->info('Found ' . count($problemsData) . ' unique problems');
        $this->command->info('Found ' . count($reasonsData) . ' unique reason-problem pairs');
        $this->command->info('Found ' . count($actionsData) . ' unique action-reason-problem triplets');

        // Import Problems
        $this->command->info('Importing Problems...');
        $problemMap = [];
        foreach ($problemsData as $problemName => $problemData) {
            try {
                // Create or get Problem
                $problem = Problem::firstOrCreate(
                    ['name' => $problemData['name']],
                    [
                        'name' => $problemData['name'],
                        'group' => $problemData['group'],
                        'problem_header' => $problemData['problem_header'],
                        'problem_mm' => $problemData['problem_mm'],
                    ]
                );

                $problemMap[$problemName] = $problem->id;

                // Attach System to Problem
                if ($problemData['system_id']) {
                    $problem->systems()->syncWithoutDetaching([$problemData['system_id']]);
                }
            } catch (\Exception $e) {
                $this->command->warn('Error creating problem: ' . $problemName . ' - ' . $e->getMessage());
            }
        }

        $this->command->info('Imported ' . count($problemMap) . ' problems');

        // Import Reasons
        $this->command->info('Importing Reasons...');
        $reasonMap = [];
        foreach ($reasonsData as $reasonKey => $reasonData) {
            try {
                $problemId = $problemMap[$reasonData['problem_name']] ?? null;
                
                if (!$problemId) {
                    continue;
                }

                // Create or get Reason
                $reason = Reason::firstOrCreate(
                    [
                        'name' => $reasonData['name'],
                        'problem_id' => $problemId,
                    ],
                    [
                        'name' => $reasonData['name'],
                        'system_id' => $reasonData['system_id'],
                        'problem_id' => $problemId,
                    ]
                );

                $reasonMap[$reasonKey] = $reason->id;
            } catch (\Exception $e) {
                $this->command->warn('Error creating reason: ' . $reasonData['name'] . ' - ' . $e->getMessage());
            }
        }

        $this->command->info('Imported ' . count($reasonMap) . ' reasons');

        // Import Actions
        $this->command->info('Importing Actions...');
        $actionMap = [];
        foreach ($actionsData as $actionKey => $actionData) {
            try {
                $problemId = $problemMap[$actionData['problem_name']] ?? null;
                $reasonKey = $actionData['problem_name'] . '|' . $actionData['reason_name'];
                $reasonId = $reasonMap[$reasonKey] ?? null;
                
                if (!$problemId || !$reasonId) {
                    continue;
                }

                // Create or get Action
                $action = Action::firstOrCreate(
                    [
                        'name' => $actionData['name'],
                        'problem_id' => $problemId,
                        'reason_id' => $reasonId,
                    ],
                    [
                        'name' => $actionData['name'],
                        'system_id' => $actionData['system_id'],
                        'problem_id' => $problemId,
                        'reason_id' => $reasonId,
                    ]
                );

                $actionMap[$actionKey] = $action->id;
            } catch (\Exception $e) {
                $this->command->warn('Error creating action: ' . $actionData['name'] . ' - ' . $e->getMessage());
            }
        }

        $this->command->info('Imported ' . count($actionMap) . ' actions');

        $this->command->info("\n==========================================");
        $this->command->info("Import completed!");
        $this->command->info("Problems: " . count($problemMap));
        $this->command->info("Reasons: " . count($reasonMap));
        $this->command->info("Actions: " . count($actionMap));
        $this->command->info("==========================================");
    }

    /**
     * Extract problem header from problem name
     */
    private function extractProblemHeader(string $problemName): string
    {
        // Common patterns for problem headers
        if (stripos($problemName, 'MOLD') !== false) {
            return 'MOLD PROBLEM';
        }
        if (stripos($problemName, 'TRIMING') !== false || stripos($problemName, 'TRIM') !== false) {
            return 'TRIMMING PROBLEM';
        }
        if (stripos($problemName, 'MESIN') !== false || stripos($problemName, 'MOTOR') !== false) {
            return 'MACHINE PROBLEM';
        }
        if (stripos($problemName, 'INSTRUMENT') !== false) {
            return 'INSTRUMENT PROBLEM';
        }
        if (stripos($problemName, 'TEKANAN') !== false) {
            return 'PRESSURE PROBLEM';
        }
        if (stripos($problemName, 'GRINDING') !== false) {
            return 'GRINDING PROBLEM';
        }
        if (stripos($problemName, 'CONVEYOR') !== false) {
            return 'CONVEYOR PROBLEM';
        }
        
        return 'OTHER';
    }
}


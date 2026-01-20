<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\System;
use App\Models\Problem;
use App\Models\Reason;
use App\Models\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterProblemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Try multiple possible paths
        $possiblePaths = [
            base_path('MASTER PROBLEM.csv'),
            database_path('../MASTER PROBLEM.csv'),
            __DIR__ . '/../../MASTER PROBLEM.csv',
        ];
        
        $csvFile = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $csvFile = $path;
                break;
            }
        }
        
        if (!$csvFile) {
            $this->command->error("File MASTER PROBLEM.csv not found. Tried paths:");
            foreach ($possiblePaths as $path) {
                $this->command->error("  - {$path}");
            }
            return;
        }

        $this->command->info("Reading CSV file: {$csvFile}");
        
        $handle = fopen($csvFile, 'r');
        if (!$handle) {
            $this->command->error("Cannot open CSV file");
            return;
        }

        // Skip header row
        $header = fgetcsv($handle, 0, ';');
        $this->command->info("Header: " . implode(', ', $header));

        $stats = [
            'problems' => 0,
            'reasons' => 0,
            'actions' => 0,
            'problem_systems' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        $lineNumber = 1;
        $batchSize = 1000;
        $processed = 0;

        // Cache untuk System
        $systemCache = [];
        
        // Cache untuk Problem
        $problemCache = [];

        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            $lineNumber++;
            
            // Skip empty rows
            if (empty(array_filter($data))) {
                $stats['skipped']++;
                continue;
            }

            // Validate data structure
            if (count($data) < 5) {
                $stats['skipped']++;
                continue;
            }

            try {
                $problemName = trim($data[0] ?? '');
                $problemMm = trim($data[1] ?? '');
                $reasonName = trim($data[2] ?? '');
                $actionName = trim($data[3] ?? '');
                $systemName = trim($data[4] ?? '');

                // Skip if essential data is missing
                if (empty($problemName) || empty($reasonName) || empty($actionName) || empty($systemName)) {
                    $stats['skipped']++;
                    continue;
                }

                // Map system name from CSV to existing system in database
                $mappedSystemName = $this->mapSystemName($systemName);
                
                // Get or create System (use mapped name)
                if (!isset($systemCache[$mappedSystemName])) {
                    $system = System::firstOrCreate(
                        ['nama_sistem' => $mappedSystemName],
                        ['deskripsi' => "System: {$mappedSystemName}"]
                    );
                    $systemCache[$mappedSystemName] = $system;
                    
                    // Log mapping if different
                    if ($mappedSystemName !== $systemName) {
                        $this->command->info("Mapped system '{$systemName}' -> '{$mappedSystemName}'");
                    }
                } else {
                    $system = $systemCache[$mappedSystemName];
                }

                // Get or create Problem (name must be unique)
                $problemKey = $problemName;
                if (!isset($problemCache[$problemKey])) {
                    $problem = Problem::firstOrCreate(
                        ['name' => $problemName],
                        [
                            'problem_mm' => $problemMm ?: null,
                            'problem_header' => $this->extractProblemHeader($problemName),
                            'group' => null,
                        ]
                    );
                    $problemCache[$problemKey] = $problem;
                    $stats['problems']++;
                } else {
                    $problem = $problemCache[$problemKey];
                }

                // Attach System to Problem (many-to-many)
                if (!$problem->systems()->where('system_id', $system->id)->exists()) {
                    $problem->systems()->attach($system->id);
                    $stats['problem_systems']++;
                }

                // Get or create Reason (can have same name with different system_id/problem_id)
                $reason = Reason::firstOrCreate(
                    [
                        'name' => $reasonName,
                        'system_id' => $system->id,
                        'problem_id' => $problem->id,
                    ]
                );
                
                if ($reason->wasRecentlyCreated) {
                    $stats['reasons']++;
                }

                // Get or create Action (can have same name with different system_id/problem_id/reason_id)
                $action = Action::firstOrCreate(
                    [
                        'name' => $actionName,
                        'system_id' => $system->id,
                        'problem_id' => $problem->id,
                        'reason_id' => $reason->id,
                    ]
                );
                
                if ($action->wasRecentlyCreated) {
                    $stats['actions']++;
                }

                $processed++;

                // Progress indicator
                if ($processed % $batchSize === 0) {
                    $this->command->info("Processed {$processed} rows...");
                }

            } catch (\Exception $e) {
                $stats['errors']++;
                Log::error("Error processing line {$lineNumber}: " . $e->getMessage());
                if ($stats['errors'] <= 10) {
                    $this->command->warn("Error on line {$lineNumber}: " . $e->getMessage());
                }
            }
        }

        fclose($handle);

        // Display summary
        $this->command->info("\n=== Seeding Summary ===");
        $this->command->info("Total rows processed: {$processed}");
        $this->command->info("Problems created: {$stats['problems']}");
        $this->command->info("Reasons created: {$stats['reasons']}");
        $this->command->info("Actions created: {$stats['actions']}");
        $this->command->info("Problem-System links: {$stats['problem_systems']}");
        $this->command->info("Rows skipped: {$stats['skipped']}");
        $this->command->info("Errors: {$stats['errors']}");
        $this->command->info("\nSeeding completed!");
    }

    /**
     * Map system name from CSV to existing system in database
     */
    private function mapSystemName($systemName)
    {
        $systemNameUpper = strtoupper(trim($systemName));
        $systemNameOriginal = trim($systemName);
        
        // Mapping dari CSV system ke database system
        // Hanya map sistem yang perlu diubah, sisanya tetap sesuai CSV
        $mapping = [
            'ELECTRICAL' => 'Electrical / Instrument',
            'ELECTRIC' => 'Electrical / Instrument',
            'ELECTRIC / INSTRUMENT' => 'Electrical / Instrument',
            'PNEUMATIC' => 'Pneumatic',
            'HYDRAULIC' => 'Hydraulic',
            'MECHANICAL' => 'Mechanical',
            'HEATING' => 'Heating',
            'COOLING' => 'Cooling',
            'ELECTRONIC' => 'Electronic',
            'LUBRICATION' => 'Lubrication',
            'SAFETY' => 'Safety',
            'OTHER' => 'Mechanical', // Default untuk OTHER
            // TRIMMER, MELTING, STITCHING, FACILITIES, ALLIGNMENT SYSTEM tetap sesuai CSV
        ];
        
        // Check exact match first
        if (isset($mapping[$systemNameUpper])) {
            return $mapping[$systemNameUpper];
        }
        
        // Check partial match (case-insensitive)
        foreach ($mapping as $csvName => $dbName) {
            if (strpos($systemNameUpper, $csvName) !== false || strpos($csvName, $systemNameUpper) !== false) {
                return $dbName;
            }
        }
        
        // Check for "ELECTRICAL" in the name (case-insensitive)
        if (stripos($systemNameOriginal, 'ELECTRICAL') !== false || stripos($systemNameOriginal, 'ELECTRIC') !== false) {
            return 'Electrical / Instrument';
        }
        
        // If not found, try to find in database by similar name (case-insensitive)
        $existingSystem = System::whereRaw('UPPER(nama_sistem) = ?', [$systemNameUpper])->first();
        if ($existingSystem) {
            return $existingSystem->nama_sistem;
        }
        
        // Default: return original name (will create new system if not exists)
        return $systemNameOriginal;
    }

    /**
     * Extract problem header from problem name
     * For example: "BEAM MACET (TIDAK NAIK/TURUN)" -> "BEAM MACET"
     */
    private function extractProblemHeader($problemName)
    {
        // Remove content in parentheses
        $header = preg_replace('/\s*\([^)]*\)\s*/', '', $problemName);
        
        // Take first few words if too long
        $words = explode(' ', $header);
        if (count($words) > 5) {
            $header = implode(' ', array_slice($words, 0, 5));
        }
        
        return trim($header) ?: $problemName;
    }
}

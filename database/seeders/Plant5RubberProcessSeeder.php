<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Plant;
use App\Models\Process;
use App\Models\Line;
use App\Models\RoomErp;
use App\Models\Brand;
use App\Models\MachineType;
use App\Models\Model;
use App\Models\MachineErp;
use App\Models\PartErp;
use App\Models\DowntimeErp2;
use Carbon\Carbon;

class Plant5RubberProcessSeeder extends Seeder
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

        $rowCount = 0;
        $errorCount = 0;
        $plant5Count = 0;

        // Track unique values for master data
        $plants = [];
        $processes = [];
        $lines = [];
        $rooms = [];
        $brands = [];
        $machineTypes = [];
        $models = [];
        $machines = [];
        $parts = [];

        $this->command->info('Starting import of PLANT 5 RUBBER PROCESS data...');

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            try {
                if (count($row) !== count($header)) {
                    $errorCount++;
                    continue;
                }

                $data = array_combine($header, $row);

                // Filter only PLANT 5 RUBBER PROCESS
                if (trim($data['building'] ?? '') !== 'PLANT 5 RUBBER PROCESS') {
                    continue;
                }

                $plant5Count++;

                // Normalize date format: 2024/01/02 -> 2024-01-02
                $date = str_replace('/', '-', trim($data['date'] ?? ''));
                if (empty($date)) {
                    $errorCount++;
                    continue;
                }

                // Parse date
                try {
                    $dateObj = Carbon::createFromFormat('Y-m-d', $date);
                } catch (\Exception $e) {
                    $errorCount++;
                    continue;
                }

                // Location data
                $plantName = trim($data['building'] ?? '');
                $processName = trim($data['proses'] ?? '');
                $lineName = trim($data['line'] ?? '');
                $roomName = trim($data['room'] ?? '');

                // Machine data
                $idMachine = trim($data['idMachine'] ?? '');
                $groupMachine = trim($data['groupMachine'] ?? '');
                $typeMachine = trim($data['typeMachine'] ?? '');
                $brandMachine = trim($data['brandMachine'] ?? '');

                // Skip if essential data is missing
                if (empty($plantName) || empty($processName) || empty($lineName) || empty($roomName) || empty($idMachine)) {
                    $errorCount++;
                    continue;
                }

                // Create or get Plant
                if (!isset($plants[$plantName])) {
                    $plant = Plant::firstOrCreate(['name' => $plantName]);
                    $plants[$plantName] = $plant->id;
                }

                // Create or get Process
                if (!isset($processes[$processName])) {
                    $process = Process::firstOrCreate(['name' => $processName]);
                    $processes[$processName] = $process->id;
                }

                // Create or get Line
                if (!isset($lines[$lineName])) {
                    $line = Line::firstOrCreate(['name' => $lineName]);
                    $lines[$lineName] = $line->id;
                }

                // Create or get Room ERP (generate kode_room if not exists)
                $kodeRoom = null;
                if (!isset($rooms[$roomName])) {
                    // Try to find existing room by name
                    $roomErp = RoomErp::where('name', $roomName)->first();
                    if (!$roomErp) {
                        // Generate kode_room (you may need to adjust this logic)
                        $kodeRoom = '*PLANT5-' . strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', $roomName), 0, 6)) . '*';
                        $roomErp = RoomErp::create([
                            'kode_room' => $kodeRoom,
                            'name' => $roomName,
                            'category' => 'Utility',
                            'plant_name' => $plantName,
                            'line_name' => $lineName,
                            'process_name' => $processName,
                        ]);
                    }
                    $rooms[$roomName] = $roomErp->kode_room;
                    $kodeRoom = $roomErp->kode_room;
                } else {
                    $kodeRoom = $rooms[$roomName];
                }

                // Create or get Brand
                if (!empty($brandMachine) && !isset($brands[$brandMachine])) {
                    $brand = Brand::firstOrCreate(['name' => $brandMachine]);
                    $brands[$brandMachine] = $brand->id;
                }

                // Create or get Machine Type
                if (!empty($typeMachine) && !isset($machineTypes[$typeMachine])) {
                    $machineType = MachineType::firstOrCreate(['name' => $typeMachine]);
                    $machineTypes[$typeMachine] = $machineType->id;
                }

                // Create or get Model (CSV doesn't have modelMachine column, so we'll use typeMachine as model name)
                // In CSV, typeMachine column contains the model information
                $modelName = $typeMachine; // Use typeMachine as model name since CSV doesn't have separate model column
                if (!empty($modelName) && !empty($brandMachine) && !isset($models[$modelName])) {
                    $brandId = $brands[$brandMachine] ?? null;
                    $machineTypeId = $machineTypes[$typeMachine] ?? null;
                    
                    if ($brandId && $machineTypeId) {
                        $model = \App\Models\Model::firstOrCreate(
                            ['name' => $modelName, 'brand_id' => $brandId, 'type_id' => $machineTypeId],
                            ['name' => $modelName, 'brand_id' => $brandId, 'type_id' => $machineTypeId]
                        );
                        $models[$modelName] = $model->id;
                    }
                }

                // Create or get Machine ERP
                $machineKey = $idMachine . '|' . $plantName;
                if (!isset($machines[$machineKey])) {
                    $machineErp = MachineErp::firstOrCreate(
                        ['idMachine' => $idMachine],
                        [
                            'idMachine' => $idMachine,
                            'kode_room' => $kodeRoom,
                            'plant_name' => $plantName,
                            'process_name' => $processName,
                            'line_name' => $lineName,
                            'room_name' => $roomName,
                            'type_name' => $typeMachine,
                            'brand_name' => $brandMachine,
                            'model_name' => $modelName,
                            'machine_type_id' => $machineTypes[$typeMachine] ?? null,
                        ]
                    );
                    $machines[$machineKey] = $machineErp->id;
                }

                // Extract parts from actionDowntime and reasonDowntime
                // Note: We don't create Part ERP records because CSV doesn't have part_number
                // We only extract part name to store in DowntimeErp2.Part field
                $actionDowntime = trim($data['actionDowntime'] ?? '');
                $reasonDowntime = trim($data['reasonDowntime'] ?? '');
                $partName = null;

                // Try to extract part name from action or reason for reference only
                if (!empty($actionDowntime)) {
                    // Common part patterns: GANTI [PART], SERVICE [PART], etc.
                    if (preg_match('/GANTI\s+([A-Z0-9\s\-]+)/i', $actionDowntime, $matches)) {
                        $partName = trim($matches[1]);
                        // Limit part name length to avoid too long names
                        if (strlen($partName) > 100) {
                            $partName = substr($partName, 0, 100);
                        }
                    } elseif (preg_match('/SERVICE\s+([A-Z0-9\s\-]+)/i', $actionDowntime, $matches)) {
                        $partName = trim($matches[1]);
                        if (strlen($partName) > 100) {
                            $partName = substr($partName, 0, 100);
                        }
                    }
                }

                // Create DowntimeErp2 record
                DowntimeErp2::create([
                    'date' => $dateObj->format('Y-m-d'),
                    'kode_room' => $kodeRoom,
                    'plant' => $plantName,
                    'process' => $processName,
                    'line' => $lineName,
                    'roomName' => $roomName,
                    'include_oee' => true,
                    'idMachine' => $idMachine,
                    'typeMachine' => $typeMachine,
                    'modelMachine' => $modelName,
                    'brandMachine' => $brandMachine,
                    'stopProduction' => trim($data['stopProduction'] ?? ''),
                    'responMechanic' => trim($data['startRepair'] ?? ''),
                    'startProduction' => trim($data['startProduction'] ?? ''),
                    'duration' => trim($data['duration'] ?? ''),
                    'Standar_Time' => null,
                    'problemDowntime' => trim($data['problemDowntime'] ?? ''),
                    'Problem_MM' => null,
                    'reasonDowntime' => $reasonDowntime,
                    'actionDowtime' => $actionDowntime,
                    'Part' => $partName,
                    'idMekanik' => trim($data['idMekanik'] ?? ''),
                    'nameMekanik' => trim($data['namaMekanik'] ?? ''),
                    'idLeader' => trim($data['idLeader'] ?? ''),
                    'nameLeader' => trim($data['namaLeader'] ?? ''),
                    'idGL' => null,
                    'nameGL' => null,
                    'idCoord' => trim($data['idCoordinator'] ?? ''),
                    'nameCoord' => trim($data['namaCoordinator'] ?? ''),
                    'groupProblem' => $groupMachine,
                ]);

                $rowCount++;

                if ($rowCount % 100 == 0) {
                    $this->command->info("Processed $rowCount rows...");
                }

            } catch (\Exception $e) {
                $errorCount++;
                $this->command->warn('Error processing row: ' . $e->getMessage());
            }
        }

        fclose($handle);

        $this->command->info("\n==========================================");
        $this->command->info("Import completed!");
        $this->command->info("Total PLANT 5 RUBBER PROCESS rows found: $plant5Count");
        $this->command->info("Successfully imported: $rowCount rows");
        $this->command->info("Errors: $errorCount rows");
        $this->command->info("==========================================");
        $this->command->info("Created/Updated:");
        $this->command->info("- Plants: " . count($plants));
        $this->command->info("- Processes: " . count($processes));
        $this->command->info("- Lines: " . count($lines));
        $this->command->info("- Rooms: " . count($rooms));
        $this->command->info("- Brands: " . count($brands));
        $this->command->info("- Machine Types: " . count($machineTypes));
        $this->command->info("- Models: " . count($models));
        $this->command->info("- Machines: " . count($machines));
        $this->command->info("- Parts extracted (stored in DowntimeErp2.Part field): " . count($parts));
    }
}


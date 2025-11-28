<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Plant;
use App\Models\Process;
use App\Models\Line;
use App\Models\Room;
use App\Models\Brand;
use App\Models\MachineType;
use App\Models\Model;
use App\Models\Machine;

class ImportDowntimeCsv extends Command
{
    protected $signature = 'import:downtime-csv {file=downtime2024revisi.csv}';
    protected $description = 'Import downtime data from CSV to database';

    public function handle()
    {
        $file = base_path($this->argument('file'));
        if (!file_exists($file)) {
            $this->error('File not found: ' . $file);
            return 1;
        }
        $handle = fopen($file, 'r');
        $header = fgetcsv($handle, 0, "\t");
        $rowCount = 0;
        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            $data = array_combine($header, $row);
            // Plants
            $plant = Plant::firstOrCreate(['name' => $data['plant']]);
            // Processes
            $process = Process::firstOrCreate(['name' => $data['process']]);
            // Lines
            $line = Line::firstOrCreate(['name' => $data['line']]);
            // Rooms
            $room = Room::firstOrCreate(['name' => $data['roomName']]);
            // Brands
            $brand = Brand::firstOrCreate(['name' => $data['brandMachine']]);
            // Machine Types
            $type = MachineType::firstOrCreate(['name' => $data['typeMachine']]);
            // Models
            $model = Model::firstOrCreate(['name' => $data['modelMachine']]);
            // Machines
            $machine = Machine::firstOrCreate([
                'idMachine' => $data['idMachine'],
                'plant_id' => $plant->id,
                'process_id' => $process->id,
                'line_id' => $line->id,
                'room_id' => $room->id,
                'type_id' => $type->id,
                'brand_id' => $brand->id,
                'model_id' => $model->id,
            ]);
            $rowCount++;
        }
        fclose($handle);
        $this->info("Imported $rowCount rows to master tables.");
        return 0;
    }
}

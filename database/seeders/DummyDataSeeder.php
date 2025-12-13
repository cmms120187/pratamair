<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DowntimeErp2;
use App\Models\ProductionDailyGrade;
use App\Models\ProductionHourly;
use App\Models\ProductionDailyDowntime;
use App\Models\RoomErp;
use App\Models\MachineErp;
use App\Models\Line;
use App\Models\Process;
use App\Models\WorkOrder;
use App\Models\Machine;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding dummy data...');
        
        // Get existing data
        $productionRooms = RoomErp::where('category', 'Production')
            ->whereNotNull('plant_name')
            ->whereNotNull('line_name')
            ->whereNotNull('process_name')
            ->get();
        
        if ($productionRooms->isEmpty()) {
            $this->command->warn('No production rooms found. Please run BasicDataSeeder first.');
            return;
        }
        
        $machines = MachineErp::whereNotNull('plant_name')
            ->whereNotNull('line_name')
            ->whereNotNull('process_name')
            ->get();
        
        if ($machines->isEmpty()) {
            $this->command->warn('No machines found. Please run MachineErpSeeder first.');
            return;
        }
        
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UsersSeeder first.');
            return;
        }
        
        // Get lines and processes
        $lines = Line::with('plant')->get();
        $processes = Process::all();
        
        // Seed DowntimeErp2
        $this->command->info('Seeding DowntimeErp2...');
        $this->seedDowntimeErp2($machines, $users);
        
        // Seed Production Data
        $this->command->info('Seeding Production Data...');
        $this->seedProductionData($lines, $processes, $productionRooms);
        
        // Seed Work Orders
        $this->command->info('Seeding Work Orders...');
        $this->seedWorkOrders($machines, $users);
        
        $this->command->info('Dummy data seeding completed!');
    }
    
    private function seedDowntimeErp2($machines, $users)
    {
        $problemTypes = [
            'Breakdown Mesin',
            'Masalah Listrik',
            'Masalah Mekanik',
            'Masalah Pneumatik',
            'Masalah Hidrolik',
            'Masalah Sensor',
            'Masalah Program',
        ];
        
        $reasons = [
            'Wear & Tear',
            'Overload',
            'Lack of Maintenance',
            'Human Error',
            'Material Issue',
            'Power Failure',
        ];
        
        $actions = [
            'Ganti Sparepart',
            'Perbaikan',
            'Cleaning',
            'Adjustment',
            'Reset Program',
            'Lubrication',
        ];
        
        $groupProblems = [
            'Mechanical',
            'Electrical',
            'Pneumatic',
            'Hydraulic',
            'Software',
        ];
        
        // Generate downtime for last 3 months
        $startDate = Carbon::now()->subMonths(3)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        $downtimeCount = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            // Generate 2-5 downtimes per day
            $downtimesPerDay = rand(2, 5);
            
            for ($i = 0; $i < $downtimesPerDay; $i++) {
                // Random machine
                $machine = $machines->random();
                
                // Random time during the day
                $stopHour = rand(6, 20);
                $stopMinute = rand(0, 59);
                $stopTime = Carbon::parse($currentDate)->setTime($stopHour, $stopMinute);
                
                // Duration: 15-180 minutes
                $durationMinutes = rand(15, 180);
                $responseMinutes = rand(5, 30);
                $startTime = $stopTime->copy()->addMinutes($durationMinutes);
                $responseTime = $stopTime->copy()->addMinutes($responseMinutes);
                
                // Random user
                $mechanic = $users->random();
                $leader = $users->random();
                $coord = $users->random();
                
                // Include OEE: 70% chance Yes, 30% No
                $includeOee = rand(1, 100) <= 70;
                
                DowntimeErp2::create([
                    'date' => $currentDate->format('Y-m-d'),
                    'kode_room' => $machine->kode_room,
                    'plant' => $machine->plant_name,
                    'process' => $machine->process_name,
                    'line' => $machine->line_name,
                    'roomName' => $machine->room_name,
                    'include_oee' => $includeOee,
                    'idMachine' => $machine->idMachine,
                    'typeMachine' => $machine->type_name ?? 'Unknown',
                    'modelMachine' => $machine->model_name ?? 'Unknown',
                    'brandMachine' => $machine->brand_name ?? 'Unknown',
                    'stopProduction' => $stopTime->format('H:i:s'),
                    'responMechanic' => $responseTime->format('H:i:s'),
                    'startProduction' => $startTime->format('H:i:s'),
                    'duration' => $durationMinutes . ' minutes',
                    'Standar_Time' => rand(10, 60) . ' minutes',
                    'problemDowntime' => $problemTypes[array_rand($problemTypes)],
                    'Problem_MM' => rand(0, 1) ? 'Problem MM ' . rand(1, 10) : null,
                    'reasonDowntime' => $reasons[array_rand($reasons)],
                    'actionDowtime' => $actions[array_rand($actions)],
                    'Part' => rand(0, 1) ? 'Part-' . strtoupper(substr(md5(rand()), 0, 6)) : null,
                    'idMekanik' => $mechanic->id ?? 'MECH001',
                    'nameMekanik' => $mechanic->name ?? 'Mechanic Name',
                    'idLeader' => $leader->id ?? 'LEAD001',
                    'nameLeader' => $leader->name ?? 'Leader Name',
                    'idGL' => rand(0, 1) ? ($users->random()->id ?? 'GL001') : null,
                    'nameGL' => rand(0, 1) ? ($users->random()->name ?? 'GL Name') : null,
                    'idCoord' => $coord->id ?? 'COORD001',
                    'nameCoord' => $coord->name ?? 'Coord Name',
                    'groupProblem' => $groupProblems[array_rand($groupProblems)],
                ]);
                
                $downtimeCount++;
            }
            
            $currentDate->addDay();
        }
        
        $this->command->info("Created {$downtimeCount} downtime records");
    }
    
    private function seedProductionData($lines, $processes, $productionRooms)
    {
        // Generate production data for last 2 months
        $startDate = Carbon::now()->subMonths(2)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        $productionCount = 0;
        $currentDate = $startDate->copy();
        
        // Group rooms by plant, process, line
        $roomGroups = $productionRooms->groupBy(function($room) {
            return $room->plant_name . '|' . $room->process_name . '|' . $room->line_name;
        });
        
        while ($currentDate <= $endDate) {
            // Skip weekends (optional - uncomment if needed)
            // if ($currentDate->isWeekend()) {
            //     $currentDate->addDay();
            //     continue;
            // }
            
            foreach ($roomGroups as $key => $rooms) {
                $room = $rooms->first();
                
                // Find matching line and process
                $line = $lines->firstWhere('name', $room->line_name);
                $process = $processes->firstWhere('name', $room->process_name);
                
                if (!$line || !$process) {
                    continue;
                }
                
                // Random production hours (6-12 hours)
                $startHour = rand(6, 8);
                $startMinute = rand(0, 30);
                $endHour = rand(17, 20);
                $endMinute = rand(0, 59);
                
                $startTime = sprintf('%02d:%02d', $startHour, $startMinute);
                $endTime = sprintf('%02d:%02d', $endHour, $endMinute);
                
                // Break duration: 1.0 for Mon-Thu, 1.5 for Fri
                $dayOfWeek = $currentDate->dayOfWeek;
                $breakDuration = ($dayOfWeek == 5) ? 1.5 : 1.0;
                
                // Target per hour: 100-500
                $targetPerHour = rand(100, 500);
                
                // Calculate production hours
                $startMinutes = $startHour * 60 + $startMinute;
                $endMinutes = $endHour * 60 + $endMinute;
                if ($endMinutes < $startMinutes) {
                    $endMinutes += 24 * 60;
                }
                $totalMinutes = $endMinutes - $startMinutes;
                $productionHours = ($totalMinutes / 60) - $breakDuration;
                
                // Grade A: 80-95% of target
                $targetOutput = round($targetPerHour * $productionHours);
                $gradeA = round($targetOutput * (rand(8000, 9500) / 10000));
                
                // Grade B: 2-8% of Grade A
                $gradeB = round($gradeA * (rand(200, 800) / 10000));
                
                // Grade C: 1-5% of Grade A
                $gradeC = round($gradeA * (rand(100, 500) / 10000));
                
                // Create or update ProductionDailyGrade
                $productionDaily = ProductionDailyGrade::updateOrCreate(
                    [
                        'line_id' => $line->id,
                        'process_id' => $process->id,
                        'production_date' => $currentDate->format('Y-m-d'),
                    ],
                    [
                        'target_per_hour' => $targetPerHour,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'break_duration' => $breakDuration,
                        'grade_b' => $gradeB,
                        'grade_c' => $gradeC,
                    ]
                );
                
                // Create or update ProductionHourly for hour 0 (summary)
                ProductionHourly::updateOrCreate(
                    [
                        'line_id' => $line->id,
                        'process_id' => $process->id,
                        'production_date' => $currentDate->format('Y-m-d'),
                        'hour' => 0,
                    ],
                    [
                        'target_per_hour' => $targetPerHour,
                        'total_production' => (string)$gradeA,
                        'notes' => 'Daily summary',
                    ]
                );
                
                // Create or update ProductionHourly for each hour (1-12 hours)
                $hoursToCreate = min(12, (int)$productionHours);
                for ($hour = 1; $hour <= $hoursToCreate; $hour++) {
                    // Hourly production: 70-110% of target
                    $hourlyProduction = round($targetPerHour * (rand(7000, 11000) / 10000));
                    
                    ProductionHourly::updateOrCreate(
                        [
                            'line_id' => $line->id,
                            'process_id' => $process->id,
                            'production_date' => $currentDate->format('Y-m-d'),
                            'hour' => $hour,
                        ],
                        [
                            'target_per_hour' => $targetPerHour,
                            'total_production' => (string)$hourlyProduction,
                            'notes' => null,
                        ]
                    );
                }
                
                // Create ProductionDailyDowntime (30% chance)
                if (rand(1, 100) <= 30) {
                    $downtimeTypes = ProductionDailyDowntime::getDowntimeTypes();
                    $downtimeTypeKeys = array_keys($downtimeTypes);
                    $downtimeType = $downtimeTypeKeys[array_rand($downtimeTypeKeys)];
                    
                    // Random downtime during production hours
                    $downtimeStartHour = rand($startHour, $endHour - 1);
                    $downtimeStartMinute = rand(0, 59);
                    $downtimeDuration = rand(15, 120); // 15-120 minutes
                    
                    $downtimeStart = sprintf('%02d:%02d', $downtimeStartHour, $downtimeStartMinute);
                    $downtimeEndMinutes = ($downtimeStartHour * 60 + $downtimeStartMinute + $downtimeDuration);
                    $downtimeEndHour = (int)($downtimeEndMinutes / 60);
                    $downtimeEndMinute = $downtimeEndMinutes % 60;
                    $downtimeEnd = sprintf('%02d:%02d', $downtimeEndHour, $downtimeEndMinute);
                    
                    ProductionDailyDowntime::create([
                        'production_daily_grade_id' => $productionDaily->id,
                        'downtime_type' => $downtimeType,
                        'start_time' => $downtimeStart,
                        'end_time' => $downtimeEnd,
                        'duration_minutes' => $downtimeDuration,
                        'description' => 'Downtime: ' . $downtimeTypes[$downtimeType],
                        'include_oee' => rand(1, 100) <= 80, // 80% include in OEE
                    ]);
                }
                
                $productionCount++;
            }
            
            $currentDate->addDay();
        }
        
        $this->command->info("Created {$productionCount} production daily records");
    }
    
    private function seedWorkOrders($machines, $users)
    {
        $statuses = ['pending', 'in_progress', 'waiting_parts', 'order_parts', 'completed', 'cancelled'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        
        $descriptions = [
            'Perbaikan mesin breakdown',
            'Maintenance rutin',
            'Ganti sparepart',
            'Cleaning dan inspection',
            'Calibration',
            'Troubleshooting',
            'Upgrade sistem',
        ];
        
        // Generate work orders for last 2 months
        $startDate = Carbon::now()->subMonths(2);
        $endDate = Carbon::now();
        
        $woCount = 0;
        for ($i = 0; $i < 50; $i++) {
            $orderDate = Carbon::instance($startDate)->addDays(rand(0, $startDate->diffInDays($endDate)));
            $dueDate = $orderDate->copy()->addDays(rand(1, 7));
            
            $status = $statuses[array_rand($statuses)];
            $priority = $priorities[array_rand($priorities)];
            
            $machine = $machines->random();
            
            // Find machine by idMachine or use first machine
            $machineModel = Machine::where('name', 'like', '%' . $machine->idMachine . '%')->first();
            
            $assignedTo = $users->random();
            $createdBy = $users->random();
            
            $startedAt = null;
            $completedAt = null;
            
            if (in_array($status, ['in_progress', 'completed'])) {
                $startedAt = $orderDate->copy()->addHours(rand(1, 24));
            }
            
            if ($status === 'completed') {
                $completedAt = $startedAt ? $startedAt->copy()->addHours(rand(2, 48)) : $orderDate->copy()->addDays(rand(1, 3));
            }
            
            $estimatedDuration = rand(60, 480); // 1-8 hours
            $actualDuration = $completedAt ? rand($estimatedDuration - 30, $estimatedDuration + 60) : null;
            
            WorkOrder::create([
                'wo_number' => 'WO-' . strtoupper(substr(md5(rand() . time()), 0, 8)),
                'order_date' => $orderDate->format('Y-m-d'),
                'status' => $status,
                'priority' => $priority,
                'machine_id' => $machineModel ? $machineModel->id : null,
                'description' => $descriptions[array_rand($descriptions)],
                'problem_description' => 'Problem: ' . $descriptions[array_rand($descriptions)],
                'assigned_to' => $assignedTo->id,
                'created_by' => $createdBy->id,
                'due_date' => $dueDate->format('Y-m-d'),
                'started_at' => $startedAt ? $startedAt->format('Y-m-d H:i:s') : null,
                'completed_at' => $completedAt ? $completedAt->format('Y-m-d H:i:s') : null,
                'notes' => rand(0, 1) ? 'Notes: ' . substr(md5(rand()), 0, 20) : null,
                'solution' => $status === 'completed' ? 'Solution: ' . substr(md5(rand()), 0, 30) : null,
                'estimated_cost' => rand(100000, 5000000),
                'actual_cost' => $completedAt ? rand(100000, 5000000) : null,
                'estimated_duration_minutes' => $estimatedDuration,
                'actual_duration_minutes' => $actualDuration,
                'photo_before' => null,
                'photo_after' => null,
            ]);
            
            $woCount++;
        }
        
        $this->command->info("Created {$woCount} work orders");
    }
}


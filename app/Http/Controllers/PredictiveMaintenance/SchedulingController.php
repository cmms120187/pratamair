<?php

namespace App\Http\Controllers\PredictiveMaintenance;

use App\Http\Controllers\Controller;
use App\Models\PredictiveMaintenanceSchedule;
use App\Models\PredictiveMaintenanceExecution;
use App\Models\Machine;
use App\Models\MaintenancePoint;
use App\Models\Standard;
use App\Models\User;
use App\Models\MachineType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SchedulingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get filter parameters
        $periodType = request()->get('period_type', 'year'); // 'month' or 'year'
        $periodMonth = request()->get('period_month', now()->month);
        $periodYear = request()->get('period_year', now()->year);
        $plantId = request()->get('plant');
        $lineId = request()->get('line');
        $machineTypeId = request()->get('machine_type');
        $searchIdMachine = request()->get('search_id_machine');
        
        // Build query
        $query = PredictiveMaintenanceSchedule::with(['machine.plant', 'machine.line', 'machine.machineType', 'maintenancePoint', 'standard', 'assignedUser', 'executions']);
        
        // Apply period filter
        if ($periodType == 'month') {
            $query->whereYear('start_date', $periodYear)
                  ->whereMonth('start_date', $periodMonth);
        } else {
            $query->whereYear('start_date', $periodYear);
        }
        
        // Apply plant filter
        if ($plantId) {
            $query->whereHas('machine', function($q) use ($plantId) {
                $q->where('plant_id', $plantId);
            });
        }
        
        // Apply line filter
        if ($lineId) {
            $query->whereHas('machine', function($q) use ($lineId) {
                $q->where('line_id', $lineId);
            });
        }
        
        // Apply machine type filter
        if ($machineTypeId) {
            $query->whereHas('machine', function($q) use ($machineTypeId) {
                $q->where('type_id', $machineTypeId);
            });
        }
        
        // Apply search ID machine filter
        if ($searchIdMachine) {
            $query->whereHas('machine', function($q) use ($searchIdMachine) {
                $q->where('idMachine', 'like', '%' . $searchIdMachine . '%');
            });
        }
        
        // Get all schedules grouped by machine - ensure unique machine_id
        $schedules = $query->orderBy('start_date', 'asc')->get();
        
        // Ensure we have unique machines loaded
        $uniqueMachineIds = $schedules->pluck('machine_id')->unique();
        $machines = Machine::whereIn('id', $uniqueMachineIds)
            ->with(['plant', 'line', 'machineType'])
            ->get()
            ->keyBy('id');
        
        // Get distinct values for filters
        $plants = \App\Models\Plant::orderBy('name')->get();
        $lines = \App\Models\Line::orderBy('name')->get();
        $machineTypes = MachineType::orderBy('name')->get();
        
        // Group schedules by machine_id and start_date (jadwal = machine_id + date)
        $machinesData = [];
        foreach ($schedules as $schedule) {
            $machineId = $schedule->machine_id;
            
            // Skip if machine not found
            if (!isset($machines[$machineId])) {
                continue;
            }
            
            $scheduleDate = $schedule->start_date;
            if (is_string($scheduleDate)) {
                $dateFormatted = $scheduleDate;
            } elseif ($scheduleDate instanceof Carbon) {
                $dateFormatted = $scheduleDate->format('Y-m-d');
            } else {
                $dateFormatted = $scheduleDate->format('Y-m-d');
            }
            $key = $machineId . '_' . $dateFormatted;
            
            if (!isset($machinesData[$key])) {
                $machine = $machines[$machineId];
                $machinesData[$key] = [
                    'machine' => $machine,
                    'date' => $dateFormatted,
                    'schedules' => [],
                ];
            }
            $machinesData[$key]['schedules'][] = $schedule;
        }
        
        // Convert to array for pagination
        $schedulesDataForJs = [];
        foreach ($machinesData as $key => $data) {
            $schedulesDataForJs[$data['machine']->id][$key] = $data['schedules']->map(function($schedule) {
                return [
                    'schedule_id' => $schedule->id,
                    'maintenance_point_name' => $schedule->maintenancePoint->name ?? $schedule->title,
                    'standard_name' => $schedule->standard->name ?? '-',
                    'standard_unit' => $schedule->standard->unit ?? '-',
                    'standard_min' => $schedule->standard->min_value,
                    'standard_max' => $schedule->standard->max_value,
                    'standard_target' => $schedule->standard->target_value,
                    'execution_status' => $schedule->executions()->latest()->first()?->status ?? 'pending',
                    'execution_id' => $schedule->executions()->latest()->first()?->id ?? null,
                ];
            })->toArray();
        }
        
        return view('predictive-maintenance.scheduling.index', compact(
            'machinesData',
            'schedulesDataForJs',
            'plants',
            'lines',
            'machineTypes',
            'periodType',
            'periodMonth',
            'periodYear',
            'plantId',
            'lineId',
            'machineTypeId',
            'searchIdMachine'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $machineTypes = MachineType::orderBy('name')->get();
        $users = User::whereIn('role', ['mekanik', 'team_leader', 'group_leader', 'coordinator'])->get();
        $standards = Standard::where('status', 'active')->orderBy('name')->get();
        
        return view('predictive-maintenance.scheduling.create', compact('machineTypes', 'users', 'standards'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'maintenance_category' => 'required|in:autonomous,preventive,predictive',
            'standard_id' => 'required|exists:standards,id', // Required for predictive maintenance
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'preferred_time' => 'nullable|date_format:H:i',
            'estimated_duration' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive,completed,cancelled',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $machine = Machine::findOrFail($validated['machine_id']);
        $typeId = $machine->type_id;
        $category = $validated['maintenance_category'];
        $standardId = $validated['standard_id'];
        
        // Get all maintenance points for this machine type and category
        $maintenancePoints = MaintenancePoint::where('machine_type_id', $typeId)
            ->where('category', $category)
            ->orderBy('sequence', 'asc')
            ->get();
        
        if ($maintenancePoints->isEmpty()) {
            return back()->withErrors(['maintenance_category' => 'Tidak ada maintenance point untuk kategori ini. Silakan buat maintenance point terlebih dahulu.'])->withInput();
        }
        
        $schedulesCreated = 0;
        $endOfYear = $this->calculateEndDate($validated['start_date'], null, null);
        
        // Create schedule for each maintenance point with standard
        foreach ($maintenancePoints as $point) {
            $frequencyType = $point->frequency_type ?? 'monthly';
            $frequencyValue = $point->frequency_value ?? 1;
            $currentDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($endOfYear);
            
            // Generate schedules until end of year
            while ($currentDate->lte($endDate)) {
                PredictiveMaintenanceSchedule::create([
                    'machine_id' => $validated['machine_id'],
                    'maintenance_point_id' => $point->id,
                    'standard_id' => $standardId, // Include standard_id
                    'title' => $point->name,
                    'description' => $point->instruction,
                    'frequency_type' => $frequencyType,
                    'frequency_value' => $frequencyValue,
                    'start_date' => $currentDate->format('Y-m-d'),
                    'end_date' => $endOfYear,
                    'preferred_time' => $validated['preferred_time'] ?? null,
                    'estimated_duration' => $validated['estimated_duration'] ?? null,
                    'status' => $validated['status'],
                    'assigned_to' => $validated['assigned_to'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);
                
                $schedulesCreated++;
                
                // Calculate next schedule date based on frequency
                $currentDate = $this->calculateNextDate($currentDate, $frequencyType, $frequencyValue);
                
                // Safety check to prevent infinite loop
                if ($schedulesCreated > 1000) {
                    break;
                }
            }
        }

        $pointsCount = $maintenancePoints->count();
        return redirect()->route('predictive-maintenance.scheduling.index')
            ->with('success', "Schedule berhasil dibuat: {$pointsCount} maintenance point(s) dengan total {$schedulesCreated} jadwal sampai akhir tahun.");
    }
    
    private function calculateEndDate($startDate, $frequencyType, $frequencyValue = 1)
    {
        $start = Carbon::parse($startDate);
        $year = $start->year;
        
        // Return end of year
        return Carbon::create($year, 12, 31)->format('Y-m-d');
    }
    
    private function calculateNextDate($currentDate, $frequencyType, $frequencyValue = 1)
    {
        $next = clone $currentDate;
        
        switch ($frequencyType) {
            case 'daily':
                $next->addDays($frequencyValue);
                break;
            case 'weekly':
                $next->addWeeks($frequencyValue);
                break;
            case 'monthly':
                $next->addMonths($frequencyValue);
                break;
            case 'quarterly':
                $next->addMonths($frequencyValue * 3);
                break;
            case 'yearly':
                $next->addYears($frequencyValue);
                break;
            default:
                // Default to monthly
                $next->addMonths($frequencyValue);
                break;
        }
        
        return $next;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $schedule = PredictiveMaintenanceSchedule::with(['machine', 'maintenancePoint', 'standard', 'assignedUser', 'executions.performedBy'])
            ->findOrFail($id);
        
        return view('predictive-maintenance.scheduling.show', compact('schedule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $schedule = PredictiveMaintenanceSchedule::findOrFail($id);
        $users = User::whereIn('role', ['mekanik', 'team_leader', 'group_leader', 'coordinator'])->get();
        $standards = Standard::where('status', 'active')->orderBy('name')->get();
        
        return view('predictive-maintenance.scheduling.edit', compact('schedule', 'users', 'standards'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'standard_id' => 'required|exists:standards,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'preferred_time' => 'nullable|date_format:H:i',
            'estimated_duration' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive,completed,cancelled',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $schedule = PredictiveMaintenanceSchedule::findOrFail($id);
        $schedule->update($validated);

        return redirect()->route('predictive-maintenance.scheduling.index')
            ->with('success', 'Schedule berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $schedule = PredictiveMaintenanceSchedule::findOrFail($id);
        $schedule->delete();

        return redirect()->route('predictive-maintenance.scheduling.index')
            ->with('success', 'Schedule berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers\PredictiveMaintenance;

use App\Http\Controllers\Controller;
use App\Models\PredictiveMaintenanceExecution;
use App\Models\PredictiveMaintenanceSchedule;
use App\Models\Standard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ControllingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get filter parameters (default: current month and year)
        $filterMonth = $request->get('month', now()->month);
        $filterYear = $request->get('year', now()->year);
        
        // Calculate start and end date for the selected month
        $startDate = Carbon::create($filterYear, $filterMonth, 1)->startOfMonth();
        $endDate = Carbon::create($filterYear, $filterMonth, 1)->endOfMonth();
        
        // Get all active schedules grouped by machine, filtered by month and year
        $schedules = PredictiveMaintenanceSchedule::where('status', 'active')
            ->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with(['machine.plant', 'machine.line', 'machine.machineType', 'standard', 'assignedUser', 'executions'])
            ->orderBy('start_date', 'asc')
            ->get();
        
        // Get unique machines
        $uniqueMachineIds = $schedules->pluck('machine_id')->unique();
        $machines = \App\Models\Machine::whereIn('id', $uniqueMachineIds)
            ->with(['plant', 'line', 'machineType'])
            ->get()
            ->keyBy('id');
        
        // Group schedules by machine_id
        $machinesData = [];
        foreach ($schedules as $schedule) {
            $machineId = $schedule->machine_id;
            
            if (!isset($machines[$machineId])) {
                continue;
            }
            
            if (!isset($machinesData[$machineId])) {
                $machine = $machines[$machineId];
                $machinesData[$machineId] = [
                    'machine' => $machine,
                    'schedules' => [],
                    'schedule_dates' => [], // Store unique dates for this month
                    'total_schedules' => 0,
                    'completed_schedules' => 0,
                    'pending_schedules' => 0,
                    'overdue_schedules' => 0,
                ];
            }
            
            $machinesData[$machineId]['schedules'][] = $schedule;
            $machinesData[$machineId]['total_schedules']++;
            
            // Add date to schedule_dates if not already present
            $scheduleDate = $schedule->start_date->format('Y-m-d');
            if (!in_array($scheduleDate, $machinesData[$machineId]['schedule_dates'])) {
                $machinesData[$machineId]['schedule_dates'][] = $scheduleDate;
            }
            
            // Check execution status
            $hasExecution = $schedule->executions()->exists();
            $isOverdue = !$hasExecution && $schedule->start_date < now()->toDateString() && $schedule->status == 'active';
            
            if ($hasExecution) {
                $execution = $schedule->executions()->latest()->first();
                if ($execution && $execution->status == 'completed') {
                    $machinesData[$machineId]['completed_schedules']++;
                } else {
                    $machinesData[$machineId]['pending_schedules']++;
                }
            } else {
                if ($isOverdue) {
                    $machinesData[$machineId]['overdue_schedules']++;
                } else {
                    $machinesData[$machineId]['pending_schedules']++;
                }
            }
        }
        
        // Calculate completion percentage for each machine and sort schedule dates
        foreach ($machinesData as $machineId => $data) {
            // Calculate based on schedule dates (jadwal), not individual schedules
            $scheduleDates = $data['schedule_dates'];
            $totalJadwal = count($scheduleDates);
            $completedJadwal = 0;
            
            // Check each date to see if all schedules for that date are completed
            foreach ($scheduleDates as $date) {
                $schedulesForDate = collect($data['schedules'])->filter(function($s) use ($date) {
                    return $s->start_date->format('Y-m-d') == $date;
                });
                
                $allCompleted = true;
                foreach ($schedulesForDate as $s) {
                    $hasExecution = $s->executions()->exists();
                    if (!$hasExecution) {
                        $allCompleted = false;
                        break;
                    }
                    $execution = $s->executions()->latest()->first();
                    if (!$execution || $execution->status != 'completed') {
                        $allCompleted = false;
                        break;
                    }
                }
                
                if ($allCompleted) {
                    $completedJadwal++;
                }
            }
            
            $machinesData[$machineId]['total_jadwal'] = $totalJadwal;
            $machinesData[$machineId]['completed_jadwal'] = $completedJadwal;
            
            if ($totalJadwal > 0) {
                $machinesData[$machineId]['completion_percentage'] = round(($completedJadwal / $totalJadwal) * 100, 1);
            } else {
                $machinesData[$machineId]['completion_percentage'] = 0;
            }
            
            // Sort schedule dates
            sort($machinesData[$machineId]['schedule_dates']);
        }
        
        // Calculate statistics
        $today = now()->toDateString();
        $allMachineIds = $schedules->pluck('machine_id')->unique();
        
        $pendingExecutionsCount = PredictiveMaintenanceExecution::whereHas('schedule', function($q) use ($startDate, $endDate) {
            $q->where('status', 'active')
              ->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()]);
        })
        ->where('status', 'pending')
        ->count();
        
        $inProgressExecutionsCount = PredictiveMaintenanceExecution::whereHas('schedule', function($q) use ($startDate, $endDate) {
            $q->where('status', 'active')
              ->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()]);
        })
        ->where('status', 'in_progress')
        ->count();
        
        // Count machines where all schedules up to today are completed
        $completedMachinesCount = 0;
        foreach ($allMachineIds as $machineId) {
            $schedulesUpToToday = PredictiveMaintenanceSchedule::where('machine_id', $machineId)
                ->where('status', 'active')
                ->where('start_date', '<=', $today)
                ->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get();
            
            if ($schedulesUpToToday->count() == 0) {
                continue;
            }
            
            $allCompleted = true;
            foreach ($schedulesUpToToday as $schedule) {
                $hasExecution = $schedule->executions()->exists();
                if (!$hasExecution) {
                    $allCompleted = false;
                    break;
                }
                $execution = $schedule->executions()->latest()->first();
                if (!$execution || $execution->status != 'completed') {
                    $allCompleted = false;
                    break;
                }
            }
            
            if ($allCompleted) {
                $completedMachinesCount++;
            }
        }
        
        // Count overdue (past date, no execution or not completed)
        $overdueCount = 0;
        foreach ($schedules as $schedule) {
            if ($schedule->start_date->format('Y-m-d') < $today) {
                $hasExecution = $schedule->executions()->exists();
                if (!$hasExecution) {
                    $overdueCount++;
                } else {
                    $execution = $schedule->executions()->latest()->first();
                    if (!$execution || $execution->status != 'completed') {
                        $overdueCount++;
                    }
                }
            }
        }
        
        // Count plan machines (machines where all schedules up to today are still pending)
        $planMachinesCount = 0;
        foreach ($allMachineIds as $machineId) {
            $schedulesUpToToday = PredictiveMaintenanceSchedule::where('machine_id', $machineId)
                ->where('status', 'active')
                ->where('start_date', '<=', $today)
                ->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get();
            
            if ($schedulesUpToToday->count() == 0) {
                continue;
            }
            
            $allPending = true;
            foreach ($schedulesUpToToday as $schedule) {
                $hasExecution = $schedule->executions()->exists();
                if ($hasExecution) {
                    $execution = $schedule->executions()->latest()->first();
                    if ($execution && $execution->status != 'pending') {
                        $allPending = false;
                        break;
                    }
                }
            }
            
            if ($allPending) {
                $planMachinesCount++;
            }
        }
        
        $stats = [
            'pending' => $pendingExecutionsCount,
            'in_progress' => $inProgressExecutionsCount,
            'completed' => $completedMachinesCount,
            'plan' => $planMachinesCount,
            'overdue' => $overdueCount,
        ];
        
        return view('predictive-maintenance.controlling.index', compact(
            'machinesData',
            'stats',
            'filterMonth',
            'filterYear'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $machineTypes = \App\Models\MachineType::orderBy('name')->get();
        $users = User::whereIn('role', ['mekanik', 'team_leader', 'group_leader', 'coordinator'])->get();
        
        // Pre-fill machine type and machine if provided
        $selectedMachineTypeId = $request->get('type_machine_id');
        $selectedMachineId = $request->get('machine_id');
        $selectedScheduledDate = $request->get('scheduled_date');
        
        return view('predictive-maintenance.controlling.create', compact('machineTypes', 'users', 'selectedMachineTypeId', 'selectedMachineId', 'selectedScheduledDate'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine_id' => 'required|exists:machines,id',
            'scheduled_date' => 'required|date',
            'performed_by' => 'nullable|exists:users,id',
            'executions' => 'required|array|min:1',
            'executions.*.schedule_id' => 'required|exists:predictive_maintenance_schedules,id',
            'executions.*.execution_id' => 'nullable|exists:predictive_maintenance_executions,id',
            'executions.*.status' => 'required|in:pending,in_progress,completed,skipped,cancelled',
            'executions.*.measured_value' => 'nullable|numeric',
        ]);

        $executionsCreated = 0;
        
        foreach ($validated['executions'] as $executionData) {
            $schedule = PredictiveMaintenanceSchedule::findOrFail($executionData['schedule_id']);
            
            // Calculate measurement_status based on standard
            $measuredValue = $executionData['measured_value'] ?? null;
            $measurementStatus = null;
            
            if ($measuredValue !== null && $schedule->standard) {
                $measurementStatus = $schedule->standard->getMeasurementStatus($measuredValue);
            }
            
            if ($executionData['execution_id']) {
                // Update existing execution
                $execution = PredictiveMaintenanceExecution::findOrFail($executionData['execution_id']);
                $execution->update([
                    'status' => $executionData['status'],
                    'measured_value' => $measuredValue,
                    'measurement_status' => $measurementStatus,
                    'performed_by' => $validated['performed_by'] ?? $execution->performed_by,
                ]);
                $executionsCreated++;
            } else {
                // Create new execution
                PredictiveMaintenanceExecution::create([
                    'schedule_id' => $executionData['schedule_id'],
                    'scheduled_date' => $validated['scheduled_date'],
                    'status' => $executionData['status'],
                    'measured_value' => $measuredValue,
                    'measurement_status' => $measurementStatus,
                    'performed_by' => $validated['performed_by'] ?? null,
                ]);
                $executionsCreated++;
            }
        }

        return redirect()->route('predictive-maintenance.controlling.index')
            ->with('success', "Berhasil membuat/update {$executionsCreated} execution(s).");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $execution = PredictiveMaintenanceExecution::with(['schedule.machine', 'schedule.standard', 'performedBy'])
            ->findOrFail($id);
        
        return view('predictive-maintenance.controlling.show', compact('execution'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $execution = PredictiveMaintenanceExecution::findOrFail($id);
        $users = User::whereIn('role', ['mekanik', 'team_leader', 'group_leader', 'coordinator'])->get();
        
        return view('predictive-maintenance.controlling.edit', compact('execution', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,skipped,cancelled',
            'measured_value' => 'nullable|numeric',
            'actual_start_time' => 'nullable|date',
            'actual_end_time' => 'nullable|date|after:actual_start_time',
            'performed_by' => 'nullable|exists:users,id',
            'findings' => 'nullable|string',
            'actions_taken' => 'nullable|string',
            'notes' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
        ]);

        $execution = PredictiveMaintenanceExecution::findOrFail($id);
        
        // Calculate measurement_status based on standard
        $measuredValue = $validated['measured_value'] ?? null;
        $measurementStatus = null;
        
        if ($measuredValue !== null && $execution->schedule && $execution->schedule->standard) {
            $measurementStatus = $execution->schedule->standard->getMeasurementStatus($measuredValue);
        }
        
        $validated['measurement_status'] = $measurementStatus;
        
        $execution->update($validated);

        return redirect()->route('predictive-maintenance.controlling.index')
            ->with('success', 'Execution berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $execution = PredictiveMaintenanceExecution::findOrFail($id);
        $execution->delete();

        return redirect()->route('predictive-maintenance.controlling.index')
            ->with('success', 'Execution berhasil dihapus.');
    }
}

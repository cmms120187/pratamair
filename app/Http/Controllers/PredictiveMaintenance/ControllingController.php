<?php

namespace App\Http\Controllers\PredictiveMaintenance;

use App\Http\Controllers\Controller;
use App\Models\PredictiveMaintenanceExecution;
use App\Models\PredictiveMaintenanceSchedule;
use App\Models\MachineErp;
use App\Models\Standard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ControllingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get filter parameters (default: current month and year)
        // Priority: query parameter > session > current month/year
        $filterMonth = $request->get('month', session('predictive_controlling_filter_month', now()->month));
        $filterYear = $request->get('year', session('predictive_controlling_filter_year', now()->year));
        
        // Save filter to session for persistence
        session([
            'predictive_controlling_filter_month' => $filterMonth,
            'predictive_controlling_filter_year' => $filterYear,
        ]);
        
        // Calculate start and end date for the selected month
        $startDate = Carbon::create($filterYear, $filterMonth, 1)->startOfMonth();
        $endDate = Carbon::create($filterYear, $filterMonth, 1)->endOfMonth();
        
        // Get all active schedules grouped by machine, filtered by month and year
        $schedules = PredictiveMaintenanceSchedule::where('status', 'active')
            ->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with(['machineErp.roomErp', 'machineErp.machineType', 'standard', 'assignedUser', 'executions', 'maintenancePoint'])
            ->orderBy('start_date', 'asc')
            ->get();
        
        // Get unique machines from MachineErp
        $uniqueMachineErpIds = $schedules->pluck('machine_erp_id')->unique();
        $machines = \App\Models\MachineErp::whereIn('id', $uniqueMachineErpIds)
            ->with(['roomErp', 'machineType'])
            ->get()
            ->keyBy('id');
        
        // Group schedules by machine_erp_id
        $machinesData = [];
        foreach ($schedules as $schedule) {
            $machineId = $schedule->machine_erp_id;
            
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
            
            // Check execution status - use eager loaded executions
            $hasExecution = $schedule->executions->isNotEmpty();
            $isOverdue = !$hasExecution && $schedule->start_date < now()->toDateString() && $schedule->status == 'active';
            
            if ($hasExecution) {
                $execution = $schedule->executions->sortByDesc('created_at')->first();
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
                $schedulesForDate = collect($data['schedules'])->filter(function($schedule) use ($date) {
                    return $schedule->start_date->format('Y-m-d') === $date;
                });
                
                // Check if all schedules for this date have completed execution
                $allCompletedForDate = true;
                foreach ($schedulesForDate as $schedule) {
                    // Use eager loaded executions instead of querying again
                    $hasCompletedExecution = $schedule->executions
                        ->where('status', 'completed')
                        ->isNotEmpty();
                    
                    if (!$hasCompletedExecution) {
                        $allCompletedForDate = false;
                        break;
                    }
                }
                
                if ($allCompletedForDate && $schedulesForDate->count() > 0) {
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
            
            // Determine machine condition per maintenance point
            // Group schedules by maintenance_point_id to get condition per point
            $maintenancePointsConditions = [];
            
            foreach ($data['schedules'] as $schedule) {
                $pointId = $schedule->maintenance_point_id ?? 'no_point_' . $schedule->id;
                $pointName = $schedule->maintenancePoint->name ?? $schedule->title ?? 'Point ' . $schedule->id;
                
                // Get latest completed execution for this schedule
                $latestExecution = $schedule->executions
                    ->where('status', 'completed')
                    ->sortByDesc('created_at')
                    ->first();
                
                // Recalculate measurement_status if standard exists and measured_value exists
                $condition = 'no_data';
                if ($latestExecution && $latestExecution->measured_value !== null && $schedule->standard) {
                    $condition = $schedule->standard->getMeasurementStatus($latestExecution->measured_value);
                    
                    // Update the execution's measurement_status if it's different
                    if ($latestExecution->measurement_status !== $condition) {
                        $latestExecution->measurement_status = $condition;
                        $latestExecution->save();
                    }
                } elseif ($latestExecution && $latestExecution->measurement_status) {
                    $condition = $latestExecution->measurement_status;
                }
                
                // Store condition per point (keep the worst condition if multiple schedules for same point)
                if (!isset($maintenancePointsConditions[$pointId])) {
                    $maintenancePointsConditions[$pointId] = [
                        'point_id' => $pointId,
                        'point_name' => $pointName,
                        'condition' => $condition,
                        'order' => $schedule->maintenancePoint->sequence ?? 999, // Use sequence for ordering
                    ];
                } else {
                    // Update to worst condition (critical > caution > warning > normal > no_data)
                    $currentCondition = $maintenancePointsConditions[$pointId]['condition'];
                    if ($condition == 'critical' || 
                        ($condition == 'caution' && !in_array($currentCondition, ['critical'])) ||
                        ($condition == 'warning' && !in_array($currentCondition, ['critical', 'caution'])) ||
                        ($condition == 'normal' && $currentCondition == 'no_data')) {
                        $maintenancePointsConditions[$pointId]['condition'] = $condition;
                    }
                }
            }
            
            // Sort by sequence/order and store maintenance points conditions
            usort($maintenancePointsConditions, function($a, $b) {
                return ($a['order'] ?? 999) <=> ($b['order'] ?? 999);
            });
            $machinesData[$machineId]['maintenance_points_conditions'] = array_values($maintenancePointsConditions);
            
            // Determine overall machine condition (worst case) for backward compatibility
            $machineCondition = 'no_data';
            if (!empty($maintenancePointsConditions)) {
                $allConditions = array_column($maintenancePointsConditions, 'condition');
                if (in_array('critical', $allConditions)) {
                    $machineCondition = 'critical';
                } elseif (in_array('caution', $allConditions)) {
                    $machineCondition = 'caution';
                } elseif (in_array('warning', $allConditions)) {
                    $machineCondition = 'warning';
                } elseif (in_array('normal', $allConditions)) {
                    $machineCondition = 'normal';
                }
            }
            
            $machinesData[$machineId]['machine_condition'] = $machineCondition;
            
            // Sort schedule dates
            sort($machinesData[$machineId]['schedule_dates']);
        }
        
        // Calculate statistics
        $today = now()->toDateString();
        $allMachineErpIds = $schedules->pluck('machine_erp_id')->unique();
        
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
        foreach ($allMachineErpIds as $machineErpId) {
            $schedulesUpToToday = PredictiveMaintenanceSchedule::where('machine_erp_id', $machineErpId)
                ->where('status', 'active')
                ->where('start_date', '<=', $today)
                ->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->with('executions')
                ->get();
            
            if ($schedulesUpToToday->count() == 0) {
                continue;
            }
            
            $allCompleted = true;
            foreach ($schedulesUpToToday as $schedule) {
                // Use eager loaded executions
                $hasExecution = $schedule->executions->isNotEmpty();
                if (!$hasExecution) {
                    $allCompleted = false;
                    break;
                }
                $execution = $schedule->executions->sortByDesc('created_at')->first();
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
                // Use eager loaded executions
                $hasExecution = $schedule->executions->isNotEmpty();
                if (!$hasExecution) {
                    $overdueCount++;
                } else {
                    $execution = $schedule->executions->sortByDesc('created_at')->first();
                    if (!$execution || $execution->status != 'completed') {
                        $overdueCount++;
                    }
                }
            }
        }
        
        // Count plan machines (machines where all schedules up to today are still pending)
        $planMachinesCount = 0;
        foreach ($allMachineErpIds as $machineErpId) {
            $schedulesUpToToday = PredictiveMaintenanceSchedule::where('machine_erp_id', $machineErpId)
                ->where('status', 'active')
                ->where('start_date', '<=', $today)
                ->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->with('executions')
                ->get();
            
            if ($schedulesUpToToday->count() == 0) {
                continue;
            }
            
            $allPending = true;
            foreach ($schedulesUpToToday as $schedule) {
                // Use eager loaded executions
                $hasExecution = $schedule->executions->isNotEmpty();
                if ($hasExecution) {
                    $execution = $schedule->executions->sortByDesc('created_at')->first();
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
            'machine_id' => 'required|exists:machine_erp,id',
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
            
            // Check if execution_id exists and is not empty
            $executionId = $executionData['execution_id'] ?? null;
            
            if ($executionId) {
                // Update existing execution by ID
                $execution = PredictiveMaintenanceExecution::findOrFail($executionId);
                $execution->update([
                    'status' => $executionData['status'],
                    'measured_value' => $measuredValue,
                    'measurement_status' => $measurementStatus,
                    'performed_by' => $validated['performed_by'] ?? $execution->performed_by,
                ]);
                $executionsCreated++;
            } else {
                // Check if execution already exists for this schedule_id and scheduled_date
                // 1 jadwal = 1 execution (update existing, don't create duplicate)
                $existingExecution = PredictiveMaintenanceExecution::where('schedule_id', $executionData['schedule_id'])
                    ->where('scheduled_date', $validated['scheduled_date'])
                    ->first();
                
                if ($existingExecution) {
                    // Update existing execution (same jadwal, just update status)
                    $existingExecution->update([
                        'status' => $executionData['status'],
                        'measured_value' => $measuredValue,
                        'measurement_status' => $measurementStatus,
                        'performed_by' => $validated['performed_by'] ?? $existingExecution->performed_by,
                    ]);
                    $executionsCreated++;
                } else {
                    // Create new execution only if doesn't exist
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
        }

        // Get filter from session or use scheduled_date month/year
        $redirectMonth = session('predictive_controlling_filter_month', Carbon::parse($validated['scheduled_date'])->month);
        $redirectYear = session('predictive_controlling_filter_year', Carbon::parse($validated['scheduled_date'])->year);
        
        return redirect()->route('predictive-maintenance.controlling.index', [
            'month' => $redirectMonth,
            'year' => $redirectYear
        ])->with('success', "Berhasil membuat/update {$executionsCreated} execution(s).");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $execution = PredictiveMaintenanceExecution::with([
            'schedule.machineErp.machineType',
            'schedule.machineErp.roomErp',
            'schedule.maintenancePoint',
            'schedule.standard',
            'performedBy'
        ])->findOrFail($id);
        
        return view('predictive-maintenance.controlling.show', compact('execution'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $execution = PredictiveMaintenanceExecution::with([
            'schedule.machineErp.machineType',
            'schedule.machineErp.roomErp',
            'schedule.maintenancePoint',
            'schedule.standard',
            'performedBy'
        ])->findOrFail($id);
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

        // Get filter from session or use execution scheduled_date month/year
        $redirectMonth = session('predictive_controlling_filter_month', Carbon::parse($execution->scheduled_date)->month);
        $redirectYear = session('predictive_controlling_filter_year', Carbon::parse($execution->scheduled_date)->year);
        
        return redirect()->route('predictive-maintenance.controlling.index', [
            'month' => $redirectMonth,
            'year' => $redirectYear
        ])->with('success', 'Execution berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $execution = PredictiveMaintenanceExecution::findOrFail($id);
        
        // Get filter from session before deleting
        $redirectMonth = session('predictive_controlling_filter_month', Carbon::parse($execution->scheduled_date)->month);
        $redirectYear = session('predictive_controlling_filter_year', Carbon::parse($execution->scheduled_date)->year);
        
        $execution->delete();

        return redirect()->route('predictive-maintenance.controlling.index', [
            'month' => $redirectMonth,
            'year' => $redirectYear
        ])->with('success', 'Execution berhasil dihapus.');
    }
    
    /**
     * Show machine condition details
     */
    public function showMachineCondition(Request $request, $machineId)
    {
        $filterMonth = $request->get('month', session('predictive_controlling_filter_month', now()->month));
        $filterYear = $request->get('year', session('predictive_controlling_filter_year', now()->year));
        
        // Calculate start and end date for the selected month
        $startDate = Carbon::create($filterYear, $filterMonth, 1)->startOfMonth();
        $endDate = Carbon::create($filterYear, $filterMonth, 1)->endOfMonth();
        
        // Get machine
        $machine = MachineErp::with(['roomErp', 'machineType'])->findOrFail($machineId);
        
        // Get all schedules for this machine in the selected month
        $schedules = PredictiveMaintenanceSchedule::where('machine_erp_id', $machineId)
            ->where('status', 'active')
            ->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with(['maintenancePoint', 'standard', 'executions' => function($query) {
                $query->where('status', 'completed')
                      ->with('performedBy')
                      ->orderBy('created_at', 'desc');
            }])
            ->orderBy('start_date', 'asc')
            ->orderBy('maintenance_point_id')
            ->get();
        
        // Group schedules by maintenance point and get latest execution for each
        $maintenancePointsData = [];
        foreach ($schedules as $schedule) {
            $pointId = $schedule->maintenance_point_id ?? 'no_point_' . $schedule->id;
            
            if (!isset($maintenancePointsData[$pointId])) {
                $maintenancePointsData[$pointId] = [
                    'maintenance_point' => $schedule->maintenancePoint,
                    'point_name' => $schedule->maintenancePoint ? $schedule->maintenancePoint->name : $schedule->title,
                    'standard' => $schedule->standard,
                    'schedules' => [],
                    'latest_execution' => null,
                    'condition' => 'no_data',
                ];
            }
            
            $maintenancePointsData[$pointId]['schedules'][] = $schedule;
            
            // Get latest completed execution
            $latestExecution = $schedule->executions
                ->where('status', 'completed')
                ->sortByDesc('created_at')
                ->first();
            
            if ($latestExecution) {
                // Update latest execution if this one is newer
                if (!$maintenancePointsData[$pointId]['latest_execution'] || 
                    $latestExecution->created_at > $maintenancePointsData[$pointId]['latest_execution']->created_at) {
                    $maintenancePointsData[$pointId]['latest_execution'] = $latestExecution;
                    
                    // Recalculate measurement_status if standard exists and measured_value exists
                    // This ensures we use the latest logic with zones
                    $condition = 'no_data';
                    if ($latestExecution->measured_value !== null && $schedule->standard) {
                        $condition = $schedule->standard->getMeasurementStatus($latestExecution->measured_value);
                        
                        // Update the execution's measurement_status if it's different
                        if ($latestExecution->measurement_status !== $condition) {
                            $latestExecution->measurement_status = $condition;
                            $latestExecution->save();
                        }
                    } else {
                        $condition = $latestExecution->measurement_status ?? 'no_data';
                    }
                    
                    $maintenancePointsData[$pointId]['condition'] = $condition;
                }
            }
        }
        
        // Determine overall machine condition (worst case)
        // Priority: critical > caution > warning > normal > no_data
        $overallCondition = 'no_data';
        foreach ($maintenancePointsData as $pointData) {
            $pointCondition = $pointData['condition'];
            if ($pointCondition == 'critical') {
                $overallCondition = 'critical';
                break; // Critical is worst, no need to check further
            } elseif ($pointCondition == 'caution' && $overallCondition != 'critical') {
                $overallCondition = 'caution';
            } elseif ($pointCondition == 'warning' && $overallCondition != 'critical' && $overallCondition != 'caution') {
                $overallCondition = 'warning';
            } elseif ($pointCondition == 'normal' && $overallCondition == 'no_data') {
                $overallCondition = 'normal';
            }
        }
        
        return view('predictive-maintenance.controlling.machine-condition', [
            'machine' => $machine,
            'maintenancePointsData' => $maintenancePointsData,
            'overallCondition' => $overallCondition,
            'filterMonth' => $filterMonth,
            'filterYear' => $filterYear,
        ]);
    }
    
    /**
     * Get machines by type for AJAX (using MachineErp)
     */
    public function getMachinesByType(Request $request)
    {
        $typeId = $request->input('type_id');
        
        if (!$typeId) {
            return response()->json(['machines' => []]);
        }
        
        try {
            // Get machine IDs that have active schedules - using MachineErp
            $machineErpIdsWithSchedules = PredictiveMaintenanceSchedule::where('status', 'active')
                ->distinct()
                ->pluck('machine_erp_id')
                ->toArray();
            
            // Get machines by type that have active schedules
            $machines = MachineErp::where('machine_type_id', $typeId)
                ->whereIn('id', $machineErpIdsWithSchedules)
                ->with(['roomErp', 'machineType'])
                ->get()
                ->map(function($machine) {
                    return [
                        'id' => $machine->id,
                        'idMachine' => $machine->idMachine,
                        'name' => $machine->idMachine . ' - ' . ($machine->machineType->name ?? $machine->type_name ?? '-') . ' (' . ($machine->plant_name ?? '-') . '/' . ($machine->process_name ?? '-') . '/' . ($machine->line_name ?? '-') . ')'
                    ];
                });
            
            return response()->json(['machines' => $machines]);
        } catch (\Exception $e) {
            \Log::error('Error in getMachinesByType (Predictive)', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get maintenance points by machine and date for AJAX
     */
    public function getMaintenancePointsByMachineAndDate(Request $request)
    {
        $machineId = $request->input('machine_id');
        $scheduledDate = $request->input('scheduled_date');
        
        if (!$machineId || !$scheduledDate) {
            return response()->json(['maintenance_points' => []]);
        }
        
        try {
            // Get machine - using MachineErp
            $machine = MachineErp::findOrFail($machineId);
            
            // Get schedules for this machine that match the criteria:
            // 1. start_date = scheduled_date (jadwal untuk tanggal yang diset)
            // 2. OR start_date < scheduled_date (terlewat) BUT belum ada execution dengan status completed
            $schedules = PredictiveMaintenanceSchedule::where('machine_erp_id', $machineId)
                ->where('status', 'active')
                ->where(function($query) use ($scheduledDate) {
                    // Jadwal untuk tanggal yang diset
                    $query->where('start_date', $scheduledDate)
                        // ATAU jadwal yang terlewat (start_date < scheduled_date)
                        ->orWhere(function($q) use ($scheduledDate) {
                            $q->where('start_date', '<', $scheduledDate)
                                // TAPI belum ada execution dengan status completed
                                ->whereDoesntHave('executions', function($execQuery) {
                                    $execQuery->where('status', 'completed');
                                });
                        });
                })
                ->with(['maintenancePoint', 'standard', 'assignedUser', 'executions'])
                ->orderBy('start_date', 'asc')
                ->orderBy('maintenance_point_id')
                ->get();
            
            // Additional filter: untuk jadwal terlewat, pastikan belum ada execution completed
            // Use eager loaded executions
            $schedules = $schedules->filter(function($schedule) use ($scheduledDate) {
                // Jika start_date < scheduled_date (terlewat), cek apakah sudah completed
                if ($schedule->start_date < $scheduledDate) {
                    // Cek apakah ada execution dengan status completed untuk schedule ini
                    $hasCompletedExecution = $schedule->executions
                        ->where('status', 'completed')
                        ->isNotEmpty();
                    
                    // Jika sudah completed, jangan tampilkan
                    if ($hasCompletedExecution) {
                        return false;
                    }
                }
                
                return true;
            });
            
            // Get PIC from first schedule (all schedules for same machine should have same PIC)
            $picId = null;
            $picName = null;
            if ($schedules->count() > 0) {
                $firstSchedule = $schedules->first();
                $picId = $firstSchedule->assigned_to;
                $picName = $firstSchedule->assignedUser ? $firstSchedule->assignedUser->name : null;
            }
            
            $points = $schedules->map(function($schedule) use ($scheduledDate) {
                // Tentukan apakah ini jadwal terlewat
                $isOverdue = $schedule->start_date < $scheduledDate;
                
                // Cek execution untuk tanggal yang dipilih - use eager loaded executions
                $execution = $schedule->executions
                    ->where('scheduled_date', $scheduledDate)
                    ->sortByDesc('created_at')
                    ->first();
                
                $hasExecution = $execution !== null;
                
                // Jika tidak ada execution untuk tanggal yang dipilih dan ini adalah overdue,
                // cek execution terakhir untuk schedule ini (mungkin dari tanggal asli)
                if (!$hasExecution && $isOverdue) {
                    $execution = $schedule->executions
                        ->sortByDesc('created_at')
                        ->first();
                    $hasExecution = $execution !== null;
                }
                
                return [
                    'schedule_id' => $schedule->id,
                    'maintenance_point_id' => $schedule->maintenance_point_id,
                    'maintenance_point_name' => $schedule->maintenancePoint ? $schedule->maintenancePoint->name : $schedule->title,
                    'standard_name' => $schedule->standard ? $schedule->standard->name : '-',
                    'standard_unit' => $schedule->standard ? $schedule->standard->unit : '-',
                    'standard_min' => $schedule->standard ? $schedule->standard->min_value : null,
                    'standard_max' => $schedule->standard ? $schedule->standard->max_value : null,
                    'standard_target' => $schedule->standard ? $schedule->standard->target_value : null,
                    'instruction' => $schedule->description ?? ($schedule->maintenancePoint ? $schedule->maintenancePoint->instruction : ''),
                    'photo' => $schedule->maintenancePoint && $schedule->maintenancePoint->photo ? asset('public-storage/' . $schedule->maintenancePoint->photo) : null,
                    'has_execution' => $hasExecution,
                    'execution_id' => $execution ? $execution->id : null,
                    'execution_status' => $execution ? $execution->status : 'pending',
                    'measured_value' => $execution ? $execution->measured_value : null,
                    'measurement_status' => $execution ? $execution->measurement_status : null,
                    'is_overdue' => $isOverdue,
                    'original_start_date' => $schedule->start_date ? ($schedule->start_date instanceof Carbon ? $schedule->start_date->format('Y-m-d') : Carbon::parse($schedule->start_date)->format('Y-m-d')) : '',
                ];
            });
            
            return response()->json([
                'maintenance_points' => $points,
                'pic_id' => $picId,
                'pic_name' => $picName,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getMaintenancePointsByMachineAndDate (Predictive)', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Export predictive maintenance executions to Excel
     */
    public function export(Request $request)
    {
        try {
            // Get filter parameters
            $filterMonth = $request->get('month', session('predictive_controlling_filter_month', now()->month));
            $filterYear = $request->get('year', session('predictive_controlling_filter_year', now()->year));
            
            // Calculate start and end date for the selected month
            $startDate = Carbon::create($filterYear, $filterMonth, 1)->startOfMonth();
            $endDate = Carbon::create($filterYear, $filterMonth, 1)->endOfMonth();
            
            // Get executions with relationships
            $executions = PredictiveMaintenanceExecution::whereHas('schedule', function($q) use ($startDate, $endDate) {
                $q->where('status', 'active')
                  ->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->with([
                'schedule.machineErp.machineType',
                'schedule.machineErp.roomErp',
                'schedule.maintenancePoint',
                'schedule.standard',
                'performedBy'
            ])
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
            
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set header
            $sheet->setCellValue('A1', 'Schedule ID');
            $sheet->setCellValue('B1', 'Scheduled Date');
            $sheet->setCellValue('C1', 'ID Mesin');
            $sheet->setCellValue('D1', 'Nama Mesin');
            $sheet->setCellValue('E1', 'Plant');
            $sheet->setCellValue('F1', 'Process');
            $sheet->setCellValue('G1', 'Line');
            $sheet->setCellValue('H1', 'Room');
            $sheet->setCellValue('I1', 'Maintenance Point');
            $sheet->setCellValue('J1', 'Standard Name');
            $sheet->setCellValue('K1', 'Status');
            $sheet->setCellValue('L1', 'Actual Start Time');
            $sheet->setCellValue('M1', 'Actual End Time');
            $sheet->setCellValue('N1', 'Performed By (NIK)');
            $sheet->setCellValue('O1', 'Performed By (Name)');
            $sheet->setCellValue('P1', 'Measured Value');
            $sheet->setCellValue('Q1', 'Measurement Status');
            $sheet->setCellValue('R1', 'Findings');
            $sheet->setCellValue('S1', 'Actions Taken');
            $sheet->setCellValue('T1', 'Notes');
            $sheet->setCellValue('U1', 'Cost');
            
            // Style header
            $headerStyle = [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            ];
            $sheet->getStyle('A1:U1')->applyFromArray($headerStyle);
            
            // Write data
            $row = 2;
            foreach ($executions as $execution) {
                $schedule = $execution->schedule;
                if (!$schedule) {
                    continue;
                }
                
                $machine = $schedule->machineErp ?? null;
                
                $scheduledDate = '';
                if ($execution->scheduled_date) {
                    $scheduledDate = $execution->scheduled_date instanceof Carbon 
                        ? $execution->scheduled_date->format('Y-m-d') 
                        : Carbon::parse($execution->scheduled_date)->format('Y-m-d');
                }
                
                $actualStartTime = '';
                if ($execution->actual_start_time) {
                    $actualStartTime = $execution->actual_start_time instanceof Carbon 
                        ? $execution->actual_start_time->format('Y-m-d H:i:s') 
                        : Carbon::parse($execution->actual_start_time)->format('Y-m-d H:i:s');
                }
                
                $actualEndTime = '';
                if ($execution->actual_end_time) {
                    $actualEndTime = $execution->actual_end_time instanceof Carbon 
                        ? $execution->actual_end_time->format('Y-m-d H:i:s') 
                        : Carbon::parse($execution->actual_end_time)->format('Y-m-d H:i:s');
                }
                
                $sheet->setCellValue('A' . $row, $schedule->id ?? '');
                $sheet->setCellValue('B' . $row, $scheduledDate);
                $sheet->setCellValue('C' . $row, $machine ? ($machine->idMachine ?? '') : '');
                $sheet->setCellValue('D' . $row, $machine && $machine->machineType ? ($machine->machineType->name ?? '') : '');
                $sheet->setCellValue('E' . $row, $machine ? ($machine->plant_name ?? '') : '');
                $sheet->setCellValue('F' . $row, $machine ? ($machine->process_name ?? '') : '');
                $sheet->setCellValue('G' . $row, $machine ? ($machine->line_name ?? '') : '');
                $sheet->setCellValue('H' . $row, $machine && $machine->roomErp ? ($machine->roomErp->name ?? '') : '');
                $sheet->setCellValue('I' . $row, $schedule->maintenancePoint ? ($schedule->maintenancePoint->name ?? '') : ($schedule->title ?? ''));
                $sheet->setCellValue('J' . $row, $schedule->standard ? ($schedule->standard->name ?? '') : '');
                $sheet->setCellValue('K' . $row, ucfirst(str_replace('_', ' ', $execution->status ?? '')));
                $sheet->setCellValue('L' . $row, $actualStartTime);
                $sheet->setCellValue('M' . $row, $actualEndTime);
                $sheet->setCellValue('N' . $row, $execution->performedBy ? ($execution->performedBy->nik ?? '') : '');
                $sheet->setCellValue('O' . $row, $execution->performedBy ? ($execution->performedBy->name ?? '') : '');
                $sheet->setCellValue('P' . $row, $execution->measured_value ?? '');
                $sheet->setCellValue('Q' . $row, ucfirst($execution->measurement_status ?? ''));
                $sheet->setCellValue('R' . $row, $execution->findings ?? '');
                $sheet->setCellValue('S' . $row, $execution->actions_taken ?? '');
                $sheet->setCellValue('T' . $row, $execution->notes ?? '');
                $sheet->setCellValue('U' . $row, $execution->cost ?? '');
                $row++;
            }
            
            // Auto-size columns
            foreach (range('A', 'U') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            $filename = 'predictive_maintenance_executions_' . $filterYear . '_' . str_pad($filterMonth, 2, '0', STR_PAD_LEFT) . '_' . date('His') . '.xlsx';
            
            // Create temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'pm_executions_');
            if ($tempFile === false) {
                throw new \Exception('Failed to create temporary file');
            }
            
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);
            
            if (!file_exists($tempFile)) {
                throw new \Exception('Failed to save Excel file');
            }
            
            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Log::error('Error exporting predictive maintenance executions: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('predictive-maintenance.controlling.index')
                ->with('error', 'Error generating Excel file: ' . $e->getMessage());
        }
    }
    
    /**
     * Import predictive maintenance executions from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Get header row (first row)
            $header = [];
            $highestColumn = $worksheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            
            // Read header from row 1
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $cellValue = $worksheet->getCell($columnLetter . '1')->getValue();
                $header[] = trim($cellValue ?? '');
            }
            
            if (empty($header) || count($header) < 1) {
                return back()->withErrors(['excel_file' => 'Invalid Excel format. Please check the file format.']);
            }
            
            // Map header to column index
            $headerMap = [];
            foreach ($header as $index => $headerName) {
                $headerMap[strtolower(trim($headerName))] = $index;
            }
            
            $rowCount = 0;
            $errorCount = 0;
            $errors = [];
            $highestRow = $worksheet->getHighestRow();
            
            // Start from row 2 (skip header)
            for ($row = 2; $row <= $highestRow; $row++) {
                try {
                    // Read row data
                    $rowData = [];
                    for ($col = 1; $col <= $highestColumnIndex; $col++) {
                        $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                        $cellValue = $worksheet->getCell($columnLetter . $row)->getValue();
                        $rowData[] = $cellValue;
                    }
                    
                    // Get values by header name
                    $getValue = function($headerName) use ($headerMap, $rowData) {
                        $index = $headerMap[strtolower(trim($headerName))] ?? null;
                        return $index !== null ? trim($rowData[$index] ?? '') : '';
                    };
                    
                    // Required fields
                    $scheduleId = $getValue('Schedule ID');
                    $scheduledDate = $getValue('Scheduled Date');
                    $status = $getValue('Status');
                    
                    if (empty($scheduleId) || empty($scheduledDate) || empty($status)) {
                        $errorCount++;
                        $errors[] = "Row $row: Missing required fields (Schedule ID, Scheduled Date, or Status)";
                        continue;
                    }
                    
                    // Find schedule
                    $schedule = PredictiveMaintenanceSchedule::find($scheduleId);
                    if (!$schedule) {
                        $errorCount++;
                        $errors[] = "Row $row: Schedule ID $scheduleId not found";
                        continue;
                    }
                    
                    // Parse scheduled date
                    $scheduledDateParsed = $this->parseDate($scheduledDate);
                    if (!$scheduledDateParsed) {
                        $errorCount++;
                        $errors[] = "Row $row: Invalid Scheduled Date format";
                        continue;
                    }
                    
                    // Parse status
                    $statusNormalized = strtolower(str_replace(' ', '_', $status));
                    if (!in_array($statusNormalized, ['pending', 'in_progress', 'completed', 'skipped', 'cancelled'])) {
                        $errorCount++;
                        $errors[] = "Row $row: Invalid Status. Must be: pending, in_progress, completed, skipped, or cancelled";
                        continue;
                    }
                    
                    // Get or create execution
                    $execution = PredictiveMaintenanceExecution::where('schedule_id', $scheduleId)
                        ->where('scheduled_date', $scheduledDateParsed)
                        ->first();
                    
                    // Prepare execution data
                    $executionData = [
                        'schedule_id' => $scheduleId,
                        'scheduled_date' => $scheduledDateParsed,
                        'status' => $statusNormalized,
                    ];
                    
                    // Optional fields
                    $actualStartTime = $getValue('Actual Start Time');
                    if (!empty($actualStartTime)) {
                        $parsedStartTime = $this->parseDateTime($actualStartTime);
                        if ($parsedStartTime) {
                            $executionData['actual_start_time'] = $parsedStartTime;
                        }
                    }
                    
                    $actualEndTime = $getValue('Actual End Time');
                    if (!empty($actualEndTime)) {
                        $parsedEndTime = $this->parseDateTime($actualEndTime);
                        if ($parsedEndTime) {
                            $executionData['actual_end_time'] = $parsedEndTime;
                        }
                    }
                    
                    // Performed by (by NIK or name)
                    $performedByNik = $getValue('Performed By (NIK)');
                    $performedByName = $getValue('Performed By (Name)');
                    if (!empty($performedByNik)) {
                        $user = User::where('nik', $performedByNik)->first();
                        if ($user) {
                            $executionData['performed_by'] = $user->id;
                        }
                    } elseif (!empty($performedByName)) {
                        $user = User::where('name', $performedByName)->first();
                        if ($user) {
                            $executionData['performed_by'] = $user->id;
                        }
                    }
                    
                    // Measured value
                    $measuredValue = $getValue('Measured Value');
                    if (!empty($measuredValue) && is_numeric($measuredValue)) {
                        $executionData['measured_value'] = $measuredValue;
                        
                        // Calculate measurement_status based on standard
                        if ($schedule->standard) {
                            $executionData['measurement_status'] = $schedule->standard->getMeasurementStatus($measuredValue);
                        }
                    }
                    
                    // Other optional fields
                    $findings = $getValue('Findings');
                    if (!empty($findings)) {
                        $executionData['findings'] = $findings;
                    }
                    
                    $actionsTaken = $getValue('Actions Taken');
                    if (!empty($actionsTaken)) {
                        $executionData['actions_taken'] = $actionsTaken;
                    }
                    
                    $notes = $getValue('Notes');
                    if (!empty($notes)) {
                        $executionData['notes'] = $notes;
                    }
                    
                    $cost = $getValue('Cost');
                    if (!empty($cost) && is_numeric($cost)) {
                        $executionData['cost'] = $cost;
                    }
                    
                    // Create or update execution
                    if ($execution) {
                        $execution->update($executionData);
                    } else {
                        PredictiveMaintenanceExecution::create($executionData);
                    }
                    
                    $rowCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Row $row: " . $e->getMessage();
                    \Log::error('Error importing predictive maintenance execution row: ' . $e->getMessage(), [
                        'row' => $row,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            
            $message = "Imported $rowCount execution(s).";
            if ($errorCount > 0) {
                $message .= " $errorCount error(s) occurred.";
                if (count($errors) <= 10) {
                    $message .= " Errors: " . implode('; ', $errors);
                } else {
                    $message .= " First 10 errors: " . implode('; ', array_slice($errors, 0, 10));
                }
            }
            
            return redirect()->route('predictive-maintenance.controlling.index')
                ->with('success', $message)
                ->with('import_errors', $errors);
        } catch (\Exception $e) {
            \Log::error('Error uploading Excel file: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['excel_file' => 'Error reading Excel file: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Parse date from various formats
     */
    private function parseDate($dateValue)
    {
        if (empty($dateValue)) {
            return null;
        }
        
        // If it's already a date object
        if ($dateValue instanceof \DateTime) {
            return $dateValue->format('Y-m-d');
        }
        
        // Try to parse as date string
        try {
            $date = Carbon::parse($dateValue);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            // Try Excel date format (numeric)
            if (is_numeric($dateValue)) {
                try {
                    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
                    return $date->format('Y-m-d');
                } catch (\Exception $e2) {
                    return null;
                }
            }
            return null;
        }
    }
    
    /**
     * Parse datetime from various formats
     */
    private function parseDateTime($dateTimeValue)
    {
        if (empty($dateTimeValue)) {
            return null;
        }
        
        // If it's already a datetime object
        if ($dateTimeValue instanceof \DateTime) {
            return $dateTimeValue->format('Y-m-d H:i:s');
        }
        
        // Try to parse as datetime string
        try {
            $dateTime = Carbon::parse($dateTimeValue);
            return $dateTime->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            // Try Excel datetime format (numeric)
            if (is_numeric($dateTimeValue)) {
                try {
                    $dateTime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateTimeValue);
                    return $dateTime->format('Y-m-d H:i:s');
                } catch (\Exception $e2) {
                    return null;
                }
            }
            return null;
        }
    }
}

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
            ->with(['machineErp.roomErp', 'machineErp.machineType', 'standard', 'assignedUser', 'executions'])
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
            
            // Determine machine condition based on latest completed executions
            // Priority: critical > warning > normal > no data
            $machineCondition = 'no_data'; // default: no measurement data
            $latestExecutions = [];
            
            foreach ($data['schedules'] as $schedule) {
                // Get latest completed execution for this schedule
                $latestExecution = $schedule->executions
                    ->where('status', 'completed')
                    ->sortByDesc('created_at')
                    ->first();
                
                if ($latestExecution && $latestExecution->measurement_status) {
                    $latestExecutions[] = $latestExecution->measurement_status;
                }
            }
            
            // Determine worst condition (critical > warning > normal)
            if (!empty($latestExecutions)) {
                if (in_array('critical', $latestExecutions)) {
                    $machineCondition = 'critical';
                } elseif (in_array('warning', $latestExecutions)) {
                    $machineCondition = 'warning';
                } else {
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
                // Update existing execution
                $execution = PredictiveMaintenanceExecution::findOrFail($executionId);
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
                    $maintenancePointsData[$pointId]['condition'] = $latestExecution->measurement_status ?? 'no_data';
                }
            }
        }
        
        // Determine overall machine condition (worst case)
        $overallCondition = 'no_data';
        foreach ($maintenancePointsData as $pointData) {
            $pointCondition = $pointData['condition'];
            if ($pointCondition == 'critical') {
                $overallCondition = 'critical';
                break; // Critical is worst, no need to check further
            } elseif ($pointCondition == 'warning' && $overallCondition != 'critical') {
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
                    'photo' => $schedule->maintenancePoint && $schedule->maintenancePoint->photo ? Storage::url($schedule->maintenancePoint->photo) : null,
                    'has_execution' => $hasExecution,
                    'execution_id' => $execution ? $execution->id : null,
                    'execution_status' => $execution ? $execution->status : 'pending',
                    'measured_value' => $execution ? $execution->measured_value : null,
                    'measurement_status' => $execution ? $execution->measurement_status : null,
                    'is_overdue' => $isOverdue,
                    'original_start_date' => $schedule->start_date->format('Y-m-d'),
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
}

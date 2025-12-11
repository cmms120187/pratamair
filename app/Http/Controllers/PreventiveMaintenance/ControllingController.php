<?php

namespace App\Http\Controllers\PreventiveMaintenance;

use App\Http\Controllers\Controller;
use App\Models\PreventiveMaintenanceExecution;
use App\Models\PreventiveMaintenanceSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $startDate = \Carbon\Carbon::create($filterYear, $filterMonth, 1)->startOfMonth();
        $endDate = \Carbon\Carbon::create($filterYear, $filterMonth, 1)->endOfMonth();
        
        // Get all active schedules grouped by machine, filtered by month and year - now using MachineErp
        $schedules = PreventiveMaintenanceSchedule::where('status', 'active')
            ->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with(['machineErp.roomErp', 'machineErp.machineType', 'assignedUser', 'executions'])
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
                $schedulesForDate = collect($data['schedules'])->filter(function($schedule) use ($date) {
                    return $schedule->start_date->format('Y-m-d') === $date;
                });
                
                // Check if all schedules for this date have completed execution
                $allCompletedForDate = true;
                foreach ($schedulesForDate as $schedule) {
                    $hasCompletedExecution = $schedule->executions()
                        ->where('status', 'completed')
                        ->exists();
                    
                    if (!$hasCompletedExecution) {
                        $allCompletedForDate = false;
                        break;
                    }
                }
                
                if ($allCompletedForDate && $schedulesForDate->count() > 0) {
                    $completedJadwal++;
                }
            }
            
            $machinesData[$machineId]['completion_percentage'] = $totalJadwal > 0 ? round(($completedJadwal / $totalJadwal) * 100, 1) : 0;
            $machinesData[$machineId]['completed_jadwal'] = $completedJadwal;
            $machinesData[$machineId]['total_jadwal'] = $totalJadwal;
            
            // Sort schedule dates
            sort($machinesData[$machineId]['schedule_dates']);
            
            // Get PIC (most common assigned user)
            $assignedUsers = [];
            foreach ($data['schedules'] as $schedule) {
                if ($schedule->assigned_to && $schedule->assignedUser) {
                    $userId = $schedule->assigned_to;
                    if (!isset($assignedUsers[$userId])) {
                        $assignedUsers[$userId] = [
                            'name' => $schedule->assignedUser->name,
                            'count' => 0
                        ];
                    }
                    $assignedUsers[$userId]['count']++;
                }
            }
            
            if (!empty($assignedUsers)) {
                $mostCommonUser = collect($assignedUsers)->sortByDesc('count')->first();
                $machinesData[$machineId]['pic_name'] = $mostCommonUser['name'];
            } else {
                $machinesData[$machineId]['pic_name'] = '-';
            }
        }
        
        // Convert to collection and paginate
        $machinesCollection = collect($machinesData)->values();
        $currentPage = request()->get('page', 1);
        $perPage = 20;
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $machinesCollection->forPage($currentPage, $perPage),
            $machinesCollection->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        
        // Calculate stats
        $today = now()->toDateString();
        
        // Get all machines that have schedules - now using MachineErp
        $allMachineErpIds = PreventiveMaintenanceSchedule::where('status', 'active')
            ->distinct()
            ->pluck('machine_erp_id')
            ->toArray();
        
        // Count completed machines and plan machines (machines where all schedules up to today are still pending)
        $completedMachinesCount = 0;
        $planMachinesCount = 0;
        $pendingExecutionsCount = PreventiveMaintenanceExecution::where('status', 'pending')->count();
        $inProgressExecutionsCount = PreventiveMaintenanceExecution::where('status', 'in_progress')->count();
        
        foreach ($allMachineErpIds as $machineErpId) {
            // Get all schedules for this machine that are <= today
            $schedulesUpToToday = PreventiveMaintenanceSchedule::where('machine_erp_id', $machineErpId)
                ->where('status', 'active')
                ->where('start_date', '<=', $today)
                ->get();
            
            if ($schedulesUpToToday->count() == 0) {
                continue; // Skip if no schedules up to today
            }
            
            // Check if all schedules have completed execution
            $allCompleted = true;
            $allPending = true;
            
            foreach ($schedulesUpToToday as $schedule) {
                $hasExecution = $schedule->executions()->exists();
                
                if ($hasExecution) {
                    // Check if execution is completed
                    $hasCompletedExecution = $schedule->executions()
                        ->where('status', 'completed')
                        ->exists();
                    
                    if (!$hasCompletedExecution) {
                        $allCompleted = false;
                        // Check if all executions are still pending
                        $hasNonPendingExecution = $schedule->executions()
                            ->where('status', '!=', 'pending')
                            ->exists();
                        
                        if ($hasNonPendingExecution) {
                            $allPending = false;
                        }
                    }
                } else {
                    // No execution means still pending
                    $allCompleted = false;
                }
            }
            
            if ($allCompleted) {
                $completedMachinesCount++;
            } elseif ($allPending) {
                $planMachinesCount++;
            }
        }
        
        // Count overdue (executions with status pending and scheduled_date < today)
        $overdueCount = PreventiveMaintenanceExecution::where('status', 'pending')
            ->where('scheduled_date', '<', $today)
            ->count();
        
        $stats = [
            'pending' => $pendingExecutionsCount,
            'in_progress' => $inProgressExecutionsCount,
            'completed' => $completedMachinesCount,
            'plan' => $planMachinesCount,
            'overdue' => $overdueCount,
        ];
        
        return view('preventive-maintenance.controlling.index', compact('paginator', 'stats', 'filterMonth', 'filterYear'));
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
        $selectedScheduledDate = $request->get('scheduled_date'); // Get scheduled date from query
        
        return view('preventive-maintenance.controlling.create', compact('machineTypes', 'users', 'selectedMachineTypeId', 'selectedMachineId', 'selectedScheduledDate'));
    }
    
    /**
     * Get machines by type for AJAX
     * Only returns machines that have active schedules
     */
    public function getMachinesByType(Request $request)
    {
        $typeId = $request->input('type_id');
        
        if (!$typeId) {
            return response()->json(['machines' => []]);
        }
        
        try {
            // Get machine IDs that have active schedules - now using MachineErp
            $machineErpIdsWithSchedules = PreventiveMaintenanceSchedule::where('status', 'active')
                ->distinct()
                ->pluck('machine_erp_id')
                ->toArray();
            
            // Get machines by type that have active schedules
            $machines = \App\Models\MachineErp::where('machine_type_id', $typeId)
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
            \Log::error('Error in getMachinesByType', ['error' => $e->getMessage()]);
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
            // Get machine - now using MachineErp
            $machine = \App\Models\MachineErp::findOrFail($machineId);
            
            // Get schedules for this machine that match the criteria:
            // 1. start_date = scheduled_date (jadwal untuk tanggal yang diset)
            // 2. OR start_date < scheduled_date (terlewat) BUT belum ada execution dengan status completed
            $schedules = PreventiveMaintenanceSchedule::where('machine_erp_id', $machineId)
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
                ->with(['maintenancePoint', 'assignedUser', 'executions'])
                ->orderBy('start_date', 'asc')
                ->orderBy('maintenance_point_id')
                ->get();
            
            // Additional filter: untuk jadwal terlewat, pastikan belum ada execution completed
            $schedules = $schedules->filter(function($schedule) use ($scheduledDate) {
                // Jika start_date < scheduled_date (terlewat), cek apakah sudah completed
                if ($schedule->start_date < $scheduledDate) {
                    // Cek apakah ada execution dengan status completed untuk schedule ini
                    $hasCompletedExecution = $schedule->executions()
                        ->where('status', 'completed')
                        ->exists();
                    
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
                
                // Cek execution untuk tanggal yang dipilih
                $execution = $schedule->executions()
                    ->where('scheduled_date', $scheduledDate)
                    ->latest()
                    ->first();
                
                $hasExecution = $execution !== null;
                
                // Jika tidak ada execution untuk tanggal yang dipilih dan ini adalah overdue,
                // cek execution terakhir untuk schedule ini (mungkin dari tanggal asli)
                if (!$hasExecution && $isOverdue) {
                    $execution = $schedule->executions()
                        ->latest()
                        ->first();
                    $hasExecution = $execution !== null;
                }
                
                return [
                    'schedule_id' => $schedule->id,
                    'maintenance_point_id' => $schedule->maintenance_point_id,
                    'maintenance_point_name' => $schedule->maintenancePoint ? $schedule->maintenancePoint->name : $schedule->title,
                    'instruction' => $schedule->description,
                    'photo' => $schedule->maintenancePoint && $schedule->maintenancePoint->photo ? asset('public-storage/' . $schedule->maintenancePoint->photo) : null,
                    'has_execution' => $hasExecution,
                    'execution_id' => $execution ? $execution->id : null,
                    'execution_status' => $execution ? $execution->status : 'pending',
                    'is_overdue' => $isOverdue,
                    'original_start_date' => $schedule->start_date->format('Y-m-d'),
                ];
            });
            
            return response()->json([
                'maintenance_points' => $points,
                'pic_id' => $picId,
                'pic_name' => $picName
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getMaintenancePointsByMachineAndDate', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
            'executions.*.schedule_id' => 'required|exists:preventive_maintenance_schedules,id',
            'executions.*.execution_id' => 'nullable|exists:preventive_maintenance_executions,id',
            'executions.*.status' => 'required|in:pending,in_progress,completed,skipped,cancelled',
        ]);

        $executionsCreated = 0;
        
        foreach ($validated['executions'] as $executionData) {
            // Check if execution already exists (by execution_id if provided, or by schedule_id and scheduled_date)
            $existingExecution = null;
            
            if (isset($executionData['execution_id']) && $executionData['execution_id']) {
                // Update existing execution by ID (for overdue dates)
                $existingExecution = PreventiveMaintenanceExecution::find($executionData['execution_id']);
            } else {
                // Check if execution exists for this schedule and date
                $existingExecution = PreventiveMaintenanceExecution::where('schedule_id', $executionData['schedule_id'])
                    ->where('scheduled_date', $validated['scheduled_date'])
                    ->first();
            }
            
            if ($existingExecution) {
                // Update existing execution (including overdue dates)
                $existingExecution->update([
                    'scheduled_date' => $validated['scheduled_date'], // Update to new scheduled date
                    'status' => $executionData['status'],
                    'performed_by' => $validated['performed_by'],
                ]);
            } else {
                // Create new execution
                PreventiveMaintenanceExecution::create([
                    'schedule_id' => $executionData['schedule_id'],
                    'scheduled_date' => $validated['scheduled_date'],
                    'status' => $executionData['status'],
                    'performed_by' => $validated['performed_by'],
                ]);
            }
            
            $executionsCreated++;
        }

        return redirect()->route('preventive-maintenance.controlling.index')
            ->with('success', "{$executionsCreated} execution(s) berhasil dibuat/diupdate.");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $execution = PreventiveMaintenanceExecution::with(['schedule.machine', 'performedBy'])
            ->findOrFail($id);
        
        return view('preventive-maintenance.controlling.show', compact('execution'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $execution = PreventiveMaintenanceExecution::findOrFail($id);
        $users = User::whereIn('role', ['mekanik', 'team_leader', 'group_leader', 'coordinator'])->get();
        
        return view('preventive-maintenance.controlling.edit', compact('execution', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'scheduled_date' => 'required|date',
            'actual_start_time' => 'nullable|date',
            'actual_end_time' => 'nullable|date|after:actual_start_time',
            'status' => 'required|in:pending,in_progress,completed,skipped,cancelled',
            'performed_by' => 'nullable|exists:users,id',
            'findings' => 'nullable|string',
            'actions_taken' => 'nullable|string',
            'notes' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'photo_before' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'photo_after' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $execution = PreventiveMaintenanceExecution::findOrFail($id);
        
        // Handle photo uploads
        if ($request->hasFile('photo_before')) {
            if ($execution->photo_before && Storage::disk('public')->exists($execution->photo_before)) {
                Storage::disk('public')->delete($execution->photo_before);
            }
            $validated['photo_before'] = $request->file('photo_before')->store('pm-executions', 'public');
        }
        
        if ($request->hasFile('photo_after')) {
            if ($execution->photo_after && Storage::disk('public')->exists($execution->photo_after)) {
                Storage::disk('public')->delete($execution->photo_after);
            }
            $validated['photo_after'] = $request->file('photo_after')->store('pm-executions', 'public');
        }
        
        $execution->update($validated);

        return redirect()->route('preventive-maintenance.controlling.index')
            ->with('success', 'Execution berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $execution = PreventiveMaintenanceExecution::findOrFail($id);
        
        // Delete photos if exists
        if ($execution->photo_before && Storage::disk('public')->exists($execution->photo_before)) {
            Storage::disk('public')->delete($execution->photo_before);
        }
        if ($execution->photo_after && Storage::disk('public')->exists($execution->photo_after)) {
            Storage::disk('public')->delete($execution->photo_after);
        }
        
        $execution->delete();

        return redirect()->route('preventive-maintenance.controlling.index')
            ->with('success', 'Execution berhasil dihapus.');
    }
    
    /**
     * Batch update execution status for multiple executions.
     */
    public function batchUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'execution_ids' => 'required|array|min:1',
            'execution_ids.*' => 'required|exists:preventive_maintenance_executions,id',
            'status' => 'required|in:pending,in_progress,completed,skipped,cancelled',
        ]);
        
        $updatedCount = 0;
        
        foreach ($validated['execution_ids'] as $executionId) {
            $execution = PreventiveMaintenanceExecution::findOrFail($executionId);
            
            // Auto set start time if status changed to in_progress
            if ($validated['status'] == 'in_progress' && !$execution->actual_start_time) {
                $execution->actual_start_time = now();
            }
            
            // Auto set end time if status changed to completed
            if ($validated['status'] == 'completed' && !$execution->actual_end_time) {
                $execution->actual_end_time = now();
            }
            
            $execution->update([
                'status' => $validated['status'],
            ]);
            
            $updatedCount++;
        }
        
        return response()->json([
            'success' => true,
            'updated_count' => $updatedCount,
            'message' => "Berhasil mengupdate {$updatedCount} execution"
        ]);
    }
}

<?php

namespace App\Http\Controllers\PreventiveMaintenance;

use App\Http\Controllers\Controller;
use App\Models\PreventiveMaintenanceSchedule;
use App\Models\PreventiveMaintenanceExecution;
use App\Models\MachineErp;
use App\Models\MaintenancePoint;
use App\Models\User;
use App\Models\MachineType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        
        // Build query - now using MachineErp
        $query = PreventiveMaintenanceSchedule::with(['machineErp.roomErp', 'machineErp.machineType', 'maintenancePoint', 'assignedUser', 'executions']);
        
        // Apply period filter
        if ($periodType == 'month') {
            $query->whereYear('start_date', $periodYear)
                  ->whereMonth('start_date', $periodMonth);
        } else {
            $query->whereYear('start_date', $periodYear);
        }
        
        // Apply plant filter - filter by MachineErp plant_name
        if ($plantId) {
            $plant = \App\Models\Plant::find($plantId);
            if ($plant) {
                $query->whereHas('machineErp', function($q) use ($plant) {
                    $q->where('plant_name', $plant->name);
                });
            }
        }
        
        // Apply line filter - filter by MachineErp line_name
        if ($lineId) {
            $line = \App\Models\Line::find($lineId);
            if ($line) {
                $query->whereHas('machineErp', function($q) use ($line) {
                    $q->where('line_name', $line->name);
                });
            }
        }
        
        // Apply machine type filter
        if ($machineTypeId) {
            $query->whereHas('machineErp', function($q) use ($machineTypeId) {
                $q->where('machine_type_id', $machineTypeId);
            });
        }
        
        // Apply search ID machine filter
        if ($searchIdMachine) {
            $query->whereHas('machineErp', function($q) use ($searchIdMachine) {
                $q->where('idMachine', 'like', '%' . $searchIdMachine . '%');
            });
        }
        
        // Get all schedules grouped by machine - ensure unique machine_erp_id
        $schedules = $query->orderBy('start_date', 'asc')->get();
        
        // Ensure we have unique machines loaded from MachineErp
        $uniqueMachineErpIds = $schedules->pluck('machine_erp_id')->unique();
        $machines = MachineErp::whereIn('id', $uniqueMachineErpIds)
            ->with(['roomErp', 'machineType'])
            ->get()
            ->keyBy('id');
        
        // Get distinct values for filters
        $plants = \App\Models\Plant::orderBy('name')->get();
        $lines = \App\Models\Line::orderBy('name')->get();
        $machineTypes = MachineType::orderBy('name')->get();
        
        // Group schedules by machine_erp_id
        $machinesData = [];
        foreach ($schedules as $schedule) {
            $machineId = $schedule->machine_erp_id;
            
            // Skip if machine not found
            if (!isset($machines[$machineId])) {
                continue;
            }
            
            if (!isset($machinesData[$machineId])) {
                // Clone machine object to avoid reference issues
                $machine = $machines[$machineId];
                $machinesData[$machineId] = [
                    'machine' => $machine,
                    'schedules' => [],
                    'total_schedules' => 0,
                    'completed_schedules' => 0,
                    'pending_schedules' => 0,
                    'overdue_schedules' => 0,
                ];
            }
            
            $machinesData[$machineId]['schedules'][] = $schedule;
            $machinesData[$machineId]['total_schedules']++;
            
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
        
        // Calculate completion percentage for each machine and group schedules by date
        foreach ($machinesData as $machineId => $data) {
            // Sort schedules by start_date
            $schedules = $data['schedules'];
            usort($schedules, function($a, $b) {
                $dateA = is_string($a->start_date) ? \Carbon\Carbon::parse($a->start_date) : $a->start_date;
                $dateB = is_string($b->start_date) ? \Carbon\Carbon::parse($b->start_date) : $b->start_date;
                return $dateA <=> $dateB;
            });
            $machinesData[$machineId]['schedules'] = $schedules;
            
            // Group schedules by date
            $schedulesByDate = [];
            foreach ($schedules as $schedule) {
                $dateKey = is_string($schedule->start_date) ? \Carbon\Carbon::parse($schedule->start_date)->format('Y-m-d') : $schedule->start_date->format('Y-m-d');
                
                if (!isset($schedulesByDate[$dateKey])) {
                    $schedulesByDate[$dateKey] = [];
                }
                
                $schedulesByDate[$dateKey][] = $schedule;
            }
            
            $machinesData[$machineId]['schedules_by_date'] = $schedulesByDate;
            
            // Get PIC (assigned user) - get from first schedule or most common assigned user
            $assignedUsers = [];
            foreach ($schedules as $schedule) {
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
            
            // Get most common assigned user or first one
            if (!empty($assignedUsers)) {
                usort($assignedUsers, function($a, $b) {
                    return $b['count'] - $a['count'];
                });
                $machinesData[$machineId]['pic_name'] = $assignedUsers[0]['name'];
            } else {
                $machinesData[$machineId]['pic_name'] = '-';
            }
            
            if ($machinesData[$machineId]['total_schedules'] > 0) {
                $machinesData[$machineId]['completion_percentage'] = round(($machinesData[$machineId]['completed_schedules'] / $machinesData[$machineId]['total_schedules']) * 100, 1);
            } else {
                $machinesData[$machineId]['completion_percentage'] = 0;
            }
        }
        
        // Convert to collection and paginate manually - ensure unique machine_id and sort by machine id
        $machinesCollection = collect($machinesData)
            ->values()
            ->unique(function($item) {
                return $item['machine']->id;
            })
            ->sortBy(function($item) {
                return $item['machine']->id;
            })
            ->values();
        
        $perPage = 20;
        $currentPage = request()->get('page', 1);
        $items = $machinesCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $machinesCollection->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        
        // Prepare schedules data for JavaScript
        $schedulesDataForJs = [];
        foreach ($machinesData as $machineId => $data) {
            $machine = $data['machine'];
            $schedulesByDate = $data['schedules_by_date'] ?? [];
            
            if (!isset($schedulesDataForJs[$machineId])) {
                $schedulesDataForJs[$machineId] = [];
            }
            
            foreach ($schedulesByDate as $dateKey => $schedules) {
                $key = $machineId . '_' . $dateKey;
                $schedulesDataForJs[$machineId][$key] = [];
                
                foreach ($schedules as $schedule) {
                    $hasExecution = $schedule->executions()->exists();
                    $isOverdue = !$hasExecution && $schedule->start_date < now()->toDateString() && $schedule->status == 'active';
                    $isCompleted = false;
                    $executionId = null;
                    $executionStatus = null;
                    
                    if ($hasExecution) {
                        $execution = $schedule->executions()->latest()->first();
                        $isCompleted = $execution && $execution->status == 'completed';
                        $executionId = $execution ? $execution->id : null;
                        $executionStatus = $execution ? $execution->status : null;
                    }
                    
                    $photoUrl = null;
                    if ($schedule->maintenancePoint && $schedule->maintenancePoint->photo) {
                        $photoUrl = asset('public-storage/' . $schedule->maintenancePoint->photo);
                    }
                    
                    $schedulesDataForJs[$machineId][$key][] = [
                        'schedule_id' => $schedule->id,
                        'title' => $schedule->title,
                        'description' => $schedule->description,
                        'maintenance_point_name' => $schedule->maintenancePoint ? $schedule->maintenancePoint->name : '-',
                        'photo_url' => $photoUrl,
                        'status' => $schedule->status,
                        'is_completed' => $isCompleted,
                        'is_overdue' => $isOverdue,
                        'has_execution' => $hasExecution,
                        'execution_id' => $executionId,
                        'execution_status' => $executionStatus,
                        'frequency_type' => $schedule->frequency_type,
                        'frequency_value' => $schedule->frequency_value,
                    ];
                }
            }
        }
        
        return view('preventive-maintenance.scheduling.index', compact('paginator', 'plants', 'lines', 'machineTypes', 'periodType', 'periodMonth', 'periodYear', 'schedulesDataForJs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get machines that don't have schedules for the current year - now using MachineErp
        $currentYear = now()->year;
        $machinesWithSchedules = PreventiveMaintenanceSchedule::whereYear('start_date', $currentYear)
            ->distinct()
            ->pluck('machine_erp_id')
            ->toArray();
        
        $machines = MachineErp::with(['roomErp', 'machineType'])
            ->whereNotIn('id', $machinesWithSchedules)
            ->get();
        
        // Prepare machines data for JavaScript
        $machinesForJs = $machines->map(function($machine) {
            $machineTypeName = ($machine->machineType ? $machine->machineType->name : null) ?? $machine->type_name ?? '-';
            $roomName = ($machine->roomErp ? $machine->roomErp->name : null) ?? $machine->room_name ?? '-';
            return [
                'id' => $machine->id,
                'idMachine' => $machine->idMachine,
                'type_id' => $machine->machine_type_id ?? null,
                'machineType' => $machineTypeName,
                'plant' => $machine->plant_name ?? '-',
                'process' => $machine->process_name ?? '-',
                'line' => $machine->line_name ?? '-',
                'room' => $roomName,
            ];
        })->values();
        
        $machineTypes = \App\Models\MachineType::orderBy('name')->get();
        $maintenancePoints = MaintenancePoint::with('machineType')->get();
        $users = User::where('role', 'mekanik')->orderBy('name')->get();
        
        return view('preventive-maintenance.scheduling.create', compact('machines', 'machinesForJs', 'machineTypes', 'maintenancePoints', 'users'));
    }
    
    public function getMachinesByType(Request $request)
    {
        $typeId = $request->input('type_id');
        
        \Log::info('getMachinesByType called', ['type_id' => $typeId]);
        
        if (!$typeId) {
            return response()->json(['machines' => []]);
        }
        
        try {
            // Get machines that don't have schedules for the current year - now using MachineErp
            $currentYear = now()->year;
            $machinesWithSchedules = PreventiveMaintenanceSchedule::whereYear('start_date', $currentYear)
                ->distinct()
                ->pluck('machine_erp_id')
                ->toArray();
            
            $machines = MachineErp::where('machine_type_id', $typeId)
                ->whereNotIn('id', $machinesWithSchedules)
                ->with(['roomErp', 'machineType'])
                ->get()
                ->map(function($machine) {
                    return [
                        'id' => $machine->id,
                        'idMachine' => $machine->idMachine,
                        'name' => $machine->idMachine . ' - ' . ($machine->machineType->name ?? $machine->type_name ?? '-') . ' (' . ($machine->plant_name ?? '-') . '/' . ($machine->process_name ?? '-') . '/' . ($machine->line_name ?? '-') . ')'
                    ];
                });
            
            \Log::info('Machines found', ['count' => $machines->count()]);
            
            return response()->json(['machines' => $machines]);
        } catch (\Exception $e) {
            \Log::error('Error in getMachinesByType', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function getMaintenancePointsByCategory(Request $request)
    {
        $typeId = $request->input('type_id');
        $category = $request->input('category'); // autonomous, preventive, predictive
        
        \Log::info('getMaintenancePointsByCategory called', ['type_id' => $typeId, 'category' => $category]);
        
        if (!$typeId || !$category) {
            return response()->json(['maintenance_points' => []]);
        }
        
        try {
            // Get all maintenance points from the category for this machine type
            $maintenancePoints = MaintenancePoint::where('machine_type_id', $typeId)
                ->where('category', $category)
                ->orderBy('sequence', 'asc')
                ->get(['id', 'name', 'instruction', 'frequency_type', 'frequency_value', 'sequence', 'photo']);
            
            $points = $maintenancePoints->map(function($point) {
                return [
                    'id' => $point->id,
                    'name' => $point->name,
                    'instruction' => $point->instruction,
                    'frequency_type' => $point->frequency_type,
                    'frequency_value' => $point->frequency_value ?? 1,
                    'sequence' => $point->sequence,
                    'photo' => $point->photo ? asset('public-storage/' . $point->photo) : null
                ];
            });
            
            \Log::info('Maintenance points found', [
                'count' => $points->count(),
                'category' => $category,
                'type_id' => $typeId
            ]);
            
            return response()->json(['maintenance_points' => $points]);
        } catch (\Exception $e) {
            \Log::error('Error in getMaintenancePointsByCategory', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function getMaintenancePointByCategory(Request $request)
    {
        $typeId = $request->input('type_id');
        $category = $request->input('category'); // autonomous, preventive, predictive
        $pointId = $request->input('point_id'); // Optional: specific point ID
        
        \Log::info('getMaintenancePointByCategory called', ['type_id' => $typeId, 'category' => $category, 'point_id' => $pointId]);
        
        if (!$typeId || !$category) {
            return response()->json(['maintenance_point' => null, 'message' => 'Missing type_id or category']);
        }
        
        try {
            $query = MaintenancePoint::where('machine_type_id', $typeId)
                ->where('category', $category);
            
            // If point_id is provided, get that specific point
            if ($pointId) {
                $query->where('id', $pointId);
            }
            
            // Get first maintenance point from the category for this machine type
            $maintenancePoint = $query->orderBy('sequence', 'asc')->first();
            
            if ($maintenancePoint) {
                return response()->json([
                    'maintenance_point' => [
                        'id' => $maintenancePoint->id,
                        'name' => $maintenancePoint->name,
                        'instruction' => $maintenancePoint->instruction,
                        'frequency_type' => $maintenancePoint->frequency_type,
                        'frequency_value' => $maintenancePoint->frequency_value ?? 1
                    ],
                    'message' => 'Maintenance point found'
                ]);
            }
            
            return response()->json([
                'maintenance_point' => null,
                'message' => 'No maintenance point found for this category'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getMaintenancePointByCategory', ['error' => $e->getMessage()]);
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
            'maintenance_category' => 'required|in:autonomous,preventive,predictive',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'preferred_time' => 'nullable|date_format:H:i',
            'estimated_duration' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive,completed,cancelled',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $machineErp = MachineErp::findOrFail($validated['machine_id']);
        $typeId = $machineErp->machine_type_id;
        
        if (!$typeId) {
            return back()->withErrors(['machine_id' => 'Machine type belum ditentukan untuk mesin ini.'])->withInput();
        }
        $category = $validated['maintenance_category'];
        
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
        
        // Create schedule for each maintenance point
        foreach ($maintenancePoints as $point) {
            $frequencyType = $point->frequency_type ?? 'monthly';
            $frequencyValue = $point->frequency_value ?? 1;
            $currentDate = \Carbon\Carbon::parse($validated['start_date']);
            $endDate = \Carbon\Carbon::parse($endOfYear);
            
            // Generate schedules until end of year
            while ($currentDate->lte($endDate)) {
                PreventiveMaintenanceSchedule::create([
                    'machine_erp_id' => $validated['machine_id'],
                    'maintenance_point_id' => $point->id,
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
        return redirect()->route('preventive-maintenance.scheduling.index')
            ->with('success', "Schedule berhasil dibuat: {$pointsCount} maintenance point(s) dengan total {$schedulesCreated} jadwal sampai akhir tahun.");
    }
    
    private function calculateEndDate($startDate, $frequencyType, $frequencyValue = 1)
    {
        $start = \Carbon\Carbon::parse($startDate);
        $year = $start->year;
        
        // Return end of year
        return \Carbon\Carbon::create($year, 12, 31)->format('Y-m-d');
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
        $schedule = PreventiveMaintenanceSchedule::with(['machineErp.roomErp', 'machineErp.machineType', 'maintenancePoint', 'assignedUser', 'executions.performedBy'])
            ->findOrFail($id);
        
        return view('preventive-maintenance.scheduling.show', compact('schedule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $schedule = PreventiveMaintenanceSchedule::findOrFail($id);
        $machines = MachineErp::with(['roomErp', 'machineType'])->get();
        $maintenancePoints = MaintenancePoint::with('machineType')->get();
        $users = User::where('role', 'mekanik')->orderBy('name')->get();
        
        return view('preventive-maintenance.scheduling.edit', compact('schedule', 'machines', 'maintenancePoints', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'machine_id' => 'required|exists:machine_erp,id',
            'maintenance_point_id' => 'nullable|exists:maintenance_points,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'frequency_type' => 'required|in:daily,weekly,monthly,quarterly,yearly,custom',
            'frequency_value' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'preferred_time' => 'nullable|date_format:H:i',
            'estimated_duration' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive,completed,cancelled',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $schedule = PreventiveMaintenanceSchedule::findOrFail($id);
        $schedule->update($validated);

        return redirect()->route('preventive-maintenance.scheduling.index')
            ->with('success', 'Schedule berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $schedule = PreventiveMaintenanceSchedule::findOrFail($id);
        $schedule->delete();

        return redirect()->route('preventive-maintenance.scheduling.index')
            ->with('success', 'Schedule berhasil dihapus.');
    }
    
    /**
     * Delete all schedules for a specific machine.
     */
    public function deleteByMachine(string $machineId)
    {
        $machine = MachineErp::findOrFail($machineId);
        $schedulesCount = PreventiveMaintenanceSchedule::where('machine_erp_id', $machineId)->count();
        
        PreventiveMaintenanceSchedule::where('machine_erp_id', $machineId)->delete();

        return redirect()->route('preventive-maintenance.scheduling.index')
            ->with('success', "Semua schedule untuk mesin {$machine->idMachine} ({$schedulesCount} schedule) berhasil dihapus.");
    }
    
    /**
     * Batch update execution status for multiple schedules.
     */
    public function batchUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'updates' => 'required|array|min:1',
            'updates.*.schedule_id' => 'required|exists:preventive_maintenance_schedules,id',
            'updates.*.execution_id' => 'nullable|exists:preventive_maintenance_executions,id',
            'updates.*.scheduled_date' => 'required|date',
            'status' => 'required|in:pending,in_progress,completed,skipped,cancelled',
        ]);
        
        $updatedCount = 0;
        
        foreach ($validated['updates'] as $updateData) {
            $schedule = PreventiveMaintenanceSchedule::findOrFail($updateData['schedule_id']);
            
            // Check if execution exists
            if (isset($updateData['execution_id']) && $updateData['execution_id']) {
                // Update existing execution by ID
                $execution = PreventiveMaintenanceExecution::findOrFail($updateData['execution_id']);
                $execution->update([
                    'status' => $validated['status'],
                ]);
                $updatedCount++;
            } else {
                // Check if execution already exists for this schedule_id and scheduled_date
                // 1 jadwal = 1 execution (update existing, don't create duplicate)
                $existingExecution = PreventiveMaintenanceExecution::where('schedule_id', $updateData['schedule_id'])
                    ->where('scheduled_date', $updateData['scheduled_date'])
                    ->first();
                
                if ($existingExecution) {
                    // Update existing execution (same jadwal, just update status)
                    $existingExecution->update([
                        'status' => $validated['status'],
                        'performed_by' => $schedule->assigned_to ?? $existingExecution->performed_by,
                    ]);
                    $updatedCount++;
                } else {
                    // Create new execution only if doesn't exist
                    PreventiveMaintenanceExecution::create([
                        'schedule_id' => $updateData['schedule_id'],
                        'scheduled_date' => $updateData['scheduled_date'],
                        'status' => $validated['status'],
                        'performed_by' => $schedule->assigned_to, // Use PIC from schedule
                    ]);
                    $updatedCount++;
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'updated_count' => $updatedCount,
            'message' => "Berhasil mengupdate {$updatedCount} maintenance point"
        ]);
    }
    
    /**
     * Reschedule multiple schedules to a new date.
     * Only works if all schedules for the date are still pending (no execution or all executions are pending).
     * When rescheduled, future schedules will be recalculated from the new date.
     * If there are duplicate maintenance points on the new date, they will be merged.
     */
    public function reschedule(Request $request)
    {
        $validated = $request->validate([
            'schedule_ids' => 'required|array|min:1',
            'schedule_ids.*' => 'required|exists:preventive_maintenance_schedules,id',
            'old_date' => 'required|date',
            'new_date' => 'required|date',
        ]);
        
        $scheduleIds = $validated['schedule_ids'];
        $oldDate = $validated['old_date'];
        $newDate = $validated['new_date'];
        $oldDateObj = \Carbon\Carbon::parse($oldDate);
        $newDateObj = \Carbon\Carbon::parse($newDate);
        
        // Check if all schedules are still pending (no execution or all executions are pending)
        $schedules = PreventiveMaintenanceSchedule::whereIn('id', $scheduleIds)
            ->where('start_date', $oldDate)
            ->with(['executions'])
            ->get();
        
        if ($schedules->count() !== count($scheduleIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Beberapa schedule tidak ditemukan atau tidak sesuai dengan tanggal lama'
            ], 400);
        }
        
        // Check if all schedules are pending (no execution or all executions are pending)
        foreach ($schedules as $schedule) {
            $hasExecution = $schedule->executions()->exists();
            if ($hasExecution) {
                // Check if all executions are pending
                $hasNonPendingExecution = $schedule->executions()
                    ->where('status', '!=', 'pending')
                    ->exists();
                
                if ($hasNonPendingExecution) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak dapat memindahkan jadwal karena ada execution yang sudah dikerjakan (status bukan pending)'
                    ], 400);
                }
            }
        }
        
        // Group schedules by (machine_erp_id, maintenance_point_id) to handle duplicates
        $schedulesByPoint = [];
        foreach ($schedules as $schedule) {
            $key = $schedule->machine_erp_id . '_' . ($schedule->maintenance_point_id ?? 'null');
            if (!isset($schedulesByPoint[$key])) {
                $schedulesByPoint[$key] = [];
            }
            $schedulesByPoint[$key][] = $schedule;
        }
        
        $updatedCount = 0;
        $deletedFutureCount = 0;
        $mergedCount = 0;
        
        \DB::beginTransaction();
        try {
            foreach ($schedulesByPoint as $key => $scheduleGroup) {
                $firstSchedule = $scheduleGroup[0];
                $machineId = $firstSchedule->machine_erp_id;
                $maintenancePointId = $firstSchedule->maintenance_point_id;
                $frequencyType = $firstSchedule->frequency_type;
                $frequencyValue = $firstSchedule->frequency_value ?? 1;
                
                // Check if there's already a schedule with the same maintenance_point_id on the new date
                $existingScheduleOnNewDate = PreventiveMaintenanceSchedule::where('machine_erp_id', $machineId)
                    ->where('maintenance_point_id', $maintenancePointId)
                    ->where('start_date', $newDate)
                    ->where('status', 'active')
                    ->whereNotIn('id', $scheduleIds) // Exclude the schedules being moved
                    ->first();
                
                if ($existingScheduleOnNewDate) {
                    // Merge: Delete the schedules being moved (they will be merged with existing one)
                    foreach ($scheduleGroup as $schedule) {
                        // Delete any pending executions for these schedules
                        $schedule->executions()->where('status', 'pending')->delete();
                        $schedule->delete();
                    }
                    $mergedCount += count($scheduleGroup);
                } else {
                    // No duplicate, move the first schedule to new date
                    $scheduleToKeep = $scheduleGroup[0];
                    $scheduleToKeep->start_date = $newDate;
                    $scheduleToKeep->save();
                    $updatedCount++;
                    
                    // Delete other schedules in the group (duplicates on old date)
                    if (count($scheduleGroup) > 1) {
                        for ($i = 1; $i < count($scheduleGroup); $i++) {
                            $scheduleGroup[$i]->executions()->where('status', 'pending')->delete();
                            $scheduleGroup[$i]->delete();
                        }
                        $mergedCount += (count($scheduleGroup) - 1);
                    }
                    
                    // Delete future schedules for this maintenance point that haven't been executed yet
                    // (schedules with start_date > old_date and no completed execution)
                    $futureSchedules = PreventiveMaintenanceSchedule::where('machine_erp_id', $machineId)
                        ->where('maintenance_point_id', $maintenancePointId)
                        ->where('start_date', '>', $oldDate)
                        ->where('status', 'active')
                        ->whereNotIn('id', $scheduleIds)
                        ->get();
                    
                    foreach ($futureSchedules as $futureSchedule) {
                        // Only delete if no completed execution exists
                        $hasCompletedExecution = $futureSchedule->executions()
                            ->where('status', 'completed')
                            ->exists();
                        
                        if (!$hasCompletedExecution) {
                            // Delete pending executions
                            $futureSchedule->executions()->where('status', 'pending')->delete();
                            $futureSchedule->delete();
                            $deletedFutureCount++;
                        }
                    }
                    
                    // Recalculate future schedules from new date
                    $currentDate = clone $newDateObj;
                    $endOfYear = \Carbon\Carbon::create($currentDate->year, 12, 31);
                    
                    while ($currentDate->lte($endOfYear)) {
                        // Skip the new date itself (already moved)
                        if ($currentDate->format('Y-m-d') == $newDate) {
                            $currentDate = $this->calculateNextDate($currentDate, $frequencyType, $frequencyValue);
                            continue;
                        }
                        
                        // Check if schedule already exists for this date
                        $existingSchedule = PreventiveMaintenanceSchedule::where('machine_erp_id', $machineId)
                            ->where('maintenance_point_id', $maintenancePointId)
                            ->where('start_date', $currentDate->format('Y-m-d'))
                            ->where('status', 'active')
                            ->first();
                        
                        if (!$existingSchedule) {
                            // Create new schedule
                            PreventiveMaintenanceSchedule::create([
                                'machine_erp_id' => $machineId,
                                'maintenance_point_id' => $maintenancePointId,
                                'title' => $scheduleToKeep->title,
                                'description' => $scheduleToKeep->description,
                                'frequency_type' => $frequencyType,
                                'frequency_value' => $frequencyValue,
                                'start_date' => $currentDate->format('Y-m-d'),
                                'end_date' => $endOfYear->format('Y-m-d'),
                                'preferred_time' => $scheduleToKeep->preferred_time,
                                'estimated_duration' => $scheduleToKeep->estimated_duration,
                                'status' => $scheduleToKeep->status,
                                'assigned_to' => $scheduleToKeep->assigned_to,
                                'notes' => $scheduleToKeep->notes,
                            ]);
                        }
                        
                        // Calculate next schedule date
                        $currentDate = $this->calculateNextDate($currentDate, $frequencyType, $frequencyValue);
                        
                        // Safety check
                        if ($currentDate->year > $endOfYear->year) {
                            break;
                        }
                    }
                }
            }
            
            \DB::commit();
            
            $message = "Berhasil memindahkan jadwal ke tanggal " . $newDateObj->format('d/m/Y');
            if ($mergedCount > 0) {
                $message .= ". {$mergedCount} jadwal digabung karena duplikat.";
            }
            if ($deletedFutureCount > 0) {
                $message .= " {$deletedFutureCount} jadwal selanjutnya dihapus dan dihitung ulang dari tanggal baru.";
            }
            
            return response()->json([
                'success' => true,
                'updated_count' => $updatedCount,
                'merged_count' => $mergedCount,
                'deleted_future_count' => $deletedFutureCount,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error in reschedule', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memindahkan jadwal: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update PIC (assigned_to) for schedules on a specific date
     * Only admin can do this, and only for schedules that are not completed
     */
    public function updatePic(Request $request)
    {
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya admin yang dapat mengubah PIC'
            ], 403);
        }
        
        $validated = $request->validate([
            'machine_erp_id' => 'required|exists:machine_erp,id',
            'scheduled_date' => 'required|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);
        
        try {
            \DB::beginTransaction();
            
            // Get all schedules for this machine and date
            $schedules = PreventiveMaintenanceSchedule::where('machine_erp_id', $validated['machine_erp_id'])
                ->where('start_date', $validated['scheduled_date'])
                ->where('status', 'active')
                ->get();
            
            if ($schedules->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada schedule ditemukan untuk tanggal ini'
                ], 404);
            }
            
            $updatedCount = 0;
            $skippedCount = 0;
            
            foreach ($schedules as $schedule) {
                // Check if schedule has completed execution
                $hasCompletedExecution = $schedule->executions()
                    ->where('status', 'completed')
                    ->exists();
                
                // Only update if not completed
                if (!$hasCompletedExecution) {
                    $schedule->update([
                        'assigned_to' => $validated['assigned_to']
                    ]);
                    $updatedCount++;
                } else {
                    $skippedCount++;
                }
            }
            
            \DB::commit();
            
            $message = "Berhasil mengupdate PIC untuk {$updatedCount} schedule";
            if ($skippedCount > 0) {
                $message .= ". {$skippedCount} schedule dilewati karena sudah completed";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updatedCount,
                'skipped_count' => $skippedCount
            ]);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error in updatePic', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate PIC: ' . $e->getMessage()
            ], 500);
        }
    }
}

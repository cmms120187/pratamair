<?php

namespace App\Http\Controllers\PredictiveMaintenance;

use App\Http\Controllers\Controller;
use App\Models\PredictiveMaintenanceSchedule;
use App\Models\PredictiveMaintenanceExecution;
use App\Models\MachineErp;
use App\Models\RoomErp;
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
        // Get filter parameters - only use if explicitly provided
        $periodType = request()->get('period_type'); // 'month' or 'year'
        $periodMonth = request()->get('period_month');
        $periodYear = request()->get('period_year');
        $plantId = request()->get('plant');
        $lineId = request()->get('line');
        $machineTypeId = request()->get('machine_type');
        $searchIdMachine = request()->get('search_id_machine');
        
        // Set defaults for display in filter form only (NOT for filtering query)
        $displayPeriodType = $periodType ?: 'year';
        $displayPeriodMonth = $periodMonth ?: now()->month;
        $displayPeriodYear = $periodYear ?: now()->year;
        
        // Build query - now using machineErp directly
        // By default, show ALL schedules regardless of date
        $query = PredictiveMaintenanceSchedule::with(['machineErp.roomErp', 'machineErp.machineType', 'maintenancePoint', 'standard', 'assignedUser', 'executions']);
        
        // Apply period filter - ONLY if user explicitly sets period filter
        if ($periodType && $periodYear) {
            if ($periodType == 'month' && $periodMonth) {
                $query->whereYear('start_date', $periodYear)
                      ->whereMonth('start_date', $periodMonth);
            } elseif ($periodType == 'year') {
                $query->whereYear('start_date', $periodYear);
            }
        }
        // If no period filter is set, show ALL schedules (no date filtering)
        
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
        
        // Get distinct values for filters from MachineErp and RoomErp
        $plants = \App\Models\Plant::orderBy('name')->get();
        $lines = \App\Models\Line::orderBy('name')->get();
        $machineTypes = MachineType::orderBy('name')->get();
        
        // Group schedules by machine_erp_id (not by machine + date)
        // Each machine will have multiple dates, dates will be shown as clickable buttons
        $machinesData = [];
        foreach ($schedules as $schedule) {
            $machineErpId = $schedule->machine_erp_id;
            
            // If machine not found in loaded machines, try to load it
            if (!isset($machines[$machineErpId])) {
                $machine = MachineErp::with(['roomErp', 'machineType'])->find($machineErpId);
                if ($machine) {
                    $machines[$machineErpId] = $machine;
                } else {
                    // Skip if machine really doesn't exist
                    \Log::warning("MachineErp with id {$machineErpId} not found for schedule {$schedule->id}");
                    continue;
                }
            }
            
            // Initialize machine data if not exists
            if (!isset($machinesData[$machineErpId])) {
                $machine = $machines[$machineErpId];
                $machinesData[$machineErpId] = [
                    'machine' => $machine,
                    'schedules' => [],
                    'schedules_by_date' => [], // Group schedules by date
                ];
            }
            
            // Add schedule to machine
            $machinesData[$machineErpId]['schedules'][] = $schedule;
            
            // Group by date
            $scheduleDate = $schedule->start_date;
            if (is_string($scheduleDate)) {
                $dateFormatted = $scheduleDate;
            } elseif ($scheduleDate instanceof Carbon) {
                $dateFormatted = $scheduleDate->format('Y-m-d');
            } else {
                $dateFormatted = $scheduleDate->format('Y-m-d');
            }
            
            if (!isset($machinesData[$machineErpId]['schedules_by_date'][$dateFormatted])) {
                $machinesData[$machineErpId]['schedules_by_date'][$dateFormatted] = [];
            }
            $machinesData[$machineErpId]['schedules_by_date'][$dateFormatted][] = $schedule;
        }
        
        // Calculate completion statistics for each machine
        foreach ($machinesData as $machineErpId => &$data) {
            $totalSchedules = count($data['schedules']);
            $completedSchedules = 0;
            $pendingSchedules = 0;
            $overdueSchedules = 0;
            
            foreach ($data['schedules'] as $schedule) {
                $hasExecution = $schedule->executions()->exists();
                $isOverdue = !$hasExecution && $schedule->start_date < now()->toDateString() && $schedule->status == 'active';
                
                if ($hasExecution) {
                    $execution = $schedule->executions()->latest()->first();
                    if ($execution && $execution->status == 'completed') {
                        $completedSchedules++;
                    } else {
                        $pendingSchedules++;
                    }
                } else {
                    if ($isOverdue) {
                        $overdueSchedules++;
                    } else {
                        $pendingSchedules++;
                    }
                }
            }
            
            // Calculate completion percentage based on unique dates (jadwal)
            $uniqueDates = array_keys($data['schedules_by_date']);
            $totalJadwal = count($uniqueDates);
            $completedJadwal = 0;
            
            foreach ($uniqueDates as $dateKey) {
                $dateSchedules = $data['schedules_by_date'][$dateKey];
                $allCompleted = true;
                
                foreach ($dateSchedules as $sch) {
                    $hasExec = $sch->executions()->exists();
                    if (!$hasExec) {
                        $allCompleted = false;
                        break;
                    }
                    $exec = $sch->executions()->latest()->first();
                    if (!$exec || $exec->status != 'completed') {
                        $allCompleted = false;
                        break;
                    }
                }
                
                if ($allCompleted) {
                    $completedJadwal++;
                }
            }
            
            $completionPercentage = $totalJadwal > 0 ? round(($completedJadwal / $totalJadwal) * 100, 1) : 0;
            
            // Get PIC (most common assigned user) - only from current month/year (filtered period)
            $assignedUsers = [];
            // Use display period (what user sees in filter) for PIC calculation
            $currentYear = $displayPeriodYear ?? now()->year;
            $currentMonth = $displayPeriodMonth ?? now()->month;
            
            foreach ($data['schedules'] as $schedule) {
                // Only count PIC from schedules in the filtered period
                $scheduleYear = $schedule->start_date->year;
                $scheduleMonth = $schedule->start_date->month;
                
                if ($scheduleYear == $currentYear && $scheduleMonth == $currentMonth) {
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
            }
            
            $data['total_schedules'] = $totalSchedules;
            $data['completed_schedules'] = $completedSchedules;
            $data['pending_schedules'] = $pendingSchedules;
            $data['overdue_schedules'] = $overdueSchedules;
            $data['total_jadwal'] = $totalJadwal;
            $data['completed_jadwal'] = $completedJadwal;
            $data['completion_percentage'] = $completionPercentage;
            $data['pic_name'] = !empty($assignedUsers) ? collect($assignedUsers)->sortByDesc('count')->first()['name'] : '-';
        }
        unset($data);
        
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
        
        // Prepare schedules data for JavaScript (grouped by machine and date)
        $schedulesDataForJs = [];
        foreach ($machinesData as $machineErpId => $data) {
            $machine = $data['machine'];
            $schedulesDataForJs[$machine->id] = [];
            
            foreach ($data['schedules_by_date'] as $dateKey => $dateSchedules) {
                $key = $machine->id . '_' . $dateKey;
                $schedulesDataForJs[$machine->id][$key] = collect($dateSchedules)->map(function($schedule) use ($dateKey) {
                    $hasExecution = $schedule->executions()->exists();
                    $execution = $hasExecution ? $schedule->executions()->latest()->first() : null;
                    $isOverdue = !$hasExecution && $schedule->start_date < now()->toDateString() && $schedule->status == 'active';
                    $isCompleted = $execution && $execution->status == 'completed';
                    
                    return [
                        'schedule_id' => $schedule->id,
                        'maintenance_point_name' => $schedule->maintenancePoint->name ?? $schedule->title,
                        'standard_name' => $schedule->standard->name ?? '-',
                        'standard_unit' => $schedule->standard->unit ?? '-',
                        'standard_min' => $schedule->standard->min_value,
                        'standard_max' => $schedule->standard->max_value,
                        'standard_target' => $schedule->standard->target_value,
                        'execution_status' => $execution ? $execution->status : 'pending',
                        'execution_id' => $execution ? $execution->id : null,
                        'has_execution' => $hasExecution,
                        'is_completed' => $isCompleted,
                        'is_overdue' => $isOverdue,
                        'status' => $schedule->status,
                        'description' => $schedule->description ?? $schedule->maintenancePoint->instruction ?? '',
                        'frequency_type' => $schedule->frequency_type,
                        'frequency_value' => $schedule->frequency_value,
                        'assigned_to' => $schedule->assigned_to, // Include assigned_to for PIC display
                    ];
                })->toArray();
            }
        }
        
        // Get users for PIC selection (only team_leader)
        $users = User::where('role', 'team_leader')->orderBy('name')->get();
        
        return view('predictive-maintenance.scheduling.index', [
            'paginator' => $paginator,
            'schedulesDataForJs' => $schedulesDataForJs,
            'plants' => $plants,
            'lines' => $lines,
            'machineTypes' => $machineTypes,
            'users' => $users,
            'periodType' => $displayPeriodType,
            'periodMonth' => $displayPeriodMonth,
            'periodYear' => $displayPeriodYear,
            'plantId' => $plantId,
            'lineId' => $lineId,
            'machineTypeId' => $machineTypeId,
            'searchIdMachine' => $searchIdMachine,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Only get users with role 'team_leader'
        $users = User::where('role', 'team_leader')->orderBy('name')->get();
        
        // Prepare machines data from MachineErp for JavaScript - now using MachineErp id directly
        $machines = MachineErp::with(['roomErp', 'machineType'])->get()->map(function($machineErp) {
            $roomName = '-';
            if ($machineErp->roomErp && $machineErp->roomErp->name) {
                $roomName = $machineErp->roomErp->name;
            } elseif ($machineErp->room_name) {
                $roomName = $machineErp->room_name;
            }
            
            return [
                'id' => $machineErp->id, // Use MachineErp id directly
                'idMachine' => $machineErp->idMachine ?? '-',
                'machineType' => $machineErp->machineType ? $machineErp->machineType->name : ($machineErp->type_name ?? '-'),
                'machineTypeId' => $machineErp->machine_type_id, // Include machine_type_id for getting standard
                'modelName' => $machineErp->model_name ?? '-',
                'brandName' => $machineErp->brand_name ?? '-',
                'plant' => $machineErp->plant_name ?? '-',
                'process' => $machineErp->process_name ?? '-',
                'line' => $machineErp->line_name ?? '-',
                'room' => $roomName,
            ];
        })->values();
        
        return view('predictive-maintenance.scheduling.create', compact('users', 'machines'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'machine_id' => 'required|exists:machine_erp,id',
            'start_date' => 'required|date',
            'status' => 'required|in:active,inactive,completed,cancelled',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        // Now machine_id is actually machine_erp_id
        $machineErp = MachineErp::findOrFail($validated['machine_id']);
        
        // Get machine_type_id from MachineErp
        $typeId = $machineErp->machine_type_id;
        
        if (!$typeId) {
            return back()->withErrors(['machine_id' => 'Machine type belum ditentukan untuk mesin ini.'])->withInput();
        }
        
        // Auto-set category to 'predictive' since we're in Predictive Maintenance menu
        $category = 'predictive';
        
        // Get all maintenance points for this machine type and category (predictive)
        // Eager load standard relationship to get standard_id from each maintenance point
        $maintenancePoints = MaintenancePoint::with('standard')
            ->where('machine_type_id', $typeId)
            ->where('category', $category)
            ->orderBy('sequence', 'asc')
            ->get();
        
        if ($maintenancePoints->isEmpty()) {
            return back()->withErrors(['machine_id' => 'Tidak ada maintenance point untuk kategori predictive pada tipe mesin ini. Silakan buat maintenance point terlebih dahulu.'])->withInput();
        }
        
        // Check if all maintenance points have standard_id
        $pointsWithoutStandard = $maintenancePoints->filter(function($point) {
            return !$point->standard_id || !$point->standard;
        });
        
        if ($pointsWithoutStandard->isNotEmpty()) {
            $pointNames = $pointsWithoutStandard->pluck('name')->implode(', ');
            return back()->withErrors(['machine_id' => "Beberapa maintenance point belum memiliki standar: {$pointNames}. Silakan tentukan standar untuk maintenance point tersebut terlebih dahulu."])->withInput();
        }
        
        $schedulesCreated = 0;
        $endOfYear = $this->calculateEndDate($validated['start_date'], null, null);
        
        // Create schedule for each maintenance point with its own standard
        foreach ($maintenancePoints as $point) {
            $frequencyType = $point->frequency_type ?? 'monthly';
            $frequencyValue = $point->frequency_value ?? 1;
            $currentDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($endOfYear);
            
            // Use standard_id from maintenance point, not from machine type
            $pointStandardId = $point->standard_id;
            
            if (!$pointStandardId) {
                continue; // Skip if no standard
            }
            
            // Generate schedules until end of year
            while ($currentDate->lte($endDate)) {
                PredictiveMaintenanceSchedule::create([
                    'machine_erp_id' => $validated['machine_id'],
                    'maintenance_point_id' => $point->id,
                    'standard_id' => $pointStandardId, // Use standard_id from maintenance point
                    'title' => $point->name,
                    'description' => $point->instruction,
                    'frequency_type' => $frequencyType,
                    'frequency_value' => $frequencyValue,
                    'start_date' => $currentDate->format('Y-m-d'),
                    'end_date' => $endOfYear,
                    'status' => $validated['status'],
                    'assigned_to' => $validated['assigned_to'] ?? null,
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
    
    /**
     * Update PIC (assigned_to) for selected schedules only
     */
    public function updatePic(Request $request)
    {
        $validated = $request->validate([
            'schedule_ids' => 'required|array|min:1',
            'schedule_ids.*' => 'required|exists:predictive_maintenance_schedules,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        // Update only selected schedules
        $updated = PredictiveMaintenanceSchedule::whereIn('id', $validated['schedule_ids'])
            ->update([
                'assigned_to' => $validated['assigned_to']
            ]);

        if ($updated > 0) {
            return response()->json([
                'success' => true,
                'message' => "PIC berhasil diupdate untuk {$updated} schedule(s) yang dipilih."
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tidak ada schedule yang ditemukan.'
        ], 404);
    }
    
    /**
     * Reschedule selected schedules to a new date.
     * Only works if all schedules are still pending (no execution or all executions are pending).
     */
    public function reschedule(Request $request)
    {
        $validated = $request->validate([
            'schedule_ids' => 'required|array|min:1',
            'schedule_ids.*' => 'required|exists:predictive_maintenance_schedules,id',
            'new_date' => 'required|date',
        ]);

        $scheduleIds = $validated['schedule_ids'];
        $newDate = $validated['new_date'];
        $newDateObj = Carbon::parse($newDate);

        // Get schedules
        $schedules = PredictiveMaintenanceSchedule::whereIn('id', $scheduleIds)
            ->with(['executions'])
            ->get();

        if ($schedules->count() !== count($scheduleIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Beberapa schedule tidak ditemukan'
            ], 400);
        }

        // Check if all schedules are pending (no execution or all executions are pending)
        // Only schedules that haven't been updated (no execution or all executions are pending) can be rescheduled
        $cannotReschedule = [];
        foreach ($schedules as $schedule) {
            $hasExecution = $schedule->executions()->exists();
            if ($hasExecution) {
                // Check if all executions are pending
                $hasNonPendingExecution = $schedule->executions()
                    ->where('status', '!=', 'pending')
                    ->exists();
                
                if ($hasNonPendingExecution) {
                    $cannotReschedule[] = $schedule->id;
                }
            }
        }
        
        if (!empty($cannotReschedule)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat memindahkan jadwal karena ada point yang sudah dilakukan Predictive (sudah diupdate). Hanya jadwal yang belum diupdate yang bisa dipindah.'
            ], 400);
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
        $mergedCount = 0;

        \DB::beginTransaction();
        try {
            foreach ($schedulesByPoint as $key => $scheduleGroup) {
                $firstSchedule = $scheduleGroup[0];
                $machineId = $firstSchedule->machine_erp_id;
                $maintenancePointId = $firstSchedule->maintenance_point_id;

                // Check if there's already a schedule with the same maintenance_point_id on the new date
                $existingScheduleOnNewDate = PredictiveMaintenanceSchedule::where('machine_erp_id', $machineId)
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
                }
            }

            \DB::commit();

            $message = "Berhasil memindahkan {$updatedCount} jadwal";
            if ($mergedCount > 0) {
                $message .= " dan menggabungkan {$mergedCount} jadwal duplikat";
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error rescheduling predictive maintenance schedules', [
                'error' => $e->getMessage(),
                'schedule_ids' => $scheduleIds,
                'new_date' => $newDate
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi error saat memindahkan jadwal: ' . $e->getMessage()
            ], 500);
        }
    }
}

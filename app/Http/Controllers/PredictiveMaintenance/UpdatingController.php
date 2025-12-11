<?php

namespace App\Http\Controllers\PredictiveMaintenance;

use App\Http\Controllers\Controller;
use App\Models\PredictiveMaintenanceExecution;
use App\Models\PredictiveMaintenanceSchedule;
use App\Models\User;
use App\Helpers\DataFilterHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UpdatingController extends Controller
{
    /**
     * Display a listing of executions that need updating.
     */
    public function index()
    {
        // Filter by current year only - hanya menampilkan jadwal tahun berjalan
        $currentYear = now()->year;
        
        $user = auth()->user();
        $userRole = $user->role ?? 'mekanik';
        $userId = $user->id ?? null;
        
        // Get all active schedules for current year - untuk menampilkan jadwal yang belum ada execution-nya
        $scheduleQuery = PredictiveMaintenanceSchedule::where('status', 'active')
            ->whereYear('start_date', $currentYear);
        
        // Filter schedules by user role
        // Admin: no filter (akses semua)
        // Team Leader: hanya jadwal yang assigned_to = user_id mereka
        // Mekanik: filter akan dilakukan di execution query
        if ($userRole === 'team_leader' && $userId) {
            $scheduleQuery->where('assigned_to', $userId);
        }
        // Admin dan role lain tidak perlu filter schedule
        
        $schedules = $scheduleQuery->with(['machineErp.machineType', 'machineErp.roomErp', 'standard', 'assignedUser', 'executions'])
            ->orderBy('start_date', 'asc')
            ->get();
        
        // Get all executions for current year
        $executionQuery = PredictiveMaintenanceExecution::with(['schedule.machineErp.machineType', 'schedule.machineErp.roomErp', 'schedule.standard', 'performedBy', 'schedule']);
        $executionQuery->whereYear('scheduled_date', $currentYear);
        
        // Filter by user role
        // Admin: no filter (akses semua)
        // Team Leader: hanya execution yang performed_by = user_id mereka ATAU schedule assigned_to = user_id mereka
        // Mekanik: hanya execution yang performed_by = user_id mereka
        if (DataFilterHelper::shouldFilterRoute(request()->route()->getName())) {
            if ($userRole === 'team_leader' && $userId) {
                // Team leader: filter berdasarkan performed_by atau schedule assigned_to
                $executionQuery->where(function($q) use ($userId) {
                    $q->where('performed_by', $userId)
                      ->orWhereHas('schedule', function($scheduleQuery) use ($userId) {
                          $scheduleQuery->where('assigned_to', $userId);
                      });
                });
            } elseif ($userRole === 'mekanik' && $userId) {
                $executionQuery->where('performed_by', $userId);
            }
            // Admin dan role lain tidak perlu filter
        }
        
        $allExecutions = $executionQuery->orderBy('scheduled_date', 'asc')->orderBy('created_at', 'desc')->get();
        
        // Group schedules by (machine_erp_id, start_date) to get unique jadwal
        $scheduleJadwalData = [];
        foreach ($schedules as $schedule) {
            $machineId = $schedule->machine_erp_id;
            $startDate = $schedule->start_date;
            if (is_string($startDate)) {
                $dateFormatted = $startDate;
            } else {
                $dateFormatted = \Carbon\Carbon::parse($startDate)->format('Y-m-d');
            }
            $key = $machineId . '_' . $dateFormatted;
            
            if (!isset($scheduleJadwalData[$key])) {
                $scheduleJadwalData[$key] = [
                    'machine_id' => $machineId,
                    'machine' => $schedule->machineErp,
                    'scheduled_date' => $dateFormatted,
                    'schedules' => [],
                    'has_execution' => false,
                ];
            }
            $scheduleJadwalData[$key]['schedules'][] = $schedule;
        }
        
        // Group all executions by (machine_erp_id, scheduled_date) 
        // For each jadwal, use only the LATEST execution (1 jadwal = 1 execution)
        $executionJadwalData = [];
        foreach ($allExecutions as $execution) {
            $machineId = $execution->schedule->machine_erp_id;
            $scheduledDate = $execution->scheduled_date;
            if (is_string($scheduledDate)) {
                $dateFormatted = $scheduledDate;
            } else {
                $dateFormatted = \Carbon\Carbon::parse($scheduledDate)->format('Y-m-d');
            }
            $key = $machineId . '_' . $dateFormatted;
            
            // For each jadwal, only keep the latest execution (most recent created_at)
            if (!isset($executionJadwalData[$key])) {
                $executionJadwalData[$key] = [
                    'machine_id' => $machineId,
                    'machine' => $execution->schedule->machineErp,
                    'scheduled_date' => $dateFormatted,
                    'latest_execution' => $execution, // Store only the latest execution
                ];
            } else {
                // Update if this execution is newer (later created_at)
                if ($execution->created_at > $executionJadwalData[$key]['latest_execution']->created_at) {
                    $executionJadwalData[$key]['latest_execution'] = $execution;
                }
            }
        }
        
        // Merge schedule jadwal with execution jadwal
        // Priority: execution data if exists, otherwise use schedule data
        $allJadwalData = [];
        foreach ($scheduleJadwalData as $key => $scheduleJadwal) {
            if (isset($executionJadwalData[$key])) {
                // Jadwal sudah ada execution
                $execution = $executionJadwalData[$key]['latest_execution'];
                $allJadwalData[$key] = [
                    'machine_id' => $scheduleJadwal['machine_id'],
                    'machine' => $scheduleJadwal['machine'],
                    'scheduled_date' => $scheduleJadwal['scheduled_date'],
                    'latest_execution' => $execution,
                    'has_pending' => false,
                    'has_in_progress' => false,
                    'has_completed' => false,
                ];
            
            // Update status flags based on latest execution
                if ($execution->status == 'pending') {
                $allJadwalData[$key]['has_pending'] = true;
                } elseif ($execution->status == 'in_progress') {
                $allJadwalData[$key]['has_in_progress'] = true;
                } elseif ($execution->status == 'completed') {
                $allJadwalData[$key]['has_completed'] = true;
                }
            } else {
                // Jadwal belum ada execution - tampilkan sebagai pending
                // Ambil schedule pertama untuk membuat execution virtual
                $firstSchedule = $scheduleJadwal['schedules'][0];
                $allJadwalData[$key] = [
                    'machine_id' => $scheduleJadwal['machine_id'],
                    'machine' => $scheduleJadwal['machine'],
                    'scheduled_date' => $scheduleJadwal['scheduled_date'],
                    'latest_execution' => null, // Belum ada execution
                    'first_schedule' => $firstSchedule, // Untuk membuat execution nanti
                    'has_pending' => true, // Default pending karena belum ada execution
                    'has_in_progress' => false,
                    'has_completed' => false,
                ];
            }
        }
        
        // Filter: only include jadwal that have pending or in_progress status (latest execution)
        // Exclude jadwal that already have completed status (completed is the latest update)
        $jadwalData = [];
        foreach ($allJadwalData as $key => $jadwal) {
            $latestExecution = $jadwal['latest_execution'];
            
            // Skip jadwal that already has completed status (latest execution is completed)
            if ($latestExecution && $latestExecution->status == 'completed') {
                continue;
            }
            
            // Include jadwal with pending or in_progress status, or jadwal yang belum ada execution
            if (!$latestExecution || $latestExecution->status == 'pending' || $latestExecution->status == 'in_progress') {
                $jadwalData[$key] = [
                    'machine_id' => $jadwal['machine_id'],
                    'machine' => $jadwal['machine'],
                    'scheduled_date' => $jadwal['scheduled_date'],
                    'executions' => $latestExecution ? [$latestExecution] : [], // Empty jika belum ada execution
                    'status' => $latestExecution ? $latestExecution->status : 'pending', // Default pending jika belum ada execution
                    'first_schedule' => $jadwal['first_schedule'] ?? null, // Untuk membuat execution nanti
                ];
            }
        }
        
        // Convert to collection and paginate manually
        $jadwalCollection = collect($jadwalData)->values();
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 12;
        $currentItems = $jadwalCollection->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $jadwalPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $jadwalCollection->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
        );
        
        // Get completed jadwal for information only - use latest execution for each jadwal
        $completedJadwal = [];
        foreach ($allJadwalData as $key => $jadwal) {
            $latestExecution = $jadwal['latest_execution'];
            
            // Only include jadwal where latest execution exists and is completed
            // Pastikan status benar-benar 'completed', bukan 'pending' atau 'in_progress'
            if ($latestExecution && $latestExecution->status === 'completed') {
                $completedJadwal[$key] = [
                    'machine_id' => $jadwal['machine_id'],
                    'machine' => $jadwal['machine'],
                    'scheduled_date' => $jadwal['scheduled_date'],
                    'executions' => [$latestExecution], // Only the latest execution
                    'latest_end_time' => $latestExecution->actual_end_time,
                    'status' => 'completed', // Explicitly set status to completed
                ];
            }
        }
        
        // Sort by latest_end_time and limit to 20
        usort($completedJadwal, function($a, $b) {
            $timeA = $a['latest_end_time'] ? \Carbon\Carbon::parse($a['latest_end_time']) : \Carbon\Carbon::createFromTimestamp(0);
            $timeB = $b['latest_end_time'] ? \Carbon\Carbon::parse($b['latest_end_time']) : \Carbon\Carbon::createFromTimestamp(0);
            return $timeB->gt($timeA) ? 1 : -1;
        });
        $completedJadwal = array_slice($completedJadwal, 0, 20);
        
        // Convert to collection and paginate manually
        $completedJadwalCollection = collect($completedJadwal);
        $currentPageCompleted = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage('completed_page');
        $perPageCompleted = 12;
        $currentItemsCompleted = $completedJadwalCollection->slice(($currentPageCompleted - 1) * $perPageCompleted, $perPageCompleted)->all();
        $completedJadwalPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItemsCompleted,
            $completedJadwalCollection->count(),
            $perPageCompleted,
            $currentPageCompleted,
            [
                'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'completed_page',
            ]
        );
        
        // Statistics - only count jadwal based on latest execution status
        $pendingCount = 0;
        $inProgressCount = 0;
        
        // Count based on latest execution status for each jadwal
        foreach ($allJadwalData as $key => $jadwal) {
            $latestExecution = $jadwal['latest_execution'];
            
            // Count jadwal yang belum ada execution sebagai pending
            if (!$latestExecution) {
                $pendingCount++;
            } elseif ($latestExecution->status == 'pending') {
                $pendingCount++;
            } elseif ($latestExecution->status == 'in_progress') {
                $inProgressCount++;
            }
        }
        
        // Count unique completed jadwal
        $completedJadwalCount = count($completedJadwal);
        $totalJadwalToUpdate = count($jadwalData);
        
        $stats = [
            'pending' => $pendingCount,
            'in_progress' => $inProgressCount,
            'completed' => $completedJadwalCount,
            'total' => $totalJadwalToUpdate,
        ];
        
        return view('predictive-maintenance.updating.index', compact('jadwalPaginator', 'completedJadwalPaginator', 'stats'));
    }
    
    /**
     * Get maintenance points by machine and date for AJAX.
     * Mengambil data dari schedules (bukan executions) untuk menghindari duplikasi.
     */
    public function getMaintenancePointsByMachineAndDate(Request $request)
    {
        $machineId = $request->input('machine_id');
        $scheduledDate = $request->input('scheduled_date');
        
        if (!$machineId || !$scheduledDate) {
            return response()->json(['maintenance_points' => []]);
        }
        
        // Ensure scheduledDate is in correct format
        if (is_string($scheduledDate)) {
            $scheduledDate = \Carbon\Carbon::parse($scheduledDate)->format('Y-m-d');
        } else {
            $scheduledDate = \Carbon\Carbon::parse($scheduledDate)->format('Y-m-d');
        }
        
        // Get all schedules for this machine with start_date = scheduled_date
        // Sama seperti di method edit(), untuk konsistensi
        $schedules = PredictiveMaintenanceSchedule::where('machine_erp_id', $machineId)
            ->where('status', 'active')
            ->whereDate('start_date', $scheduledDate)
            ->with(['maintenancePoint', 'standard', 'assignedUser', 'executions' => function($query) use ($scheduledDate) {
                // Get all executions for this scheduled_date, ordered by created_at desc
                $query->where('scheduled_date', $scheduledDate)
                      ->orderBy('created_at', 'desc');
            }])
            ->orderBy('maintenance_point_id')
            ->get();
        
        // Remove duplicates based on maintenance_point_id to ensure unique points
        $uniqueSchedules = collect();
        $seenPointIds = [];
        
        foreach ($schedules as $schedule) {
            $pointId = $schedule->maintenance_point_id;
            
            // If no maintenance_point_id, use schedule id as unique identifier
            if (!$pointId) {
                $uniqueSchedules->push($schedule);
                continue;
            }
            
            // Only add if we haven't seen this point_id before
            if (!in_array($pointId, $seenPointIds)) {
                $seenPointIds[] = $pointId;
                $uniqueSchedules->push($schedule);
            }
        }
        
        $schedules = $uniqueSchedules;
        
        // Map schedules to maintenance points data
        $maintenancePoints = [];
        foreach ($schedules as $schedule) {
            // Get execution for this scheduled_date - get the latest one
            $execution = $schedule->executions
                ->where('scheduled_date', $scheduledDate)
                ->sortByDesc('created_at')
                ->first();
            
            // If no execution found for this scheduled_date, try to get any execution for this schedule
            // (might be from a different scheduled_date but same schedule)
            if (!$execution) {
                $execution = $schedule->executions
                    ->sortByDesc('created_at')
                    ->first();
            }
            
            // Determine status - use execution status if exists, otherwise 'pending'
            $status = 'pending';
            if ($execution) {
                $status = $execution->status;
            }
            
            $maintenancePoints[] = [
                'execution_id' => $execution ? $execution->id : null,
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
                'measured_value' => $execution ? $execution->measured_value : null,
                'measurement_status' => $execution ? $execution->measurement_status : null,
                'status' => $status, // Use determined status
                'performed_by' => $execution && $execution->performedBy ? $execution->performedBy->name : '-',
                'actual_start_time' => $execution && $execution->actual_start_time ? \Carbon\Carbon::parse($execution->actual_start_time)->format('d/m/Y H:i') : '-',
                'actual_end_time' => $execution && $execution->actual_end_time ? \Carbon\Carbon::parse($execution->actual_end_time)->format('d/m/Y H:i') : '-',
            ];
        }
        
        return response()->json(['maintenance_points' => $maintenancePoints]);
    }
    
    /**
     * Create execution from schedule and show edit form.
     */
    public function createFromSchedule(Request $request)
    {
        $scheduleId = $request->input('schedule_id');
        $scheduledDate = $request->input('scheduled_date');
        
        if (!$scheduleId || !$scheduledDate) {
            return redirect()->route('predictive-maintenance.updating.index')
                ->with('error', 'Schedule ID dan Scheduled Date harus diisi.');
        }
        
        $schedule = PredictiveMaintenanceSchedule::with(['machineErp.machineType', 'machineErp.roomErp', 'standard', 'maintenancePoint'])
            ->findOrFail($scheduleId);
        
        // Check if execution already exists
        $existingExecution = PredictiveMaintenanceExecution::where('schedule_id', $scheduleId)
            ->where('scheduled_date', $scheduledDate)
            ->first();
        
        if ($existingExecution) {
            return redirect()->route('predictive-maintenance.updating.edit', $existingExecution->id);
        }
        
        // Create new execution with pending status
        $execution = PredictiveMaintenanceExecution::create([
            'schedule_id' => $scheduleId,
            'scheduled_date' => $scheduledDate,
            'status' => 'pending',
        ]);
        
        return redirect()->route('predictive-maintenance.updating.edit', $execution->id);
    }
    
    /**
     * Show the form for editing the specified resource.
     * Menampilkan semua maintenance points untuk machine_id dan scheduled_date.
     * Jika parameter 'single_point' ada, hanya menampilkan point yang sesuai dengan execution_id.
     */
    public function edit(string $id, Request $request)
    {
        $execution = PredictiveMaintenanceExecution::with(['schedule.machineErp.machineType', 'schedule.machineErp.roomErp', 'schedule.standard', 'schedule.maintenancePoint', 'performedBy'])
            ->findOrFail($id);
        
        $machineId = $execution->schedule->machine_erp_id;
        $scheduledDate = $execution->scheduled_date;
        $singlePoint = $request->get('single_point', false); // Check if single point mode
        
        // Ensure scheduledDate is in correct format
        if (is_string($scheduledDate)) {
            $scheduledDate = \Carbon\Carbon::parse($scheduledDate)->format('Y-m-d');
        } else {
            $scheduledDate = \Carbon\Carbon::parse($scheduledDate)->format('Y-m-d');
        }
        
        // Get all schedules for this machine with start_date = scheduled_date
        // Hanya ambil schedules yang sesuai dengan tanggal yang dipilih, tidak termasuk schedule terlewat
        // untuk menghindari duplikasi point
        $schedules = PredictiveMaintenanceSchedule::where('machine_erp_id', $machineId)
            ->where('status', 'active')
            ->whereDate('start_date', $scheduledDate)
            ->with(['maintenancePoint', 'standard', 'assignedUser', 'executions' => function($query) use ($scheduledDate) {
                $query->where('scheduled_date', $scheduledDate)
                      ->orderBy('created_at', 'desc');
            }])
            ->orderBy('maintenance_point_id')
            ->get();
        
        // Remove duplicates based on maintenance_point_id to ensure unique points
        $uniqueSchedules = collect();
        $seenPointIds = [];
        
        foreach ($schedules as $schedule) {
            $pointId = $schedule->maintenance_point_id;
            
            // If no maintenance_point_id, use schedule id as unique identifier
            if (!$pointId) {
                $uniqueSchedules->push($schedule);
                continue;
            }
            
            // Only add if we haven't seen this point_id before
            if (!in_array($pointId, $seenPointIds)) {
                $seenPointIds[] = $pointId;
                $uniqueSchedules->push($schedule);
            }
        }
        
        $schedules = $uniqueSchedules;
        
        // Get PIC from first schedule
        $picId = null;
        $picName = null;
        if ($schedules->count() > 0) {
            $firstSchedule = $schedules->first();
            $picId = $firstSchedule->assigned_to;
            $picName = $firstSchedule->assignedUser ? $firstSchedule->assignedUser->name : null;
        }
        
        // Map schedules to maintenance points data
        $maintenancePoints = $schedules->map(function($schedule) use ($scheduledDate, $execution, $singlePoint) {
            // If single point mode, use the execution passed as parameter
            // Otherwise, get execution for this scheduled_date
            $pointExecution = null;
            if ($singlePoint && $schedule->id == $execution->schedule_id) {
                // Use the execution passed as parameter (the one being edited)
                $pointExecution = $execution;
            } else {
                // Get execution for this scheduled_date
                $pointExecution = $schedule->executions
                    ->where('scheduled_date', $scheduledDate)
                    ->sortByDesc('created_at')
                    ->first();
                
                // If no execution found for this scheduled_date, check if there's any execution for this schedule
                if (!$pointExecution) {
                    $pointExecution = $schedule->executions
                        ->sortByDesc('created_at')
                        ->first();
                }
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
                'has_execution' => $pointExecution !== null && $pointExecution->scheduled_date == $scheduledDate,
                'execution_id' => ($pointExecution && $pointExecution->scheduled_date == $scheduledDate) ? $pointExecution->id : null,
                'execution_status' => ($pointExecution && $pointExecution->scheduled_date == $scheduledDate) ? $pointExecution->status : 'pending',
                'measured_value' => ($pointExecution && $pointExecution->scheduled_date == $scheduledDate) ? $pointExecution->measured_value : null,
                'measurement_status' => ($pointExecution && $pointExecution->scheduled_date == $scheduledDate) ? $pointExecution->measurement_status : null,
                'performed_by' => ($pointExecution && $pointExecution->scheduled_date == $scheduledDate && $pointExecution->performedBy) ? $pointExecution->performedBy->id : null,
                'actual_start_time' => ($pointExecution && $pointExecution->scheduled_date == $scheduledDate && $pointExecution->actual_start_time) ? \Carbon\Carbon::parse($pointExecution->actual_start_time)->format('Y-m-d\TH:i') : null,
                'actual_end_time' => ($pointExecution && $pointExecution->scheduled_date == $scheduledDate && $pointExecution->actual_end_time) ? \Carbon\Carbon::parse($pointExecution->actual_end_time)->format('Y-m-d\TH:i') : null,
                'findings' => ($pointExecution && $pointExecution->scheduled_date == $scheduledDate) ? $pointExecution->findings : null,
                'actions_taken' => ($pointExecution && $pointExecution->scheduled_date == $scheduledDate) ? $pointExecution->actions_taken : null,
                'notes' => ($pointExecution && $pointExecution->scheduled_date == $scheduledDate) ? $pointExecution->notes : null,
                'checklist' => ($pointExecution && $pointExecution->scheduled_date == $scheduledDate) ? $pointExecution->checklist : null,
                'photo_before' => ($pointExecution && $pointExecution->scheduled_date == $scheduledDate && $pointExecution->photo_before) ? asset('public-storage/' . $pointExecution->photo_before) : null,
                'photo_after' => ($pointExecution && $pointExecution->scheduled_date == $scheduledDate && $pointExecution->photo_after) ? asset('public-storage/' . $pointExecution->photo_after) : null,
            ];
        })->values();
        
        // Debug: Log jumlah schedules dan maintenance points
        \Log::info('UpdatingController@edit - Schedules count: ' . $schedules->count());
        \Log::info('UpdatingController@edit - Maintenance points count: ' . $maintenancePoints->count());
        \Log::info('UpdatingController@edit - Machine ID: ' . $machineId . ', Scheduled Date: ' . $scheduledDate);
        
        // Get only team_leader users
        $users = User::where('role', 'team_leader')
            ->orderBy('name')
            ->get();
        $machine = $execution->schedule->machineErp;
        
        return view('predictive-maintenance.updating.edit', compact('execution', 'users', 'maintenancePoints', 'machine', 'scheduledDate', 'picId', 'picName'));
    }
    
    /**
     * Batch update multiple executions for a jadwal.
     */
    public function batchUpdate(Request $request)
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
            'executions.*.actual_start_time' => 'nullable|date',
            'executions.*.actual_end_time' => 'nullable|date',
            'executions.*.findings' => 'nullable|string',
            'executions.*.actions_taken' => 'nullable|string',
            'executions.*.notes' => 'nullable|string',
            'executions.*.checklist' => 'nullable|array',
        ]);

        $executionsUpdated = 0;
        $errors = [];
        
        foreach ($validated['executions'] as $index => $executionData) {
            $schedule = PredictiveMaintenanceSchedule::findOrFail($executionData['schedule_id']);
            
            // Calculate measurement_status based on standard
            $measuredValue = $executionData['measured_value'] ?? null;
            $measurementStatus = null;
            
            if ($measuredValue !== null && $schedule->standard) {
                $measurementStatus = $schedule->standard->getMeasurementStatus($measuredValue);
            }
            
            // Validation: If status is completed, measured_value must be filled
            if ($executionData['status'] === 'completed' && (empty($measuredValue) || $measuredValue === '')) {
                $pointName = $schedule->maintenancePoint ? $schedule->maintenancePoint->name : $schedule->title;
                $errors[] = "Point '{$pointName}' memerlukan Measured Value untuk status Completed.";
                continue; // Skip this execution
            }
            
            // Check if execution_id exists
            $executionId = $executionData['execution_id'] ?? null;
            
            if ($executionId) {
                // Update existing execution
                $execution = PredictiveMaintenanceExecution::findOrFail($executionId);
                
                // Auto set start time if status changed to in_progress
                $actualStartTime = $executionData['actual_start_time'] ?? null;
                if ($executionData['status'] == 'in_progress' && !$actualStartTime && !$execution->actual_start_time) {
                    $actualStartTime = now();
                }
                
                // Auto set end time if status changed to completed
                $actualEndTime = $executionData['actual_end_time'] ?? null;
                if ($executionData['status'] == 'completed' && !$actualEndTime && !$execution->actual_end_time) {
                    $actualEndTime = now();
                }
                
                $execution->update([
                    'status' => $executionData['status'],
                    'measured_value' => $measuredValue,
                    'measurement_status' => $measurementStatus,
                    'performed_by' => $validated['performed_by'] ?? $execution->performed_by,
                    'actual_start_time' => $actualStartTime ?? $execution->actual_start_time,
                    'actual_end_time' => $actualEndTime ?? $execution->actual_end_time,
                    'findings' => $executionData['findings'] ?? $execution->findings,
                    'actions_taken' => $executionData['actions_taken'] ?? $execution->actions_taken,
                    'notes' => $executionData['notes'] ?? $execution->notes,
                    'checklist' => $executionData['checklist'] ?? $execution->checklist,
                ]);
                $executionsUpdated++;
            } else {
                // Create new execution if doesn't exist
                $actualStartTime = $executionData['actual_start_time'] ?? null;
                if ($executionData['status'] == 'in_progress' && !$actualStartTime) {
                    $actualStartTime = now();
                }
                
                $actualEndTime = $executionData['actual_end_time'] ?? null;
                if ($executionData['status'] == 'completed' && !$actualEndTime) {
                    $actualEndTime = now();
                }
                
                // Validation: If status is completed, measured_value must be filled
                if ($executionData['status'] === 'completed' && empty($measuredValue)) {
                    continue; // Skip this execution
                }
                
                PredictiveMaintenanceExecution::create([
                    'schedule_id' => $executionData['schedule_id'],
                    'scheduled_date' => $validated['scheduled_date'],
                    'status' => $executionData['status'],
                    'measured_value' => $measuredValue,
                    'measurement_status' => $measurementStatus,
                    'performed_by' => $validated['performed_by'] ?? null,
                    'actual_start_time' => $actualStartTime,
                    'actual_end_time' => $actualEndTime,
                    'findings' => $executionData['findings'] ?? null,
                    'actions_taken' => $executionData['actions_taken'] ?? null,
                    'notes' => $executionData['notes'] ?? null,
                    'checklist' => $executionData['checklist'] ?? null,
                ]);
                $executionsUpdated++;
            }
        }

        if (!empty($errors)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['executions' => $errors])
                ->with('error', 'Beberapa execution tidak dapat diupdate: ' . implode(' ', $errors));
        }

        $message = "Berhasil update {$executionsUpdated} execution(s).";
        if ($executionsUpdated == 0) {
            $message = "Tidak ada execution yang diupdate.";
        }

        return redirect()->route('predictive-maintenance.updating.index')
            ->with('success', $message);
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
            'checklist' => 'nullable|array',
            'checklist.*.item' => 'required|string',
            'checklist.*.checked' => 'boolean',
            'checklist.*.notes' => 'nullable|string',
            'photo_before' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'photo_after' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        // Validation: If status is completed, measured_value must be filled
        if ($validated['status'] === 'completed' && empty($validated['measured_value'])) {
            return redirect()->back()
                ->withErrors(['measured_value' => 'Measured value harus diisi jika status adalah Completed.'])
                ->withInput();
        }
        
        $execution = PredictiveMaintenanceExecution::findOrFail($id);
        
        // Calculate measurement_status based on standard
        $measuredValue = $validated['measured_value'] ?? null;
        $measurementStatus = null;
        
        if ($measuredValue !== null && $execution->schedule && $execution->schedule->standard) {
            $measurementStatus = $execution->schedule->standard->getMeasurementStatus($measuredValue);
        }
        
        $validated['measurement_status'] = $measurementStatus;
        
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
        
        // Auto set start time if status changed to in_progress
        if ($validated['status'] == 'in_progress' && !$execution->actual_start_time) {
            $validated['actual_start_time'] = now();
        }
        
        // Auto set end time if status changed to completed
        if ($validated['status'] == 'completed' && !$execution->actual_end_time) {
            $validated['actual_end_time'] = now();
        }
        
        // Recalculate measurement_status if measured_value is provided and standard exists
        if (isset($validated['measured_value']) && $execution->schedule && $execution->schedule->standard) {
            $validated['measurement_status'] = $execution->schedule->standard->getMeasurementStatus($validated['measured_value']);
        }
        
        $execution->update($validated);
        
        // If request is AJAX, return JSON response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Execution berhasil diupdate.',
                'execution' => $execution->fresh(['schedule.maintenancePoint', 'schedule.standard', 'performedBy'])
            ]);
        }
        
        return redirect()->route('predictive-maintenance.updating.index')
            ->with('success', 'Execution berhasil diupdate.');
    }
}

<?php

namespace App\Http\Controllers\PreventiveMaintenance;

use App\Http\Controllers\Controller;
use App\Models\PreventiveMaintenanceExecution;
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
        // Get ALL executions (not just pending/in_progress) - IMPORTANT: 1 jadwal = 1 execution (latest)
        $query = PreventiveMaintenanceExecution::with(['schedule.machineErp.machineType', 'schedule.machineErp.roomErp', 'performedBy', 'schedule']);
        
        // Filter by user role (mekanik only sees their own assigned executions)
        if (DataFilterHelper::shouldFilterRoute(request()->route()->getName())) {
            $user = auth()->user();
            if ($user && $user->role === 'mekanik' && $user->id) {
                $query->where('performed_by', $user->id);
            }
        }
        
        $allExecutions = $query->orderBy('scheduled_date', 'asc')->orderBy('created_at', 'desc')->get();
        
        // Group all executions by (machine_erp_id, scheduled_date)
        // For each jadwal, use only the LATEST execution (1 jadwal = 1 execution)
        $allJadwalData = [];
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
            if (!isset($allJadwalData[$key])) {
                $allJadwalData[$key] = [
                    'machine_id' => $machineId,
                    'machine' => $execution->schedule->machineErp,
                    'scheduled_date' => $dateFormatted,
                    'latest_execution' => $execution, // Store only the latest execution
                ];
            } else {
                // Update if this execution is newer (later created_at)
                if ($execution->created_at > $allJadwalData[$key]['latest_execution']->created_at) {
                    $allJadwalData[$key]['latest_execution'] = $execution;
                }
            }
        }
        
        // Filter: only include jadwal that have pending or in_progress status (latest execution)
        // Exclude jadwal that already have completed status (completed is the latest update)
        $jadwalData = [];
        foreach ($allJadwalData as $key => $jadwal) {
            $latestExecution = $jadwal['latest_execution'];
            
            // Skip jadwal that already has completed status (latest execution is completed)
            if ($latestExecution->status == 'completed') {
                continue;
            }
            
            // Only include jadwal with pending or in_progress status (latest execution)
            if ($latestExecution->status == 'pending' || $latestExecution->status == 'in_progress') {
                $jadwalData[$key] = [
                    'machine_id' => $jadwal['machine_id'],
                    'machine' => $jadwal['machine'],
                    'scheduled_date' => $jadwal['scheduled_date'],
                    'executions' => [$latestExecution], // Only the latest execution
                    'status' => $latestExecution->status,
                ];
            }
        }
        
        // Convert to collection and paginate manually
        $jadwalCollection = collect($jadwalData)->values();
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;
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
            
            // Only include jadwal where latest execution is completed
            if ($latestExecution->status == 'completed') {
                $completedJadwal[$key] = [
                    'machine_id' => $jadwal['machine_id'],
                    'machine' => $jadwal['machine'],
                    'scheduled_date' => $jadwal['scheduled_date'],
                    'executions' => [$latestExecution], // Only the latest execution
                    'latest_end_time' => $latestExecution->actual_end_time,
                ];
            }
        }
        
        // Sort completed by latest_end_time desc and limit to 20
        usort($completedJadwal, function($a, $b) {
            $timeA = $a['latest_end_time'] ? \Carbon\Carbon::parse($a['latest_end_time']) : \Carbon\Carbon::createFromTimestamp(0);
            $timeB = $b['latest_end_time'] ? \Carbon\Carbon::parse($b['latest_end_time']) : \Carbon\Carbon::createFromTimestamp(0);
            return $timeB->gt($timeA) ? 1 : -1;
        });
        $completedJadwal = array_slice($completedJadwal, 0, 20);
        
        $completedJadwalCollection = collect($completedJadwal);
        
        // Calculate statistics - only count jadwal based on latest execution status
        $pendingCount = 0;
        $inProgressCount = 0;
        $completedCount = 0;
        
        // Count based on latest execution status for each jadwal
        foreach ($allJadwalData as $key => $jadwal) {
            $latestExecution = $jadwal['latest_execution'];
            
            if ($latestExecution->status == 'pending') {
                $pendingCount++;
            } elseif ($latestExecution->status == 'in_progress') {
                $inProgressCount++;
            } elseif ($latestExecution->status == 'completed') {
                $completedCount++;
            }
        }
        
        // Calculate statistics
        $stats = [
            'pending' => $pendingCount,
            'in_progress' => $inProgressCount,
            'completed' => $completedCount,
            'total' => count($jadwalData),
        ];
        
        return view('preventive-maintenance.updating.index', compact('jadwalPaginator', 'completedJadwalCollection', 'stats'));
    }
    
    /**
     * Get maintenance points by machine and date (for modal)
     */
    public function getMaintenancePointsByMachineAndDate(Request $request)
    {
        $machineId = $request->input('machine_id');
        $scheduledDate = $request->input('scheduled_date');
        
        if (!$machineId || !$scheduledDate) {
            return response()->json(['maintenance_points' => []]);
        }
        
        try {
            // Get schedules for this machine and date - now using MachineErp
            $schedules = \App\Models\PreventiveMaintenanceSchedule::where('machine_erp_id', $machineId)
                ->where('start_date', $scheduledDate)
                ->where('status', 'active')
                ->with(['maintenancePoint', 'assignedUser', 'executions'])
                ->orderBy('maintenance_point_id')
                ->get();
            
            $maintenancePoints = [];
            foreach ($schedules as $schedule) {
                $execution = $schedule->executions()->latest()->first();
                
                $maintenancePoints[] = [
                    'schedule_id' => $schedule->id,
                    'execution_id' => $execution ? $execution->id : null,
                    'maintenance_point_name' => $schedule->maintenancePoint->name ?? '-',
                    'instruction' => $schedule->maintenancePoint->instruction ?? '',
                    'status' => $execution ? $execution->status : 'pending',
                    'performed_by' => $execution && $execution->performedBy ? $execution->performedBy->name : '-',
                    'actual_start_time' => $execution && $execution->actual_start_time ? \Carbon\Carbon::parse($execution->actual_start_time)->format('d/m/Y H:i') : '-',
                    'actual_end_time' => $execution && $execution->actual_end_time ? \Carbon\Carbon::parse($execution->actual_end_time)->format('d/m/Y H:i') : '-',
                ];
            }
            
            return response()->json(['maintenance_points' => $maintenancePoints]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for updating execution status.
     */
    public function edit(string $id)
    {
        $execution = PreventiveMaintenanceExecution::with(['schedule.machine', 'schedule.maintenancePoint', 'performedBy'])
            ->findOrFail($id);
        
        $users = User::whereIn('role', ['mekanik', 'team_leader', 'group_leader', 'coordinator'])->get();
        
        // Get checklist from maintenance point if available
        $checklist = [];
        if ($execution->schedule->maintenancePoint) {
            $checklist = [
                ['item' => $execution->schedule->maintenancePoint->name, 'checked' => false, 'notes' => ''],
            ];
        }
        
        // Load existing checklist if available
        if ($execution->checklist) {
            $checklist = $execution->checklist;
        }
        
        return view('preventive-maintenance.updating.edit', compact('execution', 'users', 'checklist'));
    }

    /**
     * Update the execution status and details.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,skipped,cancelled',
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
        
        // Auto set start time if status changed to in_progress
        if ($validated['status'] == 'in_progress' && !$execution->actual_start_time) {
            $validated['actual_start_time'] = now();
        }
        
        // Auto set end time if status changed to completed
        if ($validated['status'] == 'completed' && !$execution->actual_end_time) {
            $validated['actual_end_time'] = now();
        }
        
        $execution->update($validated);

        return redirect()->route('preventive-maintenance.updating.index')
            ->with('success', 'Execution berhasil diupdate.');
    }
}

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
        // Get executions with status pending or in_progress - now using MachineErp
        $query = PreventiveMaintenanceExecution::whereIn('status', ['pending', 'in_progress'])
            ->with(['schedule.machineErp.machineType', 'schedule.machineErp.roomErp', 'performedBy', 'schedule']);
        
        // Filter by user role (mekanik only sees their own assigned executions)
        if (DataFilterHelper::shouldFilterRoute(request()->route()->getName())) {
            $user = auth()->user();
            if ($user && $user->role === 'mekanik' && $user->id) {
                $query->where('performed_by', $user->id);
            }
        }
        
        $executionsRaw = $query->orderBy('scheduled_date', 'asc')->get();
        
        // Group by (machine_erp_id, scheduled_date) to get unique jadwal
        $jadwalData = [];
        foreach ($executionsRaw as $execution) {
            $machineId = $execution->schedule->machine_erp_id;
            $scheduledDate = $execution->scheduled_date;
            if (is_string($scheduledDate)) {
                $dateFormatted = $scheduledDate;
            } else {
                $dateFormatted = \Carbon\Carbon::parse($scheduledDate)->format('Y-m-d');
            }
            $key = $machineId . '_' . $dateFormatted;
            
            if (!isset($jadwalData[$key])) {
                $jadwalData[$key] = [
                    'machine_id' => $machineId,
                    'machine' => $execution->schedule->machineErp,
                    'scheduled_date' => $dateFormatted,
                    'executions' => [],
                    'status' => 'pending', // Default
                ];
            }
            $jadwalData[$key]['executions'][] = $execution;
            
            // Update status: if any execution is in_progress, set status to in_progress
            if ($execution->status == 'in_progress') {
                $jadwalData[$key]['status'] = 'in_progress';
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
        
        // Get completed executions for information only - grouped by jadwal
        $completedExecutionsRaw = PreventiveMaintenanceExecution::where('status', 'completed')
            ->with(['schedule.machineErp.machineType', 'schedule.machineErp.roomErp', 'performedBy', 'schedule'])
            ->orderBy('actual_end_time', 'desc')
            ->limit(100) // Get more to group
            ->get();
        
        // Group completed by (machine_erp_id, scheduled_date)
        $completedJadwal = [];
        foreach ($completedExecutionsRaw as $execution) {
            $machineId = $execution->schedule->machine_erp_id;
            $scheduledDate = $execution->scheduled_date;
            if (is_string($scheduledDate)) {
                $dateFormatted = $scheduledDate;
            } else {
                $dateFormatted = \Carbon\Carbon::parse($scheduledDate)->format('Y-m-d');
            }
            $key = $machineId . '_' . $dateFormatted;
            
            if (!isset($completedJadwal[$key])) {
                $completedJadwal[$key] = [
                    'machine_id' => $machineId,
                    'machine' => $execution->schedule->machineErp,
                    'scheduled_date' => $dateFormatted,
                    'executions' => [],
                    'latest_end_time' => $execution->actual_end_time,
                ];
            }
            $completedJadwal[$key]['executions'][] = $execution;
            
            // Keep track of latest end time
            if ($execution->actual_end_time && 
                (!$completedJadwal[$key]['latest_end_time'] || 
                 \Carbon\Carbon::parse($execution->actual_end_time)->gt(\Carbon\Carbon::parse($completedJadwal[$key]['latest_end_time'])))) {
                $completedJadwal[$key]['latest_end_time'] = $execution->actual_end_time;
            }
        }
        
        // Sort completed by latest_end_time desc and limit to 20
        $completedJadwalCollection = collect($completedJadwal)->values();
        $completedJadwalCollection = $completedJadwalCollection->sortByDesc(function($item) {
            if (!$item['latest_end_time']) {
                return '1970-01-01 00:00:00';
            }
            return \Carbon\Carbon::parse($item['latest_end_time'])->format('Y-m-d H:i:s');
        })->take(20)->values();
        
        // Calculate statistics - completed should be count of unique jadwal (machine_id + scheduled_date)
        $completedExecutionsForStats = PreventiveMaintenanceExecution::where('status', 'completed')
            ->with(['schedule'])
            ->get();
        
        // Group completed executions by (machine_erp_id, scheduled_date) to get unique jadwal
        $completedJadwalForStats = [];
        foreach ($completedExecutionsForStats as $execution) {
            $machineId = $execution->schedule->machine_erp_id;
            $scheduledDate = $execution->scheduled_date;
            if (is_string($scheduledDate)) {
                $dateFormatted = $scheduledDate;
            } else {
                $dateFormatted = \Carbon\Carbon::parse($scheduledDate)->format('Y-m-d');
            }
            $key = $machineId . '_' . $dateFormatted;
            
            if (!isset($completedJadwalForStats[$key])) {
                $completedJadwalForStats[$key] = true;
            }
        }
        $completedJadwalCount = count($completedJadwalForStats);
        
        // Calculate statistics
        $stats = [
            'pending' => PreventiveMaintenanceExecution::where('status', 'pending')->count(),
            'in_progress' => PreventiveMaintenanceExecution::where('status', 'in_progress')->count(),
            'completed' => $completedJadwalCount, // Jumlah tanggal (jadwal) yang sudah complete
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

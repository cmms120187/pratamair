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
        // Get executions with status pending or in_progress
        $query = PredictiveMaintenanceExecution::whereIn('status', ['pending', 'in_progress'])
            ->with(['schedule.machineErp.machineType', 'schedule.machineErp.roomErp', 'schedule.standard', 'performedBy', 'schedule']);
        
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
                    'status' => 'pending',
                ];
            }
            $jadwalData[$key]['executions'][] = $execution;
            
            if ($execution->status == 'in_progress') {
                $jadwalData[$key]['status'] = 'in_progress';
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
        
        // Get completed executions for information only - grouped by jadwal
        $completedExecutionsRaw = PredictiveMaintenanceExecution::where('status', 'completed')
            ->with(['schedule.machineErp.machineType', 'schedule.machineErp.roomErp', 'schedule.standard', 'performedBy', 'schedule'])
            ->orderBy('actual_end_time', 'desc')
            ->limit(100)
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
            
            if ($execution->actual_end_time && 
                (!$completedJadwal[$key]['latest_end_time'] || 
                 \Carbon\Carbon::parse($execution->actual_end_time)->gt(\Carbon\Carbon::parse($completedJadwal[$key]['latest_end_time'])))) {
                $completedJadwal[$key]['latest_end_time'] = $execution->actual_end_time;
            }
        }
        
        // Sort by latest_end_time and limit to 20
        usort($completedJadwal, function($a, $b) {
            $timeA = $a['latest_end_time'] ? \Carbon\Carbon::parse($a['latest_end_time']) : \Carbon\Carbon::minValue();
            $timeB = $b['latest_end_time'] ? \Carbon\Carbon::parse($b['latest_end_time']) : \Carbon\Carbon::minValue();
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
        
        // Statistics
        $pendingCount = PredictiveMaintenanceExecution::where('status', 'pending')->count();
        $inProgressCount = PredictiveMaintenanceExecution::where('status', 'in_progress')->count();
        
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
     */
    public function getMaintenancePointsByMachineAndDate(Request $request)
    {
        $machineId = $request->input('machine_id');
        $scheduledDate = $request->input('scheduled_date');
        
        if (!$machineId || !$scheduledDate) {
            return response()->json(['maintenance_points' => []]);
        }
        
        $executions = PredictiveMaintenanceExecution::whereHas('schedule', function ($query) use ($machineId, $scheduledDate) {
            $query->where('machine_erp_id', $machineId)
                  ->whereDate('start_date', $scheduledDate);
        })
        ->with(['schedule.maintenancePoint', 'schedule.standard', 'performedBy'])
        ->orderBy('id', 'asc')
        ->get();
        
        $maintenancePoints = [];
        foreach ($executions as $execution) {
            $maintenancePoints[] = [
                'execution_id' => $execution->id,
                'schedule_id' => $execution->schedule->id,
                'maintenance_point_name' => $execution->schedule->maintenancePoint->name ?? $execution->schedule->title,
                'standard_name' => $execution->schedule->standard->name ?? '-',
                'standard_unit' => $execution->schedule->standard->unit ?? '-',
                'standard_min' => $execution->schedule->standard->min_value,
                'standard_max' => $execution->schedule->standard->max_value,
                'standard_target' => $execution->schedule->standard->target_value,
                'measured_value' => $execution->measured_value,
                'measurement_status' => $execution->measurement_status,
                'instruction' => $execution->schedule->maintenancePoint->instruction ?? $execution->schedule->description ?? '',
                'status' => $execution->status,
                'performed_by' => $execution->performedBy->name ?? '-',
                'actual_start_time' => $execution->actual_start_time ? \Carbon\Carbon::parse($execution->actual_start_time)->format('d/m/Y H:i') : '-',
                'actual_end_time' => $execution->actual_end_time ? \Carbon\Carbon::parse($execution->actual_end_time)->format('d/m/Y H:i') : '-',
            ];
        }
        
        return response()->json(['maintenance_points' => $maintenancePoints]);
    }
    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $execution = PredictiveMaintenanceExecution::with(['schedule.machineErp.machineType', 'schedule.machineErp.roomErp', 'schedule.standard', 'schedule.maintenancePoint', 'performedBy'])
            ->findOrFail($id);
        $users = User::whereIn('role', ['mekanik', 'team_leader', 'group_leader', 'coordinator'])->get();
        
        return view('predictive-maintenance.updating.edit', compact('execution', 'users'));
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
        
        $execution->update($validated);
        
        return redirect()->route('predictive-maintenance.updating.index')
            ->with('success', 'Execution berhasil diupdate.');
    }
}

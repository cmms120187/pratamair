<?php

namespace App\Http\Controllers\PreventiveMaintenance;

use App\Http\Controllers\Controller;
use App\Models\PreventiveMaintenanceSchedule;
use App\Models\PreventiveMaintenanceExecution;
use App\Models\MachineErp;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportingController extends Controller
{
    /**
     * Display reporting dashboard.
     */
    public function index()
    {
        return view('preventive-maintenance.reporting.index');
    }
    
    /**
     * Generate schedule report.
     */
    public function scheduleReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());
        $machineId = $request->get('machine_id');
        $status = $request->get('status');
        
        $query = PreventiveMaintenanceSchedule::with(['machineErp', 'assignedUser', 'maintenancePoint'])
            ->whereBetween('start_date', [$startDate, $endDate]);
        
        if ($machineId) {
            $query->where('machine_erp_id', $machineId);
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $schedulesRaw = $query->orderBy('start_date', 'asc')->get();
        $machines = MachineErp::all();
        
        // Group schedules by (machine_erp_id, start_date) to get unique jadwal
        $jadwalData = [];
        foreach ($schedulesRaw as $schedule) {
            $machineId = $schedule->machine_erp_id;
            $scheduleDate = $schedule->start_date;
            if (is_string($scheduleDate)) {
                $dateFormatted = $scheduleDate;
            } else {
                $dateFormatted = Carbon::parse($scheduleDate)->format('Y-m-d');
            }
            $key = $machineId . '_' . $dateFormatted;
            
            if (!isset($jadwalData[$key])) {
                $jadwalData[$key] = [
                    'machine_id' => $machineId,
                    'machine' => $schedule->machineErp,
                    'start_date' => $dateFormatted,
                    'schedules' => [],
                    'assignedUser' => $schedule->assignedUser,
                ];
            }
            $jadwalData[$key]['schedules'][] = $schedule;
        }
        
        // Convert to collection and paginate
        $jadwalCollection = collect($jadwalData)->values();
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 12;
        $currentItems = $jadwalCollection->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $jadwalPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $jadwalCollection->count(),
            $perPage,
            $currentPage,
            [
                'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
        // Append query parameters to pagination links
        $jadwalPaginator->appends($request->except('page'));
        
        return view('preventive-maintenance.reporting.schedule', compact('jadwalPaginator', 'machines', 'startDate', 'endDate', 'machineId', 'status'));
    }
    
    /**
     * Get maintenance points by machine and date for schedule report.
     */
    public function getSchedulePointsByMachineAndDate(Request $request)
    {
        $machineId = $request->input('machine_id');
        $scheduleDate = $request->input('schedule_date');
        
        if (!$machineId || !$scheduleDate) {
            return response()->json(['maintenance_points' => []]);
        }
        
        try {
            // Get schedules for this machine and date
            $schedules = PreventiveMaintenanceSchedule::where('machine_erp_id', $machineId)
                ->where('start_date', $scheduleDate)
                ->where('status', 'active')
                ->with(['maintenancePoint', 'assignedUser'])
                ->orderBy('maintenance_point_id')
                ->get();
            
            $maintenancePoints = [];
            foreach ($schedules as $schedule) {
                $maintenancePoints[] = [
                    'schedule_id' => $schedule->id,
                    'maintenance_point_name' => $schedule->maintenancePoint->name ?? $schedule->title,
                    'instruction' => $schedule->maintenancePoint->instruction ?? $schedule->description ?? '',
                    'frequency' => ucfirst($schedule->frequency_type) . ($schedule->frequency_value > 1 ? ' (' . $schedule->frequency_value . ')' : ''),
                    'assigned_to' => $schedule->assignedUser ? $schedule->assignedUser->name : '-',
                    'preferred_time' => $schedule->preferred_time ? Carbon::parse($schedule->preferred_time)->format('H:i') : '-',
                    'estimated_duration' => $schedule->estimated_duration ? $schedule->estimated_duration . ' min' : '-',
                ];
            }
            
            return response()->json(['maintenance_points' => $maintenancePoints]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Generate execution report.
     */
    public function executionReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());
        $machineId = $request->get('machine_id');
        $status = $request->get('status');
        
        // Get ALL executions (not filtered by status) - IMPORTANT: 1 jadwal = 1 execution (latest)
        $allExecutionsQuery = PreventiveMaintenanceExecution::with(['schedule.machineErp', 'performedBy'])
            ->whereBetween('scheduled_date', [$startDate, $endDate]);
        
        if ($machineId) {
            $allExecutionsQuery->whereHas('schedule', function($q) use ($machineId) {
                $q->where('machine_erp_id', $machineId);
            });
        }
        
        $allExecutionsRaw = $allExecutionsQuery->orderBy('scheduled_date', 'asc')->orderBy('created_at', 'desc')->get();
        
        // Group ALL executions by (machine_erp_id, scheduled_date)
        // For each jadwal, use only the LATEST execution (1 jadwal = 1 execution)
        $jadwalByMachineAndDate = [];
        foreach ($allExecutionsRaw as $execution) {
            $machineIdKey = $execution->schedule->machine_erp_id;
            $scheduledDate = $execution->scheduled_date;
            if (is_string($scheduledDate)) {
                $dateFormatted = $scheduledDate;
            } else {
                $dateFormatted = Carbon::parse($scheduledDate)->format('Y-m-d');
            }
            $key = $machineIdKey . '_' . $dateFormatted;
            
            // For each jadwal, only keep the latest execution (most recent created_at)
            if (!isset($jadwalByMachineAndDate[$key])) {
                $jadwalByMachineAndDate[$key] = [
                    'machine_id' => $machineIdKey,
                    'scheduled_date' => $dateFormatted,
                    'latest_execution' => $execution,
                ];
            } else {
                // Update if this execution is newer (later created_at)
                if ($execution->created_at > $jadwalByMachineAndDate[$key]['latest_execution']->created_at) {
                    $jadwalByMachineAndDate[$key]['latest_execution'] = $execution;
                }
            }
        }
        
        // Calculate statistics based on latest execution status for each jadwal
        $totalJadwal = count($jadwalByMachineAndDate);
        $completedJadwal = 0;
        $pendingJadwal = 0;
        $inProgressJadwal = 0;
        
        foreach ($jadwalByMachineAndDate as $key => $jadwal) {
            $latestExecution = $jadwal['latest_execution'];
            
            if ($latestExecution->status == 'completed') {
                $completedJadwal++;
            } elseif ($latestExecution->status == 'in_progress') {
                $inProgressJadwal++;
            } elseif ($latestExecution->status == 'pending') {
                $pendingJadwal++;
            }
        }
        
        // Filter executions to display: use latest execution for each jadwal
        $executionsToDisplay = [];
        foreach ($jadwalByMachineAndDate as $key => $jadwal) {
            $latestExecution = $jadwal['latest_execution'];
            
            // Apply status filter if specified
            if ($status && $latestExecution->status != $status) {
                continue;
            }
            
            $executionsToDisplay[] = $latestExecution;
        }
        
        // For cost and duration, use completed executions only (latest for each jadwal)
        $completedExecutions = [];
        foreach ($jadwalByMachineAndDate as $key => $jadwal) {
            $latestExecution = $jadwal['latest_execution'];
            if ($latestExecution->status == 'completed') {
                $completedExecutions[] = $latestExecution;
            }
        }
        
        // Statistics
        $stats = [
            'total' => $totalJadwal,
            'completed' => $completedJadwal,
            'pending' => $pendingJadwal,
            'in_progress' => $inProgressJadwal,
            'total_cost' => collect($completedExecutions)->sum('cost'),
            'avg_duration' => collect($completedExecutions)->avg(function($execution) {
                if ($execution->actual_start_time && $execution->actual_end_time) {
                    return $execution->actual_start_time->diffInMinutes($execution->actual_end_time);
                }
                return null;
            }),
        ];
        
        // Paginate executions
        $executionsCollection = collect($executionsToDisplay);
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 12;
        $currentItems = $executionsCollection->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $executions = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $executionsCollection->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
        );
        $executions->appends($request->except('page'));
        
        $machines = MachineErp::all();
        
        return view('preventive-maintenance.reporting.execution', compact('executions', 'machines', 'startDate', 'endDate', 'machineId', 'status', 'stats'));
    }
    
    /**
     * Generate performance report.
     */
    public function performanceReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonths(6)->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());
        
        // Monthly completion rate
        $monthlyData = [];
        $current = Carbon::parse($startDate)->startOfMonth();
        $end = Carbon::parse($endDate)->endOfMonth();
        
        while ($current <= $end) {
            // Get all executions for this month
            $executionsForMonth = PreventiveMaintenanceExecution::whereMonth('scheduled_date', $current->month)
                ->whereYear('scheduled_date', $current->year)
                ->with(['schedule'])
                ->get();
            
            // Group by (machine_id, scheduled_date) to get unique jadwal
            $jadwalForMonth = [];
            foreach ($executionsForMonth as $execution) {
                $machineId = $execution->schedule->machine_id;
                $scheduledDate = $execution->scheduled_date;
                if (is_string($scheduledDate)) {
                    $dateFormatted = $scheduledDate;
                } else {
                    $dateFormatted = Carbon::parse($scheduledDate)->format('Y-m-d');
                }
                $key = $machineId . '_' . $dateFormatted;
                
                if (!isset($jadwalForMonth[$key])) {
                    $jadwalForMonth[$key] = [
                        'machine_id' => $machineId,
                        'scheduled_date' => $dateFormatted,
                        'executions' => [],
                        'status' => 'pending',
                    ];
                }
                $jadwalForMonth[$key]['executions'][] = $execution;
                
                // Update status
                if ($execution->status == 'completed') {
                    $jadwalForMonth[$key]['status'] = 'completed';
                } elseif ($execution->status == 'in_progress' && $jadwalForMonth[$key]['status'] != 'completed') {
                    $jadwalForMonth[$key]['status'] = 'in_progress';
                }
            }
            
            $totalJadwal = count($jadwalForMonth);
            $completedJadwal = 0;
            foreach ($jadwalForMonth as $jadwal) {
                if ($jadwal['status'] == 'completed') {
                    $completedJadwal++;
                }
            }
            
            $monthlyData[] = [
                'month' => $current->format('M Y'),
                'total' => $totalJadwal, // Jumlah jadwal (tanggal)
                'completed' => $completedJadwal, // Jumlah jadwal completed
                'rate' => $totalJadwal > 0 ? round(($completedJadwal / $totalJadwal) * 100, 2) : 0,
            ];
            
            $current->addMonth();
        }
        
        // Machine performance - based on jadwal (machine_id + scheduled_date)
        $machinePerformance = Machine::get()->map(function($machine) use ($startDate, $endDate) {
            $executions = PreventiveMaintenanceExecution::whereHas('schedule', function($q) use ($machine) {
                $q->where('machine_id', $machine->id);
            })
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->with(['schedule'])
            ->get();
            
            // Group by (machine_id, scheduled_date) to get unique jadwal
            $jadwalForMachine = [];
            foreach ($executions as $execution) {
                $scheduledDate = $execution->scheduled_date;
                if (is_string($scheduledDate)) {
                    $dateFormatted = $scheduledDate;
                } else {
                    $dateFormatted = Carbon::parse($scheduledDate)->format('Y-m-d');
                }
                $key = $machine->id . '_' . $dateFormatted;
                
                if (!isset($jadwalForMachine[$key])) {
                    $jadwalForMachine[$key] = [
                        'scheduled_date' => $dateFormatted,
                        'executions' => [],
                        'status' => 'pending',
                    ];
                }
                $jadwalForMachine[$key]['executions'][] = $execution;
                
                // Update status
                if ($execution->status == 'completed') {
                    $jadwalForMachine[$key]['status'] = 'completed';
                } elseif ($execution->status == 'in_progress' && $jadwalForMachine[$key]['status'] != 'completed') {
                    $jadwalForMachine[$key]['status'] = 'in_progress';
                }
            }
            
            $totalJadwal = count($jadwalForMachine);
            $completedJadwal = 0;
            foreach ($jadwalForMachine as $jadwal) {
                if ($jadwal['status'] == 'completed') {
                    $completedJadwal++;
                }
            }
            
            return [
                'machine' => $machine,
                'total' => $totalJadwal, // Jumlah jadwal (tanggal)
                'completed' => $completedJadwal, // Jumlah jadwal completed
                'rate' => $totalJadwal > 0 ? round(($completedJadwal / $totalJadwal) * 100, 2) : 0,
            ];
        })->sortByDesc('rate')->values();
        
        // Paginate machine performance
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage('machine_page');
        $perPage = 12;
        $currentItems = $machinePerformance->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $machinePerformancePaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $machinePerformance->count(),
            $perPage,
            $currentPage,
            [
                'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'machine_page',
            ]
        );
        $machinePerformancePaginator->appends($request->except('machine_page'));
        
        return view('preventive-maintenance.reporting.performance', compact('monthlyData', 'machinePerformancePaginator', 'startDate', 'endDate'));
    }
}

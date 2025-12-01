<?php

namespace App\Http\Controllers\PredictiveMaintenance;

use App\Http\Controllers\Controller;
use App\Models\PredictiveMaintenanceSchedule;
use App\Models\PredictiveMaintenanceExecution;
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
        return view('predictive-maintenance.reporting.index');
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
        
        $query = PredictiveMaintenanceSchedule::with(['machineErp', 'standard', 'assignedUser', 'maintenancePoint'])
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
        $jadwalPaginator->appends($request->except('page'));
        
        return view('predictive-maintenance.reporting.schedule', compact('jadwalPaginator', 'machines', 'startDate', 'endDate', 'machineId', 'status'));
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
        
        $schedules = PredictiveMaintenanceSchedule::where('machine_erp_id', $machineId)
            ->whereDate('start_date', $scheduleDate)
            ->with(['maintenancePoint', 'standard', 'assignedUser'])
            ->orderBy('id', 'asc')
            ->get();
        
        $maintenancePoints = [];
        foreach ($schedules as $schedule) {
            $maintenancePoints[] = [
                'schedule_id' => $schedule->id,
                'maintenance_point_name' => $schedule->maintenancePoint->name ?? $schedule->title,
                'standard_name' => $schedule->standard->name ?? '-',
                'standard_reference' => $schedule->standard->reference_code ?? ($schedule->standard->reference_name ?? '-'),
                'standard_unit' => $schedule->standard->unit ?? '-',
                'standard_min' => $schedule->standard->min_value,
                'standard_max' => $schedule->standard->max_value,
                'standard_target' => $schedule->standard->target_value,
                'description' => $schedule->maintenancePoint->instruction ?? $schedule->description ?? '',
                'assigned_to' => $schedule->assignedUser->name ?? '-',
            ];
        }
        
        return response()->json(['maintenance_points' => $maintenancePoints]);
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
        
        $query = PredictiveMaintenanceExecution::with(['schedule.machineErp', 'schedule.standard', 'performedBy'])
            ->whereBetween('scheduled_date', [$startDate, $endDate]);
        
        if ($machineId) {
            $query->whereHas('schedule', function($q) use ($machineId) {
                $q->where('machine_erp_id', $machineId);
            });
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        // Group executions by (machine_erp_id, scheduled_date) to calculate statistics based on jadwal
        $executionsRaw = $query->get();
        
        $jadwalByMachineAndDate = [];
        foreach ($executionsRaw as $execution) {
            $machineId = $execution->schedule->machine_erp_id;
            $scheduledDate = $execution->scheduled_date;
            if (is_string($scheduledDate)) {
                $dateFormatted = $scheduledDate;
            } else {
                $dateFormatted = Carbon::parse($scheduledDate)->format('Y-m-d');
            }
            $key = $machineId . '_' . $dateFormatted;
            
            if (!isset($jadwalByMachineAndDate[$key])) {
                $jadwalByMachineAndDate[$key] = [
                    'machine_id' => $machineId,
                    'scheduled_date' => $dateFormatted,
                    'executions' => [],
                ];
            }
            $jadwalByMachineAndDate[$key]['executions'][] = $execution;
        }
        
        // Calculate statistics based on jadwal
        $totalJadwal = count($jadwalByMachineAndDate);
        $completedJadwal = 0;
        $pendingJadwal = 0;
        $inProgressJadwal = 0;
        
        foreach ($jadwalByMachineAndDate as $key => $jadwal) {
            $executionsForJadwal = $jadwal['executions'];
            $allCompleted = true;
            $hasInProgress = false;
            $hasPending = false;
            
            foreach ($executionsForJadwal as $execution) {
                if ($execution->status != 'completed') {
                    $allCompleted = false;
                }
                if ($execution->status == 'in_progress') {
                    $hasInProgress = true;
                }
                if ($execution->status == 'pending') {
                    $hasPending = true;
                }
            }
            
            if ($allCompleted) {
                $completedJadwal++;
            } elseif ($hasInProgress) {
                $inProgressJadwal++;
            } elseif ($hasPending) {
                $pendingJadwal++;
            }
        }
        
        // For cost and duration, still use individual executions (get all, not paginated)
        $allExecutionsQuery = PredictiveMaintenanceExecution::with(['schedule.machineErp', 'schedule.standard', 'performedBy'])
            ->whereBetween('scheduled_date', [$startDate, $endDate]);
        
        if ($machineId) {
            $allExecutionsQuery->whereHas('schedule', function($q) use ($machineId) {
                $q->where('machine_erp_id', $machineId);
            });
        }
        
        if ($status) {
            $allExecutionsQuery->where('status', $status);
        }
        
        $completedExecutions = $allExecutionsQuery->where('status', 'completed')->get();
        
        // Statistics
        $stats = [
            'total' => $totalJadwal,
            'completed' => $completedJadwal,
            'pending' => $pendingJadwal,
            'in_progress' => $inProgressJadwal,
            'total_cost' => $completedExecutions->sum('cost'),
            'avg_duration' => $completedExecutions->avg(function($execution) {
                if ($execution->actual_start_time && $execution->actual_end_time) {
                    return $execution->actual_start_time->diffInMinutes($execution->actual_end_time);
                }
                return null;
            }),
        ];
        
        // Paginate executions
        $executions = $query->orderBy('scheduled_date', 'desc')->paginate(12);
        $executions->appends($request->except('page'));
        
        $machines = MachineErp::all();
        
        return view('predictive-maintenance.reporting.execution', compact('executions', 'stats', 'machines', 'startDate', 'endDate', 'machineId', 'status'));
    }
    
    /**
     * Generate performance report.
     */
    public function performanceReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfYear()->toDateString());
        $endDate = $request->get('end_date', now()->endOfYear()->toDateString());
        
        // Monthly completion data
        $monthlyData = [];
        $currentDate = Carbon::parse($startDate);
        $endDateObj = Carbon::parse($endDate);
        
        while ($currentDate->lte($endDateObj)) {
            $monthStart = $currentDate->copy()->startOfMonth();
            $monthEnd = $currentDate->copy()->endOfMonth();
            
            $schedules = PredictiveMaintenanceSchedule::where('status', 'active')
                ->whereBetween('start_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->with('executions')
                ->get();
            
            // Group by (machine_erp_id, date) to get unique jadwal
            $jadwalByMachineAndDate = [];
            foreach ($schedules as $schedule) {
                $machineId = $schedule->machine_erp_id;
                $date = $schedule->start_date;
                if (is_string($date)) {
                    $dateFormatted = $date;
                } else {
                    $dateFormatted = Carbon::parse($date)->format('Y-m-d');
                }
                $key = $machineId . '_' . $dateFormatted;
                
                if (!isset($jadwalByMachineAndDate[$key])) {
                    $jadwalByMachineAndDate[$key] = [
                        'machine_id' => $machineId,
                        'date' => $dateFormatted,
                        'schedules' => []
                    ];
                }
                $jadwalByMachineAndDate[$key]['schedules'][] = $schedule;
            }
            
            $totalJadwal = count($jadwalByMachineAndDate);
            $completedJadwal = 0;
            
            foreach ($jadwalByMachineAndDate as $key => $jadwal) {
                $schedulesForJadwal = $jadwal['schedules'];
                $allCompleted = true;
                
                foreach ($schedulesForJadwal as $schedule) {
                    $executions = $schedule->relationLoaded('executions') ? $schedule->executions : $schedule->executions()->get();
                    $hasCompletedExecution = $executions->where('status', 'completed')->isNotEmpty();
                    
                    if (!$hasCompletedExecution) {
                        $allCompleted = false;
                        break;
                    }
                }
                
                if ($allCompleted) {
                    $completedJadwal++;
                }
            }
            
            $monthlyData[] = [
                'month' => $currentDate->format('M Y'),
                'total' => $totalJadwal,
                'completed' => $completedJadwal,
                'completion_rate' => $totalJadwal > 0 ? round(($completedJadwal / $totalJadwal) * 100, 1) : 0,
            ];
            
            $currentDate->addMonth();
        }
        
        // Machine performance
        $schedules = PredictiveMaintenanceSchedule::where('status', 'active')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->with('executions')
            ->get();
        
        // Group by machine_erp_id
        $machinePerformance = [];
        foreach ($schedules as $schedule) {
            $machineId = $schedule->machine_erp_id;
            
            if (!isset($machinePerformance[$machineId])) {
                $machinePerformance[$machineId] = [
                    'machine_id' => $machineId,
                    'machine' => $schedule->machineErp,
                    'jadwal' => [],
                ];
            }
            
            $date = $schedule->start_date;
            if (is_string($date)) {
                $dateFormatted = $date;
            } else {
                $dateFormatted = Carbon::parse($date)->format('Y-m-d');
            }
            $key = $machineId . '_' . $dateFormatted;
            
            if (!isset($machinePerformance[$machineId]['jadwal'][$key])) {
                $machinePerformance[$machineId]['jadwal'][$key] = [
                    'date' => $dateFormatted,
                    'schedules' => []
                ];
            }
            $machinePerformance[$machineId]['jadwal'][$key]['schedules'][] = $schedule;
        }
        
        // Calculate performance for each machine
        $machinePerformanceData = [];
        foreach ($machinePerformance as $machineId => $data) {
            $totalJadwal = count($data['jadwal']);
            $completedJadwal = 0;
            
            foreach ($data['jadwal'] as $key => $jadwal) {
                $schedulesForJadwal = $jadwal['schedules'];
                $allCompleted = true;
                
                foreach ($schedulesForJadwal as $schedule) {
                    $executions = $schedule->relationLoaded('executions') ? $schedule->executions : $schedule->executions()->get();
                    $hasCompletedExecution = $executions->where('status', 'completed')->isNotEmpty();
                    
                    if (!$hasCompletedExecution) {
                        $allCompleted = false;
                        break;
                    }
                }
                
                if ($allCompleted) {
                    $completedJadwal++;
                }
            }
            
            $machinePerformanceData[] = [
                'machine_id' => $machineId,
                'machine' => $data['machine'],
                'total_jadwal' => $totalJadwal,
                'completed_jadwal' => $completedJadwal,
                'completion_rate' => $totalJadwal > 0 ? round(($completedJadwal / $totalJadwal) * 100, 1) : 0,
            ];
        }
        
        // Sort by completion rate descending
        usort($machinePerformanceData, function($a, $b) {
            return $b['completion_rate'] <=> $a['completion_rate'];
        });
        
        // Paginate machine performance
        $machinePerformanceCollection = collect($machinePerformanceData);
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 12;
        $currentItems = $machinePerformanceCollection->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $machinePerformancePaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $machinePerformanceCollection->count(),
            $perPage,
            $currentPage,
            [
                'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
        $machinePerformancePaginator->appends($request->except('page'));
        
        return view('predictive-maintenance.reporting.performance', compact('monthlyData', 'machinePerformancePaginator', 'startDate', 'endDate'));
    }
}

<?php

namespace App\Http\Controllers\PredictiveMaintenance;

use App\Http\Controllers\Controller;
use App\Models\PredictiveMaintenanceSchedule;
use App\Models\PredictiveMaintenanceExecution;
use App\Models\MachineErp;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

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
        
        $query = PredictiveMaintenanceSchedule::with(['machineErp.machineType', 'standard', 'assignedUser', 'maintenancePoint'])
            ->whereBetween('start_date', [$startDate, $endDate]);
        
        if ($machineId) {
            $query->where('machine_erp_id', $machineId);
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $schedulesRaw = $query->orderBy('start_date', 'asc')->get();
        $machines = MachineErp::all();
        
        // Get all schedule IDs to load executions
        $scheduleIds = $schedulesRaw->pluck('id')->toArray();
        $executionsData = PredictiveMaintenanceExecution::whereIn('schedule_id', $scheduleIds)
            ->with('schedule')
            ->get()
            ->groupBy('schedule_id');
        
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
                    'executions' => [], // Store all executions for this schedule date
                    'assignedUser' => $schedule->assignedUser,
                ];
            }
            $jadwalData[$key]['schedules'][] = $schedule;
            
            // Collect executions for this schedule
            if (isset($executionsData[$schedule->id])) {
                foreach ($executionsData[$schedule->id] as $execution) {
                    $jadwalData[$key]['executions'][] = $execution;
                }
            }
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
        
        $query = PredictiveMaintenanceExecution::with(['schedule.machineErp.machineType', 'schedule.standard', 'performedBy'])
            ->whereBetween('scheduled_date', [$startDate, $endDate]);
        
        if ($machineId) {
            $query->whereHas('schedule', function($q) use ($machineId) {
                $q->where('machine_erp_id', $machineId);
            });
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        // Get ALL executions (not filtered by status) to check if jadwal has completed
        $allExecutionsQuery = PredictiveMaintenanceExecution::with(['schedule.machineErp.machineType', 'schedule.standard', 'performedBy'])
            ->whereBetween('scheduled_date', [$startDate, $endDate]);
        
        if ($machineId) {
            $allExecutionsQuery->whereHas('schedule', function($q) use ($machineId) {
                $q->where('machine_erp_id', $machineId);
            });
        }
        
        $allExecutionsRaw = $allExecutionsQuery->get();
        
        // Group ALL executions by (machine_erp_id, scheduled_date) to check completion status
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
            
            if (!isset($jadwalByMachineAndDate[$key])) {
                $jadwalByMachineAndDate[$key] = [
                    'machine_id' => $machineIdKey,
                    'scheduled_date' => $dateFormatted,
                    'executions' => [],
                    'has_completed' => false,
                ];
            }
            $jadwalByMachineAndDate[$key]['executions'][] = $execution;
            
            if ($execution->status == 'completed') {
                $jadwalByMachineAndDate[$key]['has_completed'] = true;
            }
        }
        
        // Calculate statistics based on jadwal - exclude jadwal that have completed executions
        $totalJadwal = 0;
        $completedJadwal = 0;
        $pendingJadwal = 0;
        $inProgressJadwal = 0;
        
        foreach ($jadwalByMachineAndDate as $key => $jadwal) {
            // If jadwal has completed execution, it's completed (latest status)
            if ($jadwal['has_completed']) {
                $completedJadwal++;
                $totalJadwal++;
                continue;
            }
            
            // Only count jadwal that don't have completed executions
            $executionsForJadwal = $jadwal['executions'];
            $hasInProgress = false;
            $hasPending = false;
            
            foreach ($executionsForJadwal as $execution) {
                if ($execution->status == 'in_progress') {
                    $hasInProgress = true;
                }
                if ($execution->status == 'pending') {
                    $hasPending = true;
                }
            }
            
            if ($hasInProgress) {
                $inProgressJadwal++;
                $totalJadwal++;
            } elseif ($hasPending) {
                $pendingJadwal++;
                $totalJadwal++;
            }
        }
        
        // Filter executions to display: exclude executions from jadwal that have completed status
        $executionsToDisplay = [];
        foreach ($allExecutionsRaw as $execution) {
            $machineIdKey = $execution->schedule->machine_erp_id;
            $scheduledDate = $execution->scheduled_date;
            if (is_string($scheduledDate)) {
                $dateFormatted = $scheduledDate;
            } else {
                $dateFormatted = Carbon::parse($scheduledDate)->format('Y-m-d');
            }
            $key = $machineIdKey . '_' . $dateFormatted;
            
            // Skip executions from jadwal that already have completed execution
            if (isset($jadwalByMachineAndDate[$key]) && $jadwalByMachineAndDate[$key]['has_completed']) {
                // Only show completed executions from this jadwal, not pending/in_progress
                if ($execution->status == 'completed') {
                    $executionsToDisplay[] = $execution;
                }
            } else {
                // Show all executions from jadwal that don't have completed status
                $executionsToDisplay[] = $execution;
            }
        }
        
        // Apply status filter if specified
        $executionsToDisplayCollection = collect($executionsToDisplay);
        if ($status) {
            $executionsToDisplayCollection = $executionsToDisplayCollection->filter(function($execution) use ($status) {
                return $execution->status == $status;
            });
        }
        
        // For cost and duration, use completed executions only
        $completedExecutions = $allExecutionsRaw->filter(function($execution) {
            return $execution->status == 'completed';
        });
        
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
        $executionsCollection = $executionsToDisplayCollection->values();
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
        
        return view('predictive-maintenance.reporting.execution', compact('executions', 'stats', 'machines', 'startDate', 'endDate', 'machineId', 'status'));
    }
    
    /**
     * Generate performance report.
     */
    public function performanceReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfYear()->toDateString());
        $endDate = $request->get('end_date', now()->endOfYear()->toDateString());
        $machineId = $request->get('machine_id');
        
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
        
        // Get machines list for filter
        $machines = MachineErp::with('machineType')->get();
        
        return view('predictive-maintenance.reporting.performance', compact(
            'monthlyData', 
            'machinePerformancePaginator', 
            'startDate', 
            'endDate',
            'machines',
            'machineId'
        ));
    }
    
    /**
     * Get point trends data for a specific machine.
     */
    public function getPointTrendsByMachine(Request $request)
    {
        $machineId = $request->input('machine_id');
        $startDate = $request->input('start_date', now()->startOfYear()->toDateString());
        $endDate = $request->input('end_date', now()->endOfYear()->toDateString());
        
        if (!$machineId) {
            return response()->json(['error' => 'Machine ID is required'], 400);
        }
        
        // Get completed executions for the machine within date range, ordered by date
        $executions = PredictiveMaintenanceExecution::where('status', 'completed')
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->whereNotNull('measured_value')
            ->whereHas('schedule', function($q) use ($machineId) {
                $q->where('machine_erp_id', $machineId);
            })
            ->with(['schedule.maintenancePoint', 'schedule.standard.variants', 'schedule.standard.photos', 'schedule.machineErp'])
            ->orderBy('scheduled_date', 'asc')
            ->get();
        
        // Group by maintenance point
        $pointTrendsData = [];
        foreach ($executions as $execution) {
            if (!$execution->schedule || !$execution->schedule->maintenancePoint) {
                continue;
            }
            
            $pointId = $execution->schedule->maintenance_point_id;
            $pointName = $execution->schedule->maintenancePoint->name ?? $execution->schedule->title ?? 'Unknown Point';
            
            if (!isset($pointTrendsData[$pointId])) {
                $standard = $execution->schedule->standard;
                $standardName = $standard->name ?? null;
                $standardReference = $standard->reference_name ?? $standard->reference_code ?? null;
                $standardClass = $standard->class ?? null;
                
                // Build standard display text
                $standardDisplay = $standardName;
                if ($standardClass) {
                    $standardDisplay = ($standardDisplay ? $standardDisplay : '') . ($standardDisplay ? ', Class: ' : 'Class: ') . $standardClass;
                }
                
                // Load variants if not already loaded
                $variants = [];
                if ($standard) {
                    $standardVariants = $standard->relationLoaded('variants') 
                        ? $standard->variants 
                        : $standard->variants()->orderBy('order', 'asc')->get();
                    
                    foreach ($standardVariants as $variant) {
                        $variants[] = [
                            'name' => $variant->name,
                            'min_value' => $variant->min_value,
                            'max_value' => $variant->max_value,
                            'color' => $variant->color,
                            'order' => $variant->order,
                        ];
                    }
                }
                
                // Get photo URL - prioritize photos relationship, then fallback to photo field
                $standardPhotoUrl = null;
                $photoPath = null;
                
                if ($standard) {
                    // Prioritize photos relationship (many-to-many)
                    if ($standard->relationLoaded('photos') && $standard->photos && $standard->photos->count() > 0) {
                        $photoPath = $standard->photos->first()->photo_path;
                    } elseif ($standard->photo) {
                        // Fallback to legacy photo field
                        $photoPath = $standard->photo;
                    }
                    
                    // Generate URL if photo path exists
                    if ($photoPath) {
                        $actualPath = $photoPath;
                        
                        // Check if file exists, try webp extension if not
                        if (Storage::disk('public')->exists($photoPath)) {
                            $actualPath = $photoPath;
                        } else {
                            $pathInfo = pathinfo($photoPath);
                            $webpPath = ($pathInfo['dirname'] !== '.' ? $pathInfo['dirname'] . '/' : '') . $pathInfo['filename'] . '.webp';
                            if (Storage::disk('public')->exists($webpPath)) {
                                $actualPath = $webpPath;
                            }
                        }
                        
                        // Generate URL based on path format
                        if (strpos($actualPath, 'images/') === 0) {
                            // Old format: images/ISO 10816.jpg -> use asset() for public folder
                            $standardPhotoUrl = asset($actualPath);
                        } elseif (strpos($actualPath, 'standards/') === 0 || strpos($actualPath, 'maintenance-points/') === 0) {
                            // New format: standards/... or maintenance-points/... -> use public-storage
                            $standardPhotoUrl = asset('public-storage/' . $actualPath);
                        } else {
                            // Default: try public-storage
                            $standardPhotoUrl = asset('public-storage/' . $actualPath);
                        }
                    }
                }
                
                $pointTrendsData[$pointId] = [
                    'point_id' => $pointId,
                    'point_name' => $pointName,
                    'machine_id' => $execution->schedule->machine_erp_id,
                    'machine_name' => $execution->schedule->machineErp->idMachine ?? '-',
                    'standard_name' => $standardName,
                    'standard_reference' => $standardReference,
                    'standard_class' => $standardClass,
                    'standard_display' => $standardDisplay,
                    'standard_unit' => $standard->unit ?? '-',
                    'standard_min' => $standard->min_value ?? null,
                    'standard_max' => $standard->max_value ?? null,
                    'standard_target' => $standard->target_value ?? null,
                    'standard_photo_url' => $standardPhotoUrl,
                    'variants' => $variants,
                    'dates' => [],
                    'values' => [],
                ];
            }
            
            $dateFormatted = $execution->scheduled_date->format('Y-m-d');
            $pointTrendsData[$pointId]['dates'][] = $dateFormatted;
            $pointTrendsData[$pointId]['values'][] = (float)$execution->measured_value;
        }
        
        // Convert to array and format dates for display
        $result = [];
        foreach ($pointTrendsData as $pointId => $pointData) {
            $result[] = [
                'point_id' => $pointData['point_id'],
                'point_name' => $pointData['point_name'],
                'machine_id' => $pointData['machine_id'],
                'machine_name' => $pointData['machine_name'],
                'standard_name' => $pointData['standard_name'],
                'standard_reference' => $pointData['standard_reference'],
                'standard_class' => $pointData['standard_class'],
                'standard_display' => $pointData['standard_display'],
                'standard_unit' => $pointData['standard_unit'],
                'standard_min' => $pointData['standard_min'],
                'standard_max' => $pointData['standard_max'],
                'standard_target' => $pointData['standard_target'],
                'standard_photo_url' => $pointData['standard_photo_url'] ?? null,
                'variants' => $pointData['variants'],
                'dates' => $pointData['dates'], // Already sorted by execution date (asc)
                'dates_display' => array_map(function($date) {
                    return Carbon::parse($date)->format('d/m/Y');
                }, $pointData['dates']),
                'values' => $pointData['values'],
            ];
        }
        
        return response()->json(['point_trends' => $result]);
    }
}

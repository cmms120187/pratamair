<?php

namespace App\Http\Controllers\PredictiveMaintenance;

use App\Http\Controllers\Controller;
use App\Models\PredictiveMaintenanceSchedule;
use App\Models\PredictiveMaintenanceExecution;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    /**
     * Display monitoring dashboard.
     */
    public function index(Request $request)
    {
        // Get filter parameters (default: current month and year)
        $filterMonth = $request->get('month', now()->month);
        $filterYear = $request->get('year', now()->year);
        
        // Calculate start and end date for the selected month
        $startDate = Carbon::create($filterYear, $filterMonth, 1)->startOfMonth();
        $endDate = Carbon::create($filterYear, $filterMonth, 1)->endOfMonth();
        $today = now()->toDateString();
        
        // Get all active schedules for the selected month
        $schedulesForMonth = PredictiveMaintenanceSchedule::where('status', 'active')
            ->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with(['machineErp', 'standard', 'assignedUser', 'executions'])
            ->get();
        
        // Get unique machine IDs that have schedules in this month
        $uniqueMachineIds = $schedulesForMonth->pluck('machine_erp_id')->unique();
        $totalMesinTerjadwal = $uniqueMachineIds->count();
        
        // Upcoming schedules (within selected month) - grouped by (machine_erp_id, date) to show jadwal
        $upcomingSchedulesRaw = PredictiveMaintenanceSchedule::where('status', 'active')
            ->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('start_date', '>=', $today)
            ->with(['machineErp', 'assignedUser'])
            ->orderBy('start_date', 'asc')
            ->get();
        
        // Group by (machine_erp_id, date) to get unique jadwal
        $upcomingJadwal = [];
        foreach ($upcomingSchedulesRaw as $schedule) {
            $machineId = $schedule->machine_erp_id;
            $date = $schedule->start_date;
            if (is_string($date)) {
                $dateFormatted = $date;
            } elseif ($date instanceof Carbon) {
                $dateFormatted = $date->format('Y-m-d');
            } else {
                $dateFormatted = $date->format('Y-m-d');
            }
            $key = $machineId . '_' . $dateFormatted;
            
            if (!isset($upcomingJadwal[$key])) {
                $upcomingJadwal[$key] = [
                    'machine_id' => $machineId,
                    'machine' => $schedule->machineErp,
                    'start_date' => $dateFormatted,
                    'assignedUser' => $schedule->assignedUser,
                    'schedule' => $schedule,
                ];
            }
        }
        
        // Sort by date and limit to 10
        usort($upcomingJadwal, function($a, $b) {
            return strcmp($a['start_date'], $b['start_date']);
        });
        $upcomingSchedules = collect(array_slice($upcomingJadwal, 0, 10))->map(function($jadwal) {
            return (object) [
                'machine_id' => $jadwal['machine_id'],
                'machine' => $jadwal['machine'],
                'start_date' => Carbon::parse($jadwal['start_date']),
                'assignedUser' => $jadwal['assignedUser'],
                'schedule' => $jadwal['schedule'],
            ];
        });
        
        // Today's executions (within selected month)
        $todayExecutions = PredictiveMaintenanceExecution::whereDate('scheduled_date', $today)
            ->whereHas('schedule', function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->with(['schedule.machineErp', 'schedule.standard', 'performedBy'])
            ->orderBy('scheduled_date', 'asc')
            ->get();
        
        // Overdue executions (within selected month)
        $overdueExecutions = PredictiveMaintenanceExecution::where('status', 'pending')
            ->where('scheduled_date', '<', $today)
            ->whereHas('schedule', function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->with(['schedule.machineErp', 'schedule.standard', 'performedBy'])
            ->orderBy('scheduled_date', 'asc')
            ->get();
        
        // In progress executions (within selected month)
        $inProgressExecutions = PredictiveMaintenanceExecution::where('status', 'in_progress')
            ->whereHas('schedule', function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->with(['schedule.machineErp', 'schedule.standard', 'performedBy'])
            ->orderBy('actual_start_time', 'desc')
            ->get();
        
        // Statistics for selected month
        // Calculate total jadwal (machine_erp_id + date combinations)
        $jadwalCombinations = [];
        foreach ($schedulesForMonth as $schedule) {
            $machineId = $schedule->machine_erp_id;
            $date = $schedule->start_date;
            if (is_string($date)) {
                $dateFormatted = $date;
            } elseif ($date instanceof Carbon) {
                $dateFormatted = $date->format('Y-m-d');
            } else {
                $dateFormatted = $date->format('Y-m-d');
            }
            $key = $machineId . '_' . $dateFormatted;
            if (!in_array($key, $jadwalCombinations)) {
                $jadwalCombinations[] = $key;
            }
        }
        $totalJadwal = count($jadwalCombinations);
        
        // Calculate status overview per today (all schedules in selected month up to today)
        $schedulesUpToToday = $schedulesForMonth->filter(function($schedule) use ($today) {
            $scheduleDate = is_string($schedule->start_date) ? $schedule->start_date : $schedule->start_date->format('Y-m-d');
            return $scheduleDate <= $today;
        });
        
        // Group by (machine_erp_id, date) for status today
        $jadwalByMachineAndDateToday = [];
        foreach ($schedulesUpToToday as $schedule) {
            $machineId = $schedule->machine_erp_id;
            $date = $schedule->start_date;
            if (is_string($date)) {
                $dateFormatted = $date;
            } elseif ($date instanceof Carbon) {
                $dateFormatted = $date->format('Y-m-d');
            } else {
                $dateFormatted = $date->format('Y-m-d');
            }
            $key = $machineId . '_' . $dateFormatted;
            
            if (!isset($jadwalByMachineAndDateToday[$key])) {
                $jadwalByMachineAndDateToday[$key] = [
                    'machine_id' => $machineId,
                    'date' => $dateFormatted,
                    'schedules' => []
                ];
            }
            $jadwalByMachineAndDateToday[$key]['schedules'][] = $schedule;
        }
        
        // Calculate status for today
        $completedJadwalToday = 0;
        $inProgressJadwalToday = 0;
        $pendingJadwalToday = 0;
        $overdueJadwalToday = 0;
        
        foreach ($jadwalByMachineAndDateToday as $key => $jadwal) {
            $schedulesForJadwal = $jadwal['schedules'];
            
            $allCompleted = true;
            $hasInProgress = false;
            $hasPending = false;
            $isOverdue = false;
            
            foreach ($schedulesForJadwal as $schedule) {
                $scheduleDateObj = is_string($schedule->start_date) ? Carbon::parse($schedule->start_date) : $schedule->start_date;
                $isPast = $scheduleDateObj->format('Y-m-d') < $today;
                
                $executions = $schedule->relationLoaded('executions') ? $schedule->executions : $schedule->executions()->get();
                $hasCompletedExecution = $executions->where('status', 'completed')->isNotEmpty();
                
                if (!$hasCompletedExecution) {
                    $allCompleted = false;
                    
                    $hasInProgressExecution = $executions->where('status', 'in_progress')->isNotEmpty();
                    
                    if ($hasInProgressExecution) {
                        $hasInProgress = true;
                    } else {
                        $hasPending = true;
                        if ($isPast) {
                            $isOverdue = true;
                        }
                    }
                }
            }
            
            if ($allCompleted) {
                $completedJadwalToday++;
            } elseif ($hasInProgress) {
                $inProgressJadwalToday++;
            } elseif ($isOverdue) {
                $overdueJadwalToday++;
            } else {
                $pendingJadwalToday++;
            }
        }
        
        $statusToday = [
            'completed' => $completedJadwalToday,
            'in_progress' => $inProgressJadwalToday,
            'pending' => $pendingJadwalToday,
            'overdue' => $overdueJadwalToday,
            'total' => count($jadwalByMachineAndDateToday),
        ];
        
        // Status overview per month - based on jadwal (machine_erp_id + date combination)
        $jadwalByMachineAndDate = [];
        foreach ($schedulesForMonth as $schedule) {
            $machineId = $schedule->machine_erp_id;
            $date = $schedule->start_date;
            if (is_string($date)) {
                $dateFormatted = $date;
            } elseif ($date instanceof Carbon) {
                $dateFormatted = $date->format('Y-m-d');
            } else {
                $dateFormatted = $date->format('Y-m-d');
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
        
        $completedJadwalMonth = 0;
        $inProgressJadwalMonth = 0;
        $pendingJadwalMonth = 0;
        $overdueJadwalMonth = 0;
        
        foreach ($jadwalByMachineAndDate as $key => $jadwal) {
            $schedulesForJadwal = $jadwal['schedules'];
            
            $allCompleted = true;
            $hasInProgress = false;
            $hasPending = false;
            $isOverdue = false;
            
            foreach ($schedulesForJadwal as $schedule) {
                $scheduleDateObj = is_string($schedule->start_date) ? Carbon::parse($schedule->start_date) : $schedule->start_date;
                $isPast = $scheduleDateObj->format('Y-m-d') < $today;
                
                $executions = $schedule->relationLoaded('executions') ? $schedule->executions : $schedule->executions()->get();
                $hasCompletedExecution = $executions->where('status', 'completed')->isNotEmpty();
                
                if (!$hasCompletedExecution) {
                    $allCompleted = false;
                    
                    $hasInProgressExecution = $executions->where('status', 'in_progress')->isNotEmpty();
                    
                    if ($hasInProgressExecution) {
                        $hasInProgress = true;
                    } else {
                        $hasPending = true;
                        if ($isPast) {
                            $isOverdue = true;
                        }
                    }
                }
            }
            
            if ($allCompleted) {
                $completedJadwalMonth++;
            } elseif ($hasInProgress) {
                $inProgressJadwalMonth++;
            } elseif ($isOverdue) {
                $overdueJadwalMonth++;
            } else {
                $pendingJadwalMonth++;
            }
        }
        
        $statusMonth = [
            'completed' => $completedJadwalMonth,
            'in_progress' => $inProgressJadwalMonth,
            'pending' => $pendingJadwalMonth,
            'overdue' => $overdueJadwalMonth,
            'total' => count($jadwalByMachineAndDate),
        ];
        
        // Monthly completion trend
        $monthlyData = $this->getMonthlyCompletionData($filterMonth, $filterYear);
        
        // Statistics
        $stats = [
            'total_schedules' => $totalJadwal,
            'total_mesin_terjadwal' => $totalMesinTerjadwal,
            'pending' => $statusMonth['pending'],
            'in_progress' => $statusMonth['in_progress'],
            'completed' => $statusMonth['completed'],
            'overdue' => $statusMonth['overdue'],
        ];
        
        return view('predictive-maintenance.monitoring.index', compact(
            'stats',
            'upcomingSchedules',
            'todayExecutions',
            'overdueExecutions',
            'inProgressExecutions',
            'statusToday',
            'statusMonth',
            'monthlyData',
            'filterMonth',
            'filterYear'
        ));
    }
    
    private function getMonthlyCompletionData($filterMonth, $filterYear)
    {
        $startDate = Carbon::create($filterYear, $filterMonth, 1)->startOfMonth();
        $endDate = Carbon::create($filterYear, $filterMonth, 1)->endOfMonth();
        
        $schedules = PredictiveMaintenanceSchedule::where('status', 'active')
            ->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with('executions')
            ->get();
        
        // Group by (machine_erp_id, date) to get unique jadwal
        $jadwalByMachineAndDate = [];
        foreach ($schedules as $schedule) {
            $machineId = $schedule->machine_erp_id;
            $date = $schedule->start_date;
            if (is_string($date)) {
                $dateFormatted = $date;
            } elseif ($date instanceof Carbon) {
                $dateFormatted = $date->format('Y-m-d');
            } else {
                $dateFormatted = $date->format('Y-m-d');
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
        
        return [
            'total' => $totalJadwal,
            'completed' => $completedJadwal,
            'completion_rate' => $totalJadwal > 0 ? round(($completedJadwal / $totalJadwal) * 100, 1) : 0,
        ];
    }
}

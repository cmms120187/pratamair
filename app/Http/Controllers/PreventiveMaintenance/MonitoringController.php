<?php

namespace App\Http\Controllers\PreventiveMaintenance;

use App\Http\Controllers\Controller;
use App\Models\PreventiveMaintenanceSchedule;
use App\Models\PreventiveMaintenanceExecution;
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
        
        // Get all active schedules for the selected month - now using MachineErp
        $schedulesForMonth = PreventiveMaintenanceSchedule::where('status', 'active')
            ->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with(['machineErp', 'assignedUser', 'executions'])
            ->get();
        
        // Get unique machine IDs that have schedules in this month
        $uniqueMachineIds = $schedulesForMonth->pluck('machine_erp_id')->unique();
        $totalMesinTerjadwal = $uniqueMachineIds->count();
        
        // Upcoming schedules (within selected month) - grouped by (machine_erp_id, date) to show jadwal, not individual points
        $upcomingSchedulesRaw = PreventiveMaintenanceSchedule::where('status', 'active')
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
                    'schedule' => $schedule, // Keep one schedule instance for reference
                ];
            }
        }
        
        // Sort by date and limit to 10
        usort($upcomingJadwal, function($a, $b) {
            return strcmp($a['start_date'], $b['start_date']);
        });
        $upcomingJadwal = array_slice($upcomingJadwal, 0, 10);
        
        // Convert to collection-like structure for view compatibility
        $upcomingSchedules = collect($upcomingJadwal)->map(function($jadwal) {
            $schedule = $jadwal['schedule'];
            return (object) [
                'machine_id' => $jadwal['machine_id'],
                'machine' => $jadwal['machine'],
                'start_date' => Carbon::parse($jadwal['start_date']),
                'assignedUser' => $jadwal['assignedUser'],
                'schedule' => $schedule, // Keep schedule for accessing type_id via machine relationship
            ];
        });
        
        // Today's executions (within selected month)
        $todayExecutions = PreventiveMaintenanceExecution::whereDate('scheduled_date', $today)
            ->whereHas('schedule', function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->with(['schedule.machineErp', 'performedBy'])
            ->orderBy('scheduled_date', 'asc')
            ->get();
        
        // Overdue executions (within selected month)
        $overdueExecutions = PreventiveMaintenanceExecution::where('status', 'pending')
            ->where('scheduled_date', '<', $today)
            ->whereHas('schedule', function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->with(['schedule.machineErp', 'performedBy'])
            ->orderBy('scheduled_date', 'asc')
            ->get();
        
        // In progress executions (within selected month)
        $inProgressExecutions = PreventiveMaintenanceExecution::where('status', 'in_progress')
            ->whereHas('schedule', function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->with(['schedule.machine', 'performedBy'])
            ->orderBy('actual_start_time', 'desc')
            ->get();
        
        // Statistics for selected month
        // Calculate total jadwal (machine_erp_id + date combinations) from active schedules
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
        
        // Pending executions in selected month
        $pendingExecutions = PreventiveMaintenanceExecution::where('status', 'pending')
            ->whereHas('schedule', function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->count();
        
        // Completed executions in selected month
        $completedThisMonth = PreventiveMaintenanceExecution::where('status', 'completed')
            ->whereHas('schedule', function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->count();
        
        // Calculate completion rate for selected month
        $completionRate = $this->calculateCompletionRate($startDate, $endDate);
        
        // Status overview per today - jadwal PM bulan ini sampai tanggal hari ini (dari tanggal 1 sampai hari ini)
        // Get all schedules in selected month that have start_date <= today
        $schedulesUpToToday = $schedulesForMonth->filter(function($schedule) use ($today) {
            $scheduleDate = $schedule->start_date;
            if (is_string($scheduleDate)) {
                $scheduleDateObj = Carbon::parse($scheduleDate);
            } elseif ($scheduleDate instanceof Carbon) {
                $scheduleDateObj = $scheduleDate;
            } else {
                $scheduleDateObj = $scheduleDate;
            }
            return $scheduleDateObj->format('Y-m-d') <= $today;
        });
        
        // Group by machine_erp_id and start_date (setiap kombinasi mesin+tanggal = 1 jadwal)
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
        
        $completedJadwalToday = 0;
        $inProgressJadwalToday = 0;
        $pendingJadwalToday = 0;
        $overdueJadwalToday = 0;
        
        foreach ($jadwalByMachineAndDateToday as $key => $jadwal) {
            $schedulesForJadwal = $jadwal['schedules'];
            $date = $jadwal['date'];
            $scheduleDateObj = Carbon::parse($date);
            $isPast = $scheduleDateObj->format('Y-m-d') < $today;
            
            // Check if all schedules for this jadwal (machine + date) are completed
            $allCompleted = true;
            $hasInProgress = false;
            $hasPending = false;
            
            foreach ($schedulesForJadwal as $schedule) {
                // Check if this schedule has completed execution
                $executions = $schedule->relationLoaded('executions') ? $schedule->executions : $schedule->executions()->get();
                $hasCompletedExecution = $executions->where('status', 'completed')->isNotEmpty();
                
                if (!$hasCompletedExecution) {
                    $allCompleted = false;
                    
                    // Check if has in_progress
                    $hasInProgressExecution = $executions->where('status', 'in_progress')->isNotEmpty();
                    
                    if ($hasInProgressExecution) {
                        $hasInProgress = true;
                    } else {
                        $hasPending = true;
                    }
                }
            }
            
            // Categorize this jadwal (machine + date)
            if ($allCompleted) {
                $completedJadwalToday++;
            } elseif ($hasInProgress) {
                $inProgressJadwalToday++;
            } elseif ($isPast && $hasPending) {
                $overdueJadwalToday++;
            } else {
                $pendingJadwalToday++;
            }
        }
        
        $totalJadwalToday = count($jadwalByMachineAndDateToday);
        
        $statusToday = [
            'completed' => $completedJadwalToday,
            'in_progress' => $inProgressJadwalToday,
            'pending' => $pendingJadwalToday,
            'overdue' => $overdueJadwalToday,
            'total' => $totalJadwalToday,
        ];
        
        // Status overview per month - based on jadwal (machine_erp_id + date combination)
        // Group schedules by machine_erp_id and start_date (setiap kombinasi mesin+tanggal = 1 jadwal)
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
            $date = $jadwal['date'];
            $scheduleDateObj = Carbon::parse($date);
            $isPast = $scheduleDateObj->format('Y-m-d') < $today;
            
            // Check if all schedules for this jadwal (machine + date) are completed
            $allCompleted = true;
            $hasInProgress = false;
            $hasPending = false;
            
            foreach ($schedulesForJadwal as $schedule) {
                // Check if this schedule has completed execution
                $executions = $schedule->relationLoaded('executions') ? $schedule->executions : $schedule->executions()->get();
                $hasCompletedExecution = $executions->where('status', 'completed')->isNotEmpty();
                
                if (!$hasCompletedExecution) {
                    $allCompleted = false;
                    
                    // Check if has in_progress
                    $hasInProgressExecution = $executions->where('status', 'in_progress')->isNotEmpty();
                    
                    if ($hasInProgressExecution) {
                        $hasInProgress = true;
                    } else {
                        $hasPending = true;
                    }
                }
            }
            
            // Categorize this jadwal (machine + date)
            if ($allCompleted) {
                $completedJadwalMonth++;
            } elseif ($hasInProgress) {
                $inProgressJadwalMonth++;
            } elseif ($isPast && $hasPending) {
                $overdueJadwalMonth++;
            } else {
                $pendingJadwalMonth++;
            }
        }
        
        $totalJadwalMonth = count($jadwalByMachineAndDate);
        
        $statusMonth = [
            'completed' => $completedJadwalMonth,
            'in_progress' => $inProgressJadwalMonth,
            'pending' => $pendingJadwalMonth,
            'overdue' => $overdueJadwalMonth,
            'total' => $totalJadwalMonth,
        ];
        
        $stats = [
            'total_schedules' => $totalJadwal, // Total jumlah tanggal/jadwal
            'total_mesin_terjadwal' => $totalMesinTerjadwal,
            'pending_executions' => $pendingExecutions,
            'completed_this_month' => $completedThisMonth,
            'overdue_count' => $overdueExecutions->count(),
            'completion_rate' => $completionRate,
        ];
        
        // Chart data for monthly completion
        $monthlyData = $this->getMonthlyCompletionData();
        
        return view('preventive-maintenance.monitoring.index', compact(
            'upcomingSchedules',
            'todayExecutions',
            'overdueExecutions',
            'inProgressExecutions',
            'stats',
            'monthlyData',
            'statusToday',
            'statusMonth',
            'filterMonth',
            'filterYear'
        ));
    }
    
    private function calculateCompletionRate($startDate = null, $endDate = null)
    {
        if (!$startDate) {
            $startDate = now()->startOfMonth();
        }
        if (!$endDate) {
            $endDate = now()->endOfMonth();
        }
        
        $total = PreventiveMaintenanceExecution::whereHas('schedule', function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->count();
        
        if ($total == 0) return 0;
        
        $completed = PreventiveMaintenanceExecution::where('status', 'completed')
            ->whereHas('schedule', function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()]);
            })
            ->count();
        
        return round(($completed / $total) * 100, 2);
    }
    
    private function getMonthlyCompletionData()
    {
        $months = [];
        $completed = [];
        $pending = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            $completed[] = PreventiveMaintenanceExecution::where('status', 'completed')
                ->whereMonth('scheduled_date', $date->month)
                ->whereYear('scheduled_date', $date->year)
                ->count();
            
            $pending[] = PreventiveMaintenanceExecution::where('status', 'pending')
                ->whereMonth('scheduled_date', $date->month)
                ->whereYear('scheduled_date', $date->year)
                ->count();
        }
        
        return [
            'months' => $months,
            'completed' => $completed,
            'pending' => $pending,
        ];
    }
}

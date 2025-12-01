<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plant;
use App\Models\Process;
use App\Models\Line;
use App\Models\Room;
use App\Models\MachineType;
use App\Models\Brand;
use App\Models\Model;
use App\Models\Machine;
use App\Models\Group;
use App\Models\Part;
use App\Models\Problem;
use App\Models\ProblemMm;
use App\Models\Reason;
use App\Models\Action;
use App\Models\Downtime;
use App\Models\DowntimeErp;
use App\Models\User;
use App\Models\PreventiveMaintenanceSchedule;
use App\Models\PreventiveMaintenanceExecution;
use App\Models\PredictiveMaintenanceSchedule;
use App\Models\PredictiveMaintenanceExecution;
use App\Models\WorkOrder;
use App\Models\Standard;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get data source from request or session, default to 'downtime'
        $dataSource = $request->input('data_source', session('dashboard_data_source', 'downtime'));
        session(['dashboard_data_source' => $dataSource]);
        
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        // ========== THIS MONTH STATISTICS ==========
        
        if ($dataSource === 'downtime_erp') {
            // Use DowntimeErp model
            $stats = $this->getDowntimeErpStats($currentYear, $currentMonth);
        } else {
            // Use Downtime model
            $stats = $this->getDowntimeStats($currentYear, $currentMonth);
        }
        
        // ========== PREVENTIVE MAINTENANCE STATISTICS ==========
        $pmSchedulesThisMonth = PreventiveMaintenanceSchedule::whereYear('start_date', $currentYear)
            ->whereMonth('start_date', $currentMonth)
            ->count();
        
        $pmSchedulesPending = PreventiveMaintenanceSchedule::whereYear('start_date', $currentYear)
            ->whereMonth('start_date', $currentMonth)
            ->where('status', 'active')
            ->whereDoesntHave('executions')
            ->count();
        
        $pmSchedulesCompleted = PreventiveMaintenanceSchedule::whereYear('start_date', $currentYear)
            ->whereMonth('start_date', $currentMonth)
            ->whereHas('executions', function($q) {
                $q->where('status', 'completed');
            })
            ->count();
        
        $pmSchedulesInProgress = PreventiveMaintenanceSchedule::whereYear('start_date', $currentYear)
            ->whereMonth('start_date', $currentMonth)
            ->whereHas('executions', function($q) {
                $q->where('status', 'in_progress');
            })
            ->count();
        
        $pmCompletionRate = $pmSchedulesThisMonth > 0 ? ($pmSchedulesCompleted / $pmSchedulesThisMonth) * 100 : 0;

        // ========== PREDICTIVE MAINTENANCE STATISTICS ==========
        $pdmSchedulesThisMonth = PredictiveMaintenanceSchedule::whereYear('start_date', $currentYear)
            ->whereMonth('start_date', $currentMonth)
            ->count();
        
        $pdmSchedulesPending = PredictiveMaintenanceSchedule::whereYear('start_date', $currentYear)
            ->whereMonth('start_date', $currentMonth)
            ->where('status', 'active')
            ->whereDoesntHave('executions')
            ->count();
        
        $pdmSchedulesCompleted = PredictiveMaintenanceSchedule::whereYear('start_date', $currentYear)
            ->whereMonth('start_date', $currentMonth)
            ->whereHas('executions', function($q) {
                $q->where('status', 'completed');
            })
            ->count();
        
        $pdmCompletionRate = $pdmSchedulesThisMonth > 0 ? ($pdmSchedulesCompleted / $pdmSchedulesThisMonth) * 100 : 0;

        // ========== WORK ORDERS STATISTICS ==========
        $workOrdersTotal = WorkOrder::count();
        $workOrdersPending = WorkOrder::where('status', 'pending')->count();
        $workOrdersInProgress = WorkOrder::where('status', 'in_progress')->count();
        $workOrdersCompleted = WorkOrder::where('status', 'completed')->count();
        $workOrdersThisMonth = WorkOrder::whereYear('order_date', $currentYear)
            ->whereMonth('order_date', $currentMonth)
            ->count();

        // ========== MACHINES STATISTICS ==========
        $totalMachines = Machine::count();
        $machinesWithDowntime = $dataSource === 'downtime_erp' 
            ? DowntimeErp::whereYear('date', $currentYear)
                ->whereMonth('date', $currentMonth)
                ->whereNotNull('idMachine')
                ->where('idMachine', '!=', '')
                ->distinct('idMachine')
                ->count('idMachine')
            : Downtime::whereYear('date', $currentYear)
                ->whereMonth('date', $currentMonth)
                ->whereNotNull('machine_id')
                ->distinct('machine_id')
                ->count('machine_id');
        
        $machinesWithPM = PreventiveMaintenanceSchedule::whereYear('start_date', $currentYear)
            ->whereMonth('start_date', $currentMonth)
            ->distinct('machine_erp_id')
            ->count('machine_erp_id');

        // ========== USERS STATISTICS ==========
        $totalUsers = User::count();
        $totalMechanics = User::whereIn('role', ['mekanik', 'team_leader', 'group_leader'])->count();
        
        // Active mechanics are those who have downtime records this month
        if ($dataSource === 'downtime_erp') {
            $activeMechanicNames = DowntimeErp::whereYear('date', $currentYear)
                ->whereMonth('date', $currentMonth)
                ->whereNotNull('nameMekanik')
                ->where('nameMekanik', '!=', '')
                ->distinct()
                ->pluck('nameMekanik')
                ->toArray();
            
            $activeMechanics = User::whereIn('role', ['mekanik', 'team_leader', 'group_leader'])
                ->whereIn('name', $activeMechanicNames)
                ->count();
        } else {
            $activeMechanics = Downtime::whereYear('date', $currentYear)
                ->whereMonth('date', $currentMonth)
                ->whereNotNull('mekanik_id')
                ->distinct('mekanik_id')
                ->count('mekanik_id');
        }

        // ========== STANDARDS STATISTICS ==========
        $totalStandards = Standard::count();
        $activeStandards = Standard::where('status', 'active')->count();

        // ========== RECENT WORK ORDERS ==========
        $recentWorkOrders = WorkOrder::orderBy('order_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', array_merge($stats, [
            'dataSource' => $dataSource,
            'currentMonth' => $currentMonth,
            'currentYear' => $currentYear,
            'daysInMonth' => now()->daysInMonth,
            // PM Stats
            'pmSchedulesThisMonth' => $pmSchedulesThisMonth,
            'pmSchedulesPending' => $pmSchedulesPending,
            'pmSchedulesCompleted' => $pmSchedulesCompleted,
            'pmSchedulesInProgress' => $pmSchedulesInProgress,
            'pmCompletionRate' => $pmCompletionRate,
            // PdM Stats
            'pdmSchedulesThisMonth' => $pdmSchedulesThisMonth,
            'pdmSchedulesPending' => $pdmSchedulesPending,
            'pdmSchedulesCompleted' => $pdmSchedulesCompleted,
            'pdmCompletionRate' => $pdmCompletionRate,
            // Work Orders Stats
            'workOrdersTotal' => $workOrdersTotal,
            'workOrdersPending' => $workOrdersPending,
            'workOrdersInProgress' => $workOrdersInProgress,
            'workOrdersCompleted' => $workOrdersCompleted,
            'workOrdersThisMonth' => $workOrdersThisMonth,
            'recentWorkOrders' => $recentWorkOrders,
            // Machines Stats
            'totalMachines' => $totalMachines,
            'machinesWithDowntime' => $machinesWithDowntime,
            'machinesWithPM' => $machinesWithPM,
            // Users Stats
            'totalUsers' => $totalUsers,
            'totalMechanics' => $totalMechanics,
            'activeMechanics' => $activeMechanics,
            // Standards Stats
            'totalStandards' => $totalStandards,
            'activeStandards' => $activeStandards
        ]));
    }
    
    /**
     * Get statistics from DowntimeErp table
     */
    private function getDowntimeErpStats($currentYear, $currentMonth)
    {
        // Total downtime count this month
        $monthDowntimeCount = DowntimeErp::whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->count();
        
        // Total downtime duration this month
        $monthDowntime = DowntimeErp::whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->get()
            ->sum(function($item) {
                return (float) ($item->duration ?? 0);
            });
        
        // Average downtime duration per incident
        $avgDowntimeDuration = $monthDowntimeCount > 0 ? $monthDowntime / $monthDowntimeCount : 0;
        
        // Average downtime per day (total duration / days in month)
        $daysInMonth = now()->daysInMonth;
        $avgDowntimePerDay = $monthDowntime / $daysInMonth;
        
        // Most problematic machine (by total duration)
        $mostProblematicMachine = DowntimeErp::select(
                'idMachine',
                DB::raw('MAX(typeMachine) as typeMachine'),
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->whereNotNull('idMachine')
            ->where('idMachine', '!=', '')
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->groupBy('idMachine')
            ->orderBy('total_duration', 'desc')
            ->first();
        
        // Longest single downtime this month
        $longestDowntime = DowntimeErp::whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->orderByRaw('CAST(duration AS DECIMAL(10,2)) DESC')
            ->first();
        
        // Top 10 Machine dengan Akumulasi Downtime Tertinggi (This Month)
        $topMachines = DowntimeErp::select(
                'idMachine',
                DB::raw('MAX(typeMachine) as typeMachine'),
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->whereNotNull('idMachine')
            ->where('idMachine', '!=', '')
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->groupBy('idMachine')
            ->orderBy('total_duration', 'desc')
            ->limit(10)
            ->get();

        // Top 5 MTTR (Mean Time To Repair) Tertinggi (This Month)
        $topMTTR = DowntimeErp::select(
                'idMachine',
                DB::raw('MAX(typeMachine) as typeMachine'),
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                DB::raw('COUNT(*) as downtime_count'),
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) / COUNT(*) as mttr')
            )
            ->whereNotNull('idMachine')
            ->where('idMachine', '!=', '')
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->havingRaw('COUNT(*) > 0')
            ->groupBy('idMachine')
            ->orderBy('mttr', 'desc')
            ->limit(5)
            ->get();

        // Top 5 Plant dengan Akumulasi Downtime Tertinggi (This Month)
        $topPlants = DowntimeErp::select(
                'plant',
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->whereNotNull('plant')
            ->where('plant', '!=', '')
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->groupBy('plant')
            ->orderBy('total_duration', 'desc')
            ->limit(5)
            ->get();
        
        // Top 5 Most Common Problems (This Month)
        $topProblems = DowntimeErp::select(
                'problemDowntime',
                DB::raw('COUNT(*) as problem_count'),
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration')
            )
            ->whereNotNull('problemDowntime')
            ->where('problemDowntime', '!=', '')
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->groupBy('problemDowntime')
            ->orderBy('problem_count', 'desc')
            ->limit(5)
            ->get();

        // Top 5 Most Active Mekanik (This Month)
        $topMekanik = DowntimeErp::select(
                'nameMekanik',
                DB::raw('COUNT(*) as downtime_count'),
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration')
            )
            ->whereNotNull('nameMekanik')
            ->where('nameMekanik', '!=', '')
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->groupBy('nameMekanik')
            ->orderBy('downtime_count', 'desc')
            ->limit(5)
            ->get();

        // Top 5 Lines with Most Downtime (This Month)
        $topLines = DowntimeErp::select(
                'line',
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->whereNotNull('line')
            ->where('line', '!=', '')
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->groupBy('line')
            ->orderBy('total_duration', 'desc')
            ->limit(5)
            ->get();

        // Downtime Trend per Day (This Month)
        $downtimeTrend = DowntimeErp::select(
                DB::raw('DATE(date) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration')
            )
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->groupBy(DB::raw('DATE(date)'))
            ->orderBy('date', 'asc')
            ->get();
        
        // Recent Downtime ERPs (10 terakhir) - This Month
        $recentDowntimeErps = DowntimeErp::whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return [
            'monthDowntimeCount' => $monthDowntimeCount,
            'monthDowntime' => $monthDowntime,
            'avgDowntimeDuration' => $avgDowntimeDuration,
            'avgDowntimePerDay' => $avgDowntimePerDay,
            'mostProblematicMachine' => $mostProblematicMachine,
            'longestDowntime' => $longestDowntime,
            'topMachines' => $topMachines,
            'topMTTR' => $topMTTR,
            'topPlants' => $topPlants,
            'topProblems' => $topProblems,
            'topMekanik' => $topMekanik,
            'topLines' => $topLines,
            'downtimeTrend' => $downtimeTrend,
            'recentDowntimeErps' => $recentDowntimeErps,
        ];
    }
    
    /**
     * Get statistics from Downtime table
     */
    private function getDowntimeStats($currentYear, $currentMonth)
    {
        // Total downtime count this month
        $monthDowntimeCount = Downtime::whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->count();
        
        // Total downtime duration this month
        $monthDowntime = Downtime::whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->sum('duration');
        
        // Average downtime duration per incident
        $avgDowntimeDuration = $monthDowntimeCount > 0 ? $monthDowntime / $monthDowntimeCount : 0;
        
        // Average downtime per day (total duration / days in month)
        $daysInMonth = now()->daysInMonth;
        $avgDowntimePerDay = $monthDowntime / $daysInMonth;
        
        // Most problematic machine (by total duration)
        $mostProblematicMachine = Downtime::select(
                DB::raw('machines.idMachine as idMachine'),
                DB::raw('MAX(machine_types.name) as typeMachine'),
                DB::raw('SUM(downtimes.duration) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->join('machines', 'downtimes.machine_id', '=', 'machines.id')
            ->leftJoin('machine_types', 'machines.type_id', '=', 'machine_types.id')
            ->whereYear('downtimes.date', $currentYear)
            ->whereMonth('downtimes.date', $currentMonth)
            ->whereNotNull('machines.idMachine')
            ->groupBy('machines.idMachine')
            ->orderBy('total_duration', 'desc')
            ->first();
        
        // Longest single downtime this month
        $longestDowntime = Downtime::whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->orderBy('duration', 'desc')
            ->first();
        
        // Top 10 Machine dengan Akumulasi Downtime Tertinggi (This Month)
        $topMachines = Downtime::select(
                DB::raw('machines.idMachine as idMachine'),
                DB::raw('MAX(machine_types.name) as typeMachine'),
                DB::raw('SUM(downtimes.duration) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->join('machines', 'downtimes.machine_id', '=', 'machines.id')
            ->leftJoin('machine_types', 'machines.type_id', '=', 'machine_types.id')
            ->whereYear('downtimes.date', $currentYear)
            ->whereMonth('downtimes.date', $currentMonth)
            ->whereNotNull('machines.idMachine')
            ->groupBy('machines.idMachine')
            ->orderBy('total_duration', 'desc')
            ->limit(10)
            ->get();

        // Top 5 MTTR (Mean Time To Repair) Tertinggi (This Month)
        $topMTTR = Downtime::select(
                DB::raw('machines.idMachine as idMachine'),
                DB::raw('MAX(machine_types.name) as typeMachine'),
                DB::raw('SUM(downtimes.duration) as total_duration'),
                DB::raw('COUNT(*) as downtime_count'),
                DB::raw('SUM(downtimes.duration) / COUNT(*) as mttr')
            )
            ->join('machines', 'downtimes.machine_id', '=', 'machines.id')
            ->leftJoin('machine_types', 'machines.type_id', '=', 'machine_types.id')
            ->whereYear('downtimes.date', $currentYear)
            ->whereMonth('downtimes.date', $currentMonth)
            ->whereNotNull('machines.idMachine')
            ->groupBy('machines.idMachine')
            ->havingRaw('COUNT(*) > 0')
            ->orderBy('mttr', 'desc')
            ->limit(5)
            ->get();

        // Top 5 Plant dengan Akumulasi Downtime Tertinggi (This Month)
        $topPlants = Downtime::select(
                'plants.name as plant',
                DB::raw('SUM(downtimes.duration) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->join('machines', 'downtimes.machine_id', '=', 'machines.id')
            ->join('plants', 'machines.plant_id', '=', 'plants.id')
            ->whereYear('downtimes.date', $currentYear)
            ->whereMonth('downtimes.date', $currentMonth)
            ->whereNotNull('plants.name')
            ->groupBy('plants.name')
            ->orderBy('total_duration', 'desc')
            ->limit(5)
            ->get();
        
        // Top 5 Most Common Problems (This Month)
        $topProblems = Downtime::select(
                'problems.name as problemDowntime',
                DB::raw('COUNT(*) as problem_count'),
                DB::raw('SUM(downtimes.duration) as total_duration')
            )
            ->join('problems', 'downtimes.problem_id', '=', 'problems.id')
            ->whereYear('downtimes.date', $currentYear)
            ->whereMonth('downtimes.date', $currentMonth)
            ->whereNotNull('problems.name')
            ->groupBy('problems.name')
            ->orderBy('problem_count', 'desc')
            ->limit(5)
            ->get();

        // Top 5 Most Active Mekanik (This Month)
        $topMekanik = Downtime::select(
                'users.name as nameMekanik',
                DB::raw('COUNT(*) as downtime_count'),
                DB::raw('SUM(downtimes.duration) as total_duration')
            )
            ->join('users', 'downtimes.mekanik_id', '=', 'users.id')
            ->whereYear('downtimes.date', $currentYear)
            ->whereMonth('downtimes.date', $currentMonth)
            ->whereNotNull('users.name')
            ->groupBy('users.name')
            ->orderBy('downtime_count', 'desc')
            ->limit(5)
            ->get();

        // Top 5 Lines with Most Downtime (This Month)
        $topLines = Downtime::select(
                'lines.name as line',
                DB::raw('SUM(downtimes.duration) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->join('machines', 'downtimes.machine_id', '=', 'machines.id')
            ->join('lines', 'machines.line_id', '=', 'lines.id')
            ->whereYear('downtimes.date', $currentYear)
            ->whereMonth('downtimes.date', $currentMonth)
            ->whereNotNull('lines.name')
            ->groupBy('lines.name')
            ->orderBy('total_duration', 'desc')
            ->limit(5)
            ->get();

        // Downtime Trend per Day (This Month)
        $downtimeTrend = Downtime::select(
                DB::raw('DATE(date) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(duration) as total_duration')
            )
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->groupBy(DB::raw('DATE(date)'))
            ->orderBy('date', 'asc')
            ->get();
        
        // Recent Downtimes (10 terakhir) - This Month
        $recentDowntimeErps = Downtime::with(['machine.machineType', 'machine.plant', 'problem', 'mekanik'])
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return [
            'monthDowntimeCount' => $monthDowntimeCount,
            'monthDowntime' => $monthDowntime,
            'avgDowntimeDuration' => $avgDowntimeDuration,
            'avgDowntimePerDay' => $avgDowntimePerDay,
            'mostProblematicMachine' => $mostProblematicMachine,
            'longestDowntime' => $longestDowntime,
            'topMachines' => $topMachines,
            'topMTTR' => $topMTTR,
            'topPlants' => $topPlants,
            'topProblems' => $topProblems,
            'topMekanik' => $topMekanik,
            'topLines' => $topLines,
            'downtimeTrend' => $downtimeTrend,
            'recentDowntimeErps' => $recentDowntimeErps,
        ];
    }
}

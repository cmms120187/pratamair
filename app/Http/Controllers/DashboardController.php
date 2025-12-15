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
use App\Models\DowntimeErp2;
use App\Models\User;
use App\Models\PreventiveMaintenanceSchedule;
use App\Models\PreventiveMaintenanceExecution;
use App\Models\PredictiveMaintenanceSchedule;
use App\Models\PredictiveMaintenanceExecution;
use App\Models\WorkOrder;
use App\Models\Standard;
use App\Models\PartErp;
use App\Models\System;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get data source from request or session, default to 'downtime_erp2'
        $dataSource = $request->input('data_source', session('dashboard_data_source', 'downtime_erp2'));
        session(['dashboard_data_source' => $dataSource]);
        
        // Get filter parameters (default: current month and year)
        // Priority: query parameter > session > current month/year
        $filterMonth = $request->get('month', session('dashboard_filter_month', now()->month));
        $filterYear = $request->get('year', session('dashboard_filter_year', now()->year));
        
        // Validate month and year
        $filterMonth = max(1, min(12, (int)$filterMonth));
        $filterYear = max(2000, min(2100, (int)$filterYear));
        
        // Save filter to session for persistence
        session([
            'dashboard_filter_month' => $filterMonth,
            'dashboard_filter_year' => $filterYear,
        ]);
        
        $currentMonth = $filterMonth;
        $currentYear = $filterYear;
        
        // ========== THIS MONTH STATISTICS (CACHED) ==========
        // Cache key based on data source, year, and month
        $statsCacheKey = 'dashboard_stats_' . $dataSource . '_' . $currentYear . '_' . $currentMonth;
        
        // Cache for 1 hour (3600 seconds) - can be cleared manually when data changes
        $stats = Cache::remember($statsCacheKey, 3600, function() use ($dataSource, $currentYear, $currentMonth) {
            if ($dataSource === 'downtime_erp2') {
                return $this->getDowntimeErp2Stats($currentYear, $currentMonth);
            } elseif ($dataSource === 'downtime_erp') {
                return $this->getDowntimeErpStats($currentYear, $currentMonth);
            } else {
                return $this->getDowntimeStats($currentYear, $currentMonth);
            }
        });
        
        // ========== PREVENTIVE MAINTENANCE STATISTICS (CACHED) ==========
        $pmCacheKey = 'pm_stats_' . $currentYear . '_' . $currentMonth;
        $pmStats = Cache::remember($pmCacheKey, 7200, function() use ($currentYear, $currentMonth) {
            return [
                'pmSchedulesThisMonth' => PreventiveMaintenanceSchedule::whereYear('start_date', $currentYear)
                    ->whereMonth('start_date', $currentMonth)
                    ->count(),
                'pmSchedulesPending' => PreventiveMaintenanceSchedule::whereYear('start_date', $currentYear)
                    ->whereMonth('start_date', $currentMonth)
                    ->where('status', 'active')
                    ->whereDoesntHave('executions')
                    ->count(),
                'pmSchedulesCompleted' => PreventiveMaintenanceSchedule::whereYear('start_date', $currentYear)
                    ->whereMonth('start_date', $currentMonth)
                    ->whereHas('executions', function($q) {
                        $q->where('status', 'completed');
                    })
                    ->count(),
                'pmSchedulesInProgress' => PreventiveMaintenanceSchedule::whereYear('start_date', $currentYear)
                    ->whereMonth('start_date', $currentMonth)
                    ->whereHas('executions', function($q) {
                        $q->where('status', 'in_progress');
                    })
                    ->count(),
            ];
        });
        
        $pmSchedulesThisMonth = $pmStats['pmSchedulesThisMonth'];
        $pmSchedulesPending = $pmStats['pmSchedulesPending'];
        $pmSchedulesCompleted = $pmStats['pmSchedulesCompleted'];
        $pmSchedulesInProgress = $pmStats['pmSchedulesInProgress'];
        $pmCompletionRate = $pmSchedulesThisMonth > 0 ? ($pmSchedulesCompleted / $pmSchedulesThisMonth) * 100 : 0;

        // ========== PREDICTIVE MAINTENANCE STATISTICS (CACHED) ==========
        $pdmCacheKey = 'pdm_stats_' . $currentYear . '_' . $currentMonth;
        $pdmStats = Cache::remember($pdmCacheKey, 7200, function() use ($currentYear, $currentMonth) {
            return [
                'pdmSchedulesThisMonth' => PredictiveMaintenanceSchedule::whereYear('start_date', $currentYear)
                    ->whereMonth('start_date', $currentMonth)
                    ->count(),
                'pdmSchedulesPending' => PredictiveMaintenanceSchedule::whereYear('start_date', $currentYear)
                    ->whereMonth('start_date', $currentMonth)
                    ->where('status', 'active')
                    ->whereDoesntHave('executions')
                    ->count(),
                'pdmSchedulesCompleted' => PredictiveMaintenanceSchedule::whereYear('start_date', $currentYear)
                    ->whereMonth('start_date', $currentMonth)
                    ->whereHas('executions', function($q) {
                        $q->where('status', 'completed');
                    })
                    ->count(),
            ];
        });
        
        $pdmSchedulesThisMonth = $pdmStats['pdmSchedulesThisMonth'];
        $pdmSchedulesPending = $pdmStats['pdmSchedulesPending'];
        $pdmSchedulesCompleted = $pdmStats['pdmSchedulesCompleted'];
        $pdmCompletionRate = $pdmSchedulesThisMonth > 0 ? ($pdmSchedulesCompleted / $pdmSchedulesThisMonth) * 100 : 0;

        // ========== WORK ORDERS STATISTICS (CACHED) ==========
        $woCacheKey = 'wo_stats_' . $currentYear . '_' . $currentMonth;
        $woStats = Cache::remember($woCacheKey, 1800, function() use ($currentYear, $currentMonth) {
            return [
                'workOrdersTotal' => WorkOrder::count(),
                'workOrdersPending' => WorkOrder::where('status', 'pending')->count(),
                'workOrdersInProgress' => WorkOrder::where('status', 'in_progress')->count(),
                'workOrdersCompleted' => WorkOrder::where('status', 'completed')->count(),
                'workOrdersThisMonth' => WorkOrder::whereYear('order_date', $currentYear)
                    ->whereMonth('order_date', $currentMonth)
                    ->count(),
            ];
        });
        
        $workOrdersTotal = $woStats['workOrdersTotal'];
        $workOrdersPending = $woStats['workOrdersPending'];
        $workOrdersInProgress = $woStats['workOrdersInProgress'];
        $workOrdersCompleted = $woStats['workOrdersCompleted'];
        $workOrdersThisMonth = $woStats['workOrdersThisMonth'];

        // ========== MACHINES STATISTICS (CACHED) ==========
        $machinesCacheKey = 'machines_stats_' . $dataSource . '_' . $currentYear . '_' . $currentMonth;
        $machinesStats = Cache::remember($machinesCacheKey, 3600, function() use ($dataSource, $currentYear, $currentMonth) {
            $totalMachines = Machine::count();
            
            if ($dataSource === 'downtime_erp2') {
                $machinesWithDowntime = DowntimeErp2::whereYear('date', $currentYear)
                    ->whereMonth('date', $currentMonth)
                    ->whereNotNull('idMachine')
                    ->where('idMachine', '!=', '')
                    ->distinct('idMachine')
                    ->count('idMachine');
            } elseif ($dataSource === 'downtime_erp') {
                $machinesWithDowntime = DowntimeErp::whereYear('date', $currentYear)
                    ->whereMonth('date', $currentMonth)
                    ->whereNotNull('idMachine')
                    ->where('idMachine', '!=', '')
                    ->distinct('idMachine')
                    ->count('idMachine');
            } else {
                $machinesWithDowntime = Downtime::whereYear('date', $currentYear)
                    ->whereMonth('date', $currentMonth)
                    ->whereNotNull('machine_id')
                    ->distinct('machine_id')
                    ->count('machine_id');
            }
            
            $machinesWithPM = PreventiveMaintenanceSchedule::whereYear('start_date', $currentYear)
                ->whereMonth('start_date', $currentMonth)
                ->distinct('machine_erp_id')
                ->count('machine_erp_id');
            
            return [
                'totalMachines' => $totalMachines,
                'machinesWithDowntime' => $machinesWithDowntime,
                'machinesWithPM' => $machinesWithPM,
            ];
        });
        
        $totalMachines = $machinesStats['totalMachines'];
        $machinesWithDowntime = $machinesStats['machinesWithDowntime'];
        $machinesWithPM = $machinesStats['machinesWithPM'];

        // ========== USERS STATISTICS (CACHED) ==========
        $usersCacheKey = 'users_stats_' . $dataSource . '_' . $currentYear . '_' . $currentMonth;
        $usersStats = Cache::remember($usersCacheKey, 3600, function() use ($dataSource, $currentYear, $currentMonth) {
            $totalUsers = User::count();
            $totalMechanics = User::whereIn('role', ['mekanik', 'team_leader', 'group_leader'])->count();
            
            // Active mechanics are those who have downtime records this month
            if ($dataSource === 'downtime_erp2') {
                $activeMechanicNames = DowntimeErp2::whereYear('date', $currentYear)
                    ->whereMonth('date', $currentMonth)
                    ->whereNotNull('nameMekanik')
                    ->where('nameMekanik', '!=', '')
                    ->distinct()
                    ->pluck('nameMekanik')
                    ->toArray();
                
                $activeMechanics = User::whereIn('role', ['mekanik', 'team_leader', 'group_leader'])
                    ->whereIn('name', $activeMechanicNames)
                    ->count();
            } elseif ($dataSource === 'downtime_erp') {
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
            
            return [
                'totalUsers' => $totalUsers,
                'totalMechanics' => $totalMechanics,
                'activeMechanics' => $activeMechanics,
            ];
        });
        
        $totalUsers = $usersStats['totalUsers'];
        $totalMechanics = $usersStats['totalMechanics'];
        $activeMechanics = $usersStats['activeMechanics'];

        // ========== STANDARDS STATISTICS (CACHED) ==========
        $standardsCacheKey = 'standards_stats';
        $standardsStats = Cache::remember($standardsCacheKey, 7200, function() {
            return [
                'totalStandards' => Standard::count(),
                'activeStandards' => Standard::where('status', 'active')->count(),
            ];
        });
        
        $totalStandards = $standardsStats['totalStandards'];
        $activeStandards = $standardsStats['activeStandards'];

        // ========== SPAREPART STATISTICS (CACHED) ==========
        $sparepartCacheKey = 'sparepart_stats';
        $sparepartStats = Cache::remember($sparepartCacheKey, 3600, function() {
            return [
                'totalSpareparts' => PartErp::count(),
                'lowStockSpareparts' => PartErp::whereColumn('stock', '<', 'minimum_stock')
                    ->where('minimum_stock', '>', 0)
                    ->count(),
                'totalStockValue' => PartErp::sum(DB::raw('stock * COALESCE(price, 0)')),
            ];
        });
        
        $totalSpareparts = $sparepartStats['totalSpareparts'];
        $lowStockSpareparts = $sparepartStats['lowStockSpareparts'];
        $totalStockValue = $sparepartStats['totalStockValue'];

        // ========== LOCATION STATISTICS (CACHED) ==========
        $locationCacheKey = 'location_stats';
        $locationStats = Cache::remember($locationCacheKey, 7200, function() {
            return [
                'totalPlants' => Plant::count(),
                'totalProcesses' => Process::count(),
                'totalLines' => Line::count(),
                'totalRooms' => Room::count(),
            ];
        });
        
        $totalPlants = $locationStats['totalPlants'];
        $totalProcesses = $locationStats['totalProcesses'];
        $totalLines = $locationStats['totalLines'];
        $totalRooms = $locationStats['totalRooms'];

        // ========== PROBLEM, REASON, ACTION STATISTICS (CACHED) ==========
        $problemReasonActionCacheKey = 'problem_reason_action_stats';
        $problemReasonActionStats = Cache::remember($problemReasonActionCacheKey, 7200, function() {
            return [
                'uniqueProblems' => Problem::distinct('name')->count('name'),
                'uniqueReasons' => Reason::distinct('name')->count('name'),
                'uniqueActions' => Action::distinct('name')->count('name'),
                'uniqueProblemMms' => ProblemMm::distinct('name')->count('name'),
            ];
        });
        
        $uniqueProblems = $problemReasonActionStats['uniqueProblems'];
        $uniqueReasons = $problemReasonActionStats['uniqueReasons'];
        $uniqueActions = $problemReasonActionStats['uniqueActions'];
        $uniqueProblemMms = $problemReasonActionStats['uniqueProblemMms'];

        // ========== PREDICTIVE RED STATUS STATISTICS (CACHED) ==========
        $predictiveRedCacheKey = 'predictive_red_stats';
        $predictiveRedStats = Cache::remember($predictiveRedCacheKey, 1800, function() {
            return [
                'redStatusCount' => PredictiveMaintenanceExecution::where('measurement_status', 'critical')->count(),
                'redStatusThisMonth' => PredictiveMaintenanceExecution::where('measurement_status', 'critical')
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->count(),
            ];
        });
        
        $redStatusCount = $predictiveRedStats['redStatusCount'];
        $redStatusThisMonth = $predictiveRedStats['redStatusThisMonth'];

        // ========== MACHINERY STATISTICS (CACHED) ==========
        $machineryCacheKey = 'machinery_stats';
        $machineryStats = Cache::remember($machineryCacheKey, 7200, function() {
            return [
                'totalSystems' => System::count(),
                'totalGroups' => Group::count(),
                'totalMachineTypes' => MachineType::count(),
                'totalBrands' => Brand::count(),
                'totalModels' => Model::count(),
                'machinesWithBrand' => Machine::whereNotNull('brand_id')->distinct()->count('brand_id'),
                'machinesWithModel' => Machine::whereNotNull('model_id')->distinct()->count('model_id'),
                'machinesWithType' => Machine::whereNotNull('type_id')->distinct()->count('type_id'),
            ];
        });
        
        $totalSystems = $machineryStats['totalSystems'];
        $totalGroups = $machineryStats['totalGroups'];
        $totalMachineTypes = $machineryStats['totalMachineTypes'];
        $totalBrands = $machineryStats['totalBrands'];
        $totalModels = $machineryStats['totalModels'];
        $machinesWithBrand = $machineryStats['machinesWithBrand'];
        $machinesWithModel = $machineryStats['machinesWithModel'];
        $machinesWithType = $machineryStats['machinesWithType'];

        // ========== RECENT WORK ORDERS ==========
        $recentWorkOrders = WorkOrder::orderBy('order_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Calculate days in the selected month
        $daysInMonth = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->daysInMonth;
        
        return view('dashboard', array_merge($stats, [
            'dataSource' => $dataSource,
            'currentMonth' => $currentMonth,
            'currentYear' => $currentYear,
            'filterMonth' => $filterMonth,
            'filterYear' => $filterYear,
            'daysInMonth' => $daysInMonth,
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
            'activeStandards' => $activeStandards,
            // Sparepart Stats
            'totalSpareparts' => $totalSpareparts,
            'lowStockSpareparts' => $lowStockSpareparts,
            'totalStockValue' => $totalStockValue,
            // Location Stats
            'totalPlants' => $totalPlants,
            'totalProcesses' => $totalProcesses,
            'totalLines' => $totalLines,
            'totalRooms' => $totalRooms,
            // Problem, Reason, Action Stats
            'uniqueProblems' => $uniqueProblems,
            'uniqueReasons' => $uniqueReasons,
            'uniqueActions' => $uniqueActions,
            'uniqueProblemMms' => $uniqueProblemMms,
            // Predictive Red Status Stats
            'redStatusCount' => $redStatusCount,
            'redStatusThisMonth' => $redStatusThisMonth,
            // Machinery Stats
            'totalSystems' => $totalSystems,
            'totalGroups' => $totalGroups,
            'totalMachineTypes' => $totalMachineTypes,
            'totalBrands' => $totalBrands,
            'totalModels' => $totalModels,
            'machinesWithBrand' => $machinesWithBrand,
            'machinesWithModel' => $machinesWithModel,
            'machinesWithType' => $machinesWithType
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
        $daysInMonth = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->daysInMonth;
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
     * Get statistics from DowntimeErp2 table
     */
    private function getDowntimeErp2Stats($currentYear, $currentMonth)
    {
        // Total downtime count this month
        $monthDowntimeCount = DowntimeErp2::whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->count();
        
        // Total downtime duration this month
        $monthDowntime = DowntimeErp2::whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->get()
            ->sum(function($item) {
                return (float) ($item->duration ?? 0);
            });
        
        // Average downtime duration per incident
        $avgDowntimeDuration = $monthDowntimeCount > 0 ? $monthDowntime / $monthDowntimeCount : 0;
        
        // Average downtime per day (total duration / days in month)
        $daysInMonth = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->daysInMonth;
        $avgDowntimePerDay = $monthDowntime / $daysInMonth;
        
        // Most problematic machine (by total duration)
        $mostProblematicMachine = DowntimeErp2::select(
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
        $longestDowntime = DowntimeErp2::whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->orderByRaw('CAST(duration AS DECIMAL(10,2)) DESC')
            ->first();
        
        // Top 10 Machine dengan Akumulasi Downtime Tertinggi (This Month)
        $topMachines = DowntimeErp2::select(
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
        $topMTTR = DowntimeErp2::select(
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
        $topPlants = DowntimeErp2::select(
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
        $topProblems = DowntimeErp2::select(
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
        $topMekanik = DowntimeErp2::select(
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
        $topLines = DowntimeErp2::select(
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
        $downtimeTrend = DowntimeErp2::select(
                DB::raw('DATE(date) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration')
            )
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->groupBy(DB::raw('DATE(date)'))
            ->orderBy('date', 'asc')
            ->get();
        
        // Recent Downtime ERP2s (10 terakhir) - This Month
        $recentDowntimeErps = DowntimeErp2::whereYear('date', $currentYear)
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
        $daysInMonth = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->daysInMonth;
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

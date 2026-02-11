<?php

namespace App\Http\Controllers;

use App\Models\DowntimeErp2;
use App\Models\DowntimeErp;
use App\Models\Downtime;
use App\Models\WorkOrder;
use App\Models\PreventiveMaintenanceSchedule as PMScheduling;
use App\Models\PredictiveMaintenanceSchedule as PDMScheduling;
use App\Models\Standard;
use App\Models\PartErp;
use App\Models\Plant;
use App\Models\Process;
use App\Models\Line;
use App\Models\Room;
use App\Models\Problem;
use App\Models\Reason;
use App\Models\Action;
use App\Models\ProblemMm;
use App\Models\User;
use App\Models\Machine;
use App\Models\RoomErp;
use App\Models\MachineErp;
use App\Models\System;
use App\Models\Group;
use App\Models\MachineType;
use App\Models\Brand;
use App\Models\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

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
        $pmStats = Cache::remember($pmCacheKey, 1800, function() use ($currentYear, $currentMonth) {
            $startDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            
            $pmSchedulesThisMonth = PMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])->count();
            $pmSchedulesPending = PMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->where('status', 'active')->count();
            $pmSchedulesInProgress = PMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->where('status', 'active')->count(); // PM doesn't have in_progress status, using active
            $pmSchedulesCompleted = PMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->where('status', 'completed')->count();
            
            $pmCompletionRate = $pmSchedulesThisMonth > 0 
                ? ($pmSchedulesCompleted / $pmSchedulesThisMonth) * 100 
                : 0;
            
            return [
                'pmSchedulesThisMonth' => $pmSchedulesThisMonth,
                'pmSchedulesPending' => $pmSchedulesPending,
                'pmSchedulesInProgress' => $pmSchedulesInProgress,
                'pmSchedulesCompleted' => $pmSchedulesCompleted,
                'pmCompletionRate' => $pmCompletionRate,
            ];
        });
        
        $pmSchedulesThisMonth = $pmStats['pmSchedulesThisMonth'];
        $pmSchedulesPending = $pmStats['pmSchedulesPending'];
        $pmSchedulesInProgress = $pmStats['pmSchedulesInProgress'];
        $pmSchedulesCompleted = $pmStats['pmSchedulesCompleted'];
        $pmCompletionRate = $pmStats['pmCompletionRate'];

        // ========== PREDICTIVE MAINTENANCE STATISTICS (CACHED) ==========
        $pdmCacheKey = 'pdm_stats_' . $currentYear . '_' . $currentMonth;
        $pdmStats = Cache::remember($pdmCacheKey, 1800, function() use ($currentYear, $currentMonth) {
            $startDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            
            $pdmSchedulesThisMonth = PDMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])->count();
            $pdmSchedulesPending = PDMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->where('status', 'active')->count(); // PDM status: active, inactive, completed, cancelled
            $pdmSchedulesCompleted = PDMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->where('status', 'completed')->count();
            
            $pdmCompletionRate = $pdmSchedulesThisMonth > 0 
                ? ($pdmSchedulesCompleted / $pdmSchedulesThisMonth) * 100 
                : 0;
            
            return [
                'pdmSchedulesThisMonth' => $pdmSchedulesThisMonth,
                'pdmSchedulesPending' => $pdmSchedulesPending,
                'pdmSchedulesCompleted' => $pdmSchedulesCompleted,
                'pdmCompletionRate' => $pdmCompletionRate,
            ];
        });
        
        $pdmSchedulesThisMonth = $pdmStats['pdmSchedulesThisMonth'];
        $pdmSchedulesPending = $pdmStats['pdmSchedulesPending'];
        $pdmSchedulesCompleted = $pdmStats['pdmSchedulesCompleted'];
        $pdmCompletionRate = $pdmStats['pdmCompletionRate'];

        // ========== WORK ORDERS STATISTICS (CACHED) ==========
        $woCacheKey = 'wo_stats_' . $currentYear . '_' . $currentMonth;
        $woStats = Cache::remember($woCacheKey, 1800, function() use ($currentYear, $currentMonth) {
            $startDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            
            $workOrdersTotal = WorkOrder::count();
            $workOrdersPending = WorkOrder::where('status', 'pending')->count();
            $workOrdersInProgress = WorkOrder::where('status', 'in_progress')->count();
            $workOrdersCompleted = WorkOrder::where('status', 'completed')->count();
            $workOrdersThisMonth = WorkOrder::whereBetween('order_date', [$startDate, $endDate])->count();
            
            return [
                'workOrdersTotal' => $workOrdersTotal,
                'workOrdersPending' => $workOrdersPending,
                'workOrdersInProgress' => $workOrdersInProgress,
                'workOrdersCompleted' => $workOrdersCompleted,
                'workOrdersThisMonth' => $workOrdersThisMonth,
            ];
        });
        
        $workOrdersTotal = $woStats['workOrdersTotal'];
        $workOrdersPending = $woStats['workOrdersPending'];
        $workOrdersInProgress = $woStats['workOrdersInProgress'];
        $workOrdersCompleted = $woStats['workOrdersCompleted'];
        $workOrdersThisMonth = $woStats['workOrdersThisMonth'];

        // ========== MACHINES STATISTICS (CACHED) ==========
        $machinesCacheKey = 'machines_stats_' . $currentYear . '_' . $currentMonth;
        $machinesStats = Cache::remember($machinesCacheKey, 3600, function() use ($dataSource, $currentYear, $currentMonth) {
            if ($dataSource === 'downtime_erp2' || $dataSource === 'downtime_erp') {
                $totalMachines = MachineErp::count();
                $machinesWithDowntime = DB::table('downtime_erp2')
                    ->whereYear('date', $currentYear)
                    ->whereMonth('date', $currentMonth)
                    ->distinct('idMachine')
                    ->count('idMachine');
                // Count machines with PM schedules (any status)
                $machinesWithPM = MachineErp::whereHas('preventiveMaintenanceSchedules')->count();
                
                // Alternative: Count distinct machines from PM schedules table (more accurate)
                $machinesWithPMAlt = DB::table('preventive_maintenance_schedules')
                    ->whereNotNull('machine_erp_id')
                    ->distinct('machine_erp_id')
                    ->count('machine_erp_id');
                
                // Use the higher count (more accurate)
                $machinesWithPM = max($machinesWithPM, $machinesWithPMAlt);
            } else {
                $totalMachines = Machine::count();
                $machinesWithDowntime = Downtime::whereYear('date', $currentYear)
                    ->whereMonth('date', $currentMonth)
                    ->distinct('machine_id')
                    ->count('machine_id');
                $machinesWithPM = Machine::whereHas('preventiveMaintenanceSchedules')->count();
            }
            
            return [
                'totalMachines' => $totalMachines,
                'machinesWithDowntime' => $machinesWithDowntime,
                'machinesWithPM' => $machinesWithPM,
            ];
        });
        
        $totalMachines = $machinesStats['totalMachines'];
        $machinesWithDowntime = $machinesStats['machinesWithDowntime'];
        $machinesWithPM = $machinesStats['machinesWithPM'];

        // ========== USERS/SDM STATISTICS (CACHED) ==========
        $usersCacheKey = 'users_stats';
        $usersStats = Cache::remember($usersCacheKey, 7200, function() use ($dataSource, $currentYear, $currentMonth) {
            $totalUsers = User::count();
            $totalMechanics = User::where('role', 'mekanik')->count();
            
            if ($dataSource === 'downtime_erp2') {
                $activeMechanics = DowntimeErp2::whereYear('date', $currentYear)
                    ->whereMonth('date', $currentMonth)
                    ->whereNotNull('nameMekanik')
                    ->distinct('nameMekanik')
                    ->count('nameMekanik');
            } elseif ($dataSource === 'downtime_erp') {
                $activeMechanics = DowntimeErp::whereYear('date', $currentYear)
                    ->whereMonth('date', $currentMonth)
                    ->whereNotNull('nameMekanik')
                    ->distinct('nameMekanik')
                    ->count('nameMekanik');
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
                'totalRooms' => RoomErp::count(),
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
        $predictiveRedStats = Cache::remember($predictiveRedCacheKey, 1800, function() use ($currentYear, $currentMonth) {
            $startDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            
            $redStatusCount = Standard::where('status', 'red')->count();
            $redStatusThisMonth = Standard::where('status', 'red')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count();
            
            return [
                'redStatusCount' => $redStatusCount,
                'redStatusThisMonth' => $redStatusThisMonth,
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
                'machinesWithBrand' => MachineErp::whereNotNull('brand_name')->where('brand_name', '!=', '')->count(),
                'machinesWithModel' => MachineErp::whereNotNull('model_name')->where('model_name', '!=', '')->count(),
                'machinesWithType' => MachineErp::whereNotNull('type_name')->where('type_name', '!=', '')->count(),
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
     * Dashboard Large View - Optimized for Large Monitors (50 inch+)
     */
    public function large(Request $request)
    {
        // Get user's dashboard settings (default to current month/year if not set)
        $user = Auth::user();
        $userSettings = $user ? $user->getDashboardSettings() : [
            'data_source' => 'downtime_erp2',
            'month' => now()->month,
            'year' => now()->year,
        ];
        
        // Priority: request > user settings > session > defaults
        $dataSource = $request->input('data_source', 
            $userSettings['data_source'] ?? 
            session('dashboard_data_source', 'downtime_erp2')
        );
        session(['dashboard_data_source' => $dataSource]);
        
        $filterMonth = $request->get('month', 
            $userSettings['month'] ?? 
            session('dashboard_filter_month', now()->month)
        );
        $filterYear = $request->get('year', 
            $userSettings['year'] ?? 
            session('dashboard_filter_year', now()->year)
        );
        
        $filterMonth = max(1, min(12, (int)$filterMonth));
        $filterYear = max(2000, min(2100, (int)$filterYear));
        
        session([
            'dashboard_filter_month' => $filterMonth,
            'dashboard_filter_year' => $filterYear,
        ]);
        
        $currentMonth = $filterMonth;
        $currentYear = $filterYear;
        
        $statsCacheKey = 'dashboard_stats_' . $dataSource . '_' . $currentYear . '_' . $currentMonth;
        $stats = Cache::remember($statsCacheKey, 3600, function() use ($dataSource, $currentYear, $currentMonth) {
            if ($dataSource === 'downtime_erp2') {
                return $this->getDowntimeErp2Stats($currentYear, $currentMonth);
            } elseif ($dataSource === 'downtime_erp') {
                return $this->getDowntimeErpStats($currentYear, $currentMonth);
            } else {
                return $this->getDowntimeStats($currentYear, $currentMonth);
            }
        });
        
        // Get all the same stats as index method - reuse all cached data
        $pmCacheKey = 'pm_stats_' . $currentYear . '_' . $currentMonth;
        $pmStats = Cache::remember($pmCacheKey, 1800, function() use ($currentYear, $currentMonth) {
            $startDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            
            $pmSchedulesThisMonth = PMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])->count();
            $pmSchedulesPending = PMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])->where('status', 'active')->count();
            $pmSchedulesInProgress = PMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])->where('status', 'active')->count();
            $pmSchedulesCompleted = PMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])->where('status', 'completed')->count();
            $pmCompletionRate = $pmSchedulesThisMonth > 0 ? ($pmSchedulesCompleted / $pmSchedulesThisMonth) * 100 : 0;
            
            return compact('pmSchedulesThisMonth', 'pmSchedulesPending', 'pmSchedulesInProgress', 'pmSchedulesCompleted', 'pmCompletionRate');
        });
        
        $pdmCacheKey = 'pdm_stats_' . $currentYear . '_' . $currentMonth;
        $pdmStats = Cache::remember($pdmCacheKey, 1800, function() use ($currentYear, $currentMonth) {
            $startDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            
            $pdmSchedulesThisMonth = PDMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])->count();
            $pdmSchedulesPending = PDMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])->where('status', 'active')->count();
            $pdmSchedulesCompleted = PDMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])->where('status', 'completed')->count();
            $pdmCompletionRate = $pdmSchedulesThisMonth > 0 ? ($pdmSchedulesCompleted / $pdmSchedulesThisMonth) * 100 : 0;
            
            return compact('pdmSchedulesThisMonth', 'pdmSchedulesPending', 'pdmSchedulesCompleted', 'pdmCompletionRate');
        });
        
        $woCacheKey = 'wo_stats_' . $currentYear . '_' . $currentMonth;
        $woStats = Cache::remember($woCacheKey, 1800, function() use ($currentYear, $currentMonth) {
            $startDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            
            return [
                'workOrdersTotal' => WorkOrder::count(),
                'workOrdersPending' => WorkOrder::where('status', 'pending')->count(),
                'workOrdersInProgress' => WorkOrder::where('status', 'in_progress')->count(),
                'workOrdersCompleted' => WorkOrder::where('status', 'completed')->count(),
                'workOrdersThisMonth' => WorkOrder::whereBetween('order_date', [$startDate, $endDate])->count(),
            ];
        });
        
        $machinesCacheKey = 'machines_stats_' . $currentYear . '_' . $currentMonth;
        $machinesStats = Cache::remember($machinesCacheKey, 3600, function() use ($dataSource, $currentYear, $currentMonth) {
            if ($dataSource === 'downtime_erp2' || $dataSource === 'downtime_erp') {
                // Count machines with PM schedules (any status, not just active)
                $machinesWithPM = MachineErp::whereHas('preventiveMaintenanceSchedules', function($query) {
                    // Count machines that have at least one PM schedule (any status)
                })->count();
                
                // Alternative: Count distinct machines from PM schedules table
                $machinesWithPMAlt = DB::table('preventive_maintenance_schedules')
                    ->whereNotNull('machine_erp_id')
                    ->distinct('machine_erp_id')
                    ->count('machine_erp_id');
                
                // Use the higher count (more accurate)
                $machinesWithPM = max($machinesWithPM, $machinesWithPMAlt);
                
                return [
                    'totalMachines' => MachineErp::count(),
                    'machinesWithDowntime' => DB::table('downtime_erp2')->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->distinct('idMachine')->count('idMachine'),
                    'machinesWithPM' => $machinesWithPM,
                ];
            } else {
                // Count machines with PM schedules (any status)
                $machinesWithPM = Machine::whereHas('preventiveMaintenanceSchedules')->count();
                
                return [
                    'totalMachines' => Machine::count(),
                    'machinesWithDowntime' => Downtime::whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->distinct('machine_id')->count('machine_id'),
                    'machinesWithPM' => $machinesWithPM,
                ];
            }
        });
        
        $usersCacheKey = 'users_stats';
        $usersStats = Cache::remember($usersCacheKey, 7200, function() use ($dataSource, $currentYear, $currentMonth) {
            $totalUsers = User::count();
            $totalMechanics = User::where('role', 'mekanik')->count();
            
            if ($dataSource === 'downtime_erp2') {
                $activeMechanics = DowntimeErp2::whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->whereNotNull('nameMekanik')->distinct('nameMekanik')->count('nameMekanik');
            } elseif ($dataSource === 'downtime_erp') {
                $activeMechanics = DowntimeErp::whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->whereNotNull('nameMekanik')->distinct('nameMekanik')->count('nameMekanik');
            } else {
                $activeMechanics = Downtime::whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->whereNotNull('mekanik_id')->distinct('mekanik_id')->count('mekanik_id');
            }
            
            return compact('totalUsers', 'totalMechanics', 'activeMechanics');
        });
        
        $standardsStats = Cache::remember('standards_stats', 7200, function() {
            return ['totalStandards' => Standard::count(), 'activeStandards' => Standard::where('status', 'active')->count()];
        });
        
        $sparepartStats = Cache::remember('sparepart_stats', 3600, function() {
        return [
                'totalSpareparts' => PartErp::count(),
                'lowStockSpareparts' => PartErp::whereColumn('stock', '<', 'minimum_stock')->where('minimum_stock', '>', 0)->count(),
                'totalStockValue' => PartErp::sum(DB::raw('stock * COALESCE(price, 0)')),
            ];
        });
        
        $locationStats = Cache::remember('location_stats', 7200, function() {
            return ['totalPlants' => Plant::count(), 'totalProcesses' => Process::count(), 'totalLines' => Line::count(), 'totalRooms' => RoomErp::count()];
        });
        
        $problemReasonActionStats = Cache::remember('problem_reason_action_stats', 7200, function() {
            return [
                'uniqueProblems' => Problem::distinct('name')->count('name'),
                'uniqueReasons' => Reason::distinct('name')->count('name'),
                'uniqueActions' => Action::distinct('name')->count('name'),
                'uniqueProblemMms' => ProblemMm::distinct('name')->count('name'),
            ];
        });
        
        $predictiveRedStats = Cache::remember('predictive_red_stats', 1800, function() use ($currentYear, $currentMonth) {
            $startDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            return [
                'redStatusCount' => Standard::where('status', 'red')->count(),
                'redStatusThisMonth' => Standard::where('status', 'red')->whereBetween('updated_at', [$startDate, $endDate])->count(),
            ];
        });
        
        $machineryStats = Cache::remember('machinery_stats', 7200, function() {
            return [
                'totalSystems' => System::count(), 'totalGroups' => Group::count(), 'totalMachineTypes' => MachineType::count(),
                'totalBrands' => Brand::count(), 'totalModels' => Model::count(),
                'machinesWithBrand' => MachineErp::whereNotNull('brand_name')->where('brand_name', '!=', '')->count(),
                'machinesWithModel' => MachineErp::whereNotNull('model_name')->where('model_name', '!=', '')->count(),
                'machinesWithType' => MachineErp::whereNotNull('type_name')->where('type_name', '!=', '')->count(),
            ];
        });
        
        $recentWorkOrders = WorkOrder::orderBy('order_date', 'desc')->orderBy('created_at', 'desc')->limit(5)->get();
        $daysInMonth = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->daysInMonth;
        
        // Get upcoming maintenance schedules for this month
        $startDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $today = now()->toDateString();
        
        $upcomingPMSchedules = PMScheduling::where('status', 'active')
            ->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('start_date', '>=', $today)
            ->with(['machineErp', 'assignedUser'])
            ->orderBy('start_date', 'asc')
            ->limit(10)
            ->get();
        
        $upcomingPDMSchedules = PDMScheduling::where('status', 'active')
            ->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('start_date', '>=', $today)
            ->with(['machineErp', 'standard', 'assignedUser'])
            ->orderBy('start_date', 'asc')
            ->limit(10)
            ->get();
        
        // Skill Matrix untuk halaman Informasi User (terpisah dari Downtime)
        $skillMatrixCacheKey = 'skill_matrix_' . $dataSource . '_' . $currentYear . '_' . $currentMonth;
        $skillMatrixData = Cache::remember($skillMatrixCacheKey, 1800, function() use ($dataSource, $currentYear, $currentMonth) {
            if ($dataSource === 'downtime_erp2') {
                $baseQuery = DowntimeErp2::whereYear('date', $currentYear)->whereMonth('date', $currentMonth)
                    ->whereNotNull('nameMekanik')->where('nameMekanik', '!=', '')
                    ->whereNotNull('typeMachine')->where('typeMachine', '!=', '')
                    ->whereNotNull('idMachine')->where('idMachine', '!=', '');
                $mechanicStats = (clone $baseQuery)->select('idMekanik', 'nameMekanik', DB::raw('COUNT(*) as total_repairs'), DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'))
                    ->groupBy('idMekanik', 'nameMekanik')->orderBy('total_repairs', 'desc')->get();
                $skillMatrixStats = (clone $baseQuery)->select('idMekanik', 'nameMekanik', 'typeMachine', DB::raw('COUNT(DISTINCT idMachine) as machine_count'), DB::raw('COUNT(*) as repair_count'), DB::raw('AVG(CAST(duration AS DECIMAL(10,2))) as avg_duration'), DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'))
                    ->groupBy('idMekanik', 'nameMekanik', 'typeMachine')->get();
                $machinesRaw = (clone $baseQuery)->select('idMekanik', 'typeMachine', 'idMachine')->distinct()->get();
                $machinesData = [];
                foreach ($machinesRaw as $r) {
                    $key = $r->idMekanik . '_' . $r->typeMachine;
                    if (!isset($machinesData[$key])) $machinesData[$key] = [];
                    if (!in_array($r->idMachine, $machinesData[$key])) $machinesData[$key][] = $r->idMachine;
                }
                foreach ($machinesData as $k => $v) sort($machinesData[$k]);
                $skillMatrix = $skillMatrixStats->map(function($s) use ($machinesData) {
                    $s->machines_list = $machinesData[$s->idMekanik . '_' . $s->typeMachine] ?? [];
                    return $s;
                })->groupBy('idMekanik');
            } elseif ($dataSource === 'downtime_erp') {
                $baseQuery = DowntimeErp::whereYear('date', $currentYear)->whereMonth('date', $currentMonth)
                    ->whereNotNull('nameMekanik')->where('nameMekanik', '!=', '')
                    ->whereNotNull('typeMachine')->where('typeMachine', '!=', '')
                    ->whereNotNull('idMachine')->where('idMachine', '!=', '');
                $mechanicStats = (clone $baseQuery)->select('idMekanik', 'nameMekanik', DB::raw('COUNT(*) as total_repairs'), DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'))
                    ->groupBy('idMekanik', 'nameMekanik')->orderBy('total_repairs', 'desc')->get();
                $skillMatrixStats = (clone $baseQuery)->select('idMekanik', 'nameMekanik', 'typeMachine', DB::raw('COUNT(DISTINCT idMachine) as machine_count'), DB::raw('COUNT(*) as repair_count'), DB::raw('AVG(CAST(duration AS DECIMAL(10,2))) as avg_duration'), DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'))
                    ->groupBy('idMekanik', 'nameMekanik', 'typeMachine')->get();
                $machinesRaw = (clone $baseQuery)->select('idMekanik', 'typeMachine', 'idMachine')->distinct()->get();
                $machinesData = [];
                foreach ($machinesRaw as $r) {
                    $key = $r->idMekanik . '_' . $r->typeMachine;
                    if (!isset($machinesData[$key])) $machinesData[$key] = [];
                    if (!in_array($r->idMachine, $machinesData[$key])) $machinesData[$key][] = $r->idMachine;
                }
                foreach ($machinesData as $k => $v) sort($machinesData[$k]);
                $skillMatrix = $skillMatrixStats->map(function($s) use ($machinesData) {
                    $s->machines_list = $machinesData[$s->idMekanik . '_' . $s->typeMachine] ?? [];
                    return $s;
                })->groupBy('idMekanik');
            } else {
                $mechanicStats = collect();
                $skillMatrix = collect();
            }
            return ['mechanicStats' => $mechanicStats ?? collect(), 'skillMatrix' => $skillMatrix ?? collect()];
        });
        $mechanicStats = $skillMatrixData['mechanicStats'];
        $skillMatrix = $skillMatrixData['skillMatrix'];
        
        return view('dashboard-large', array_merge($stats, [
            'dataSource' => $dataSource, 'currentMonth' => $currentMonth, 'currentYear' => $currentYear,
            'filterMonth' => $filterMonth, 'filterYear' => $filterYear, 'daysInMonth' => $daysInMonth,
            'pmSchedulesThisMonth' => $pmStats['pmSchedulesThisMonth'], 'pmSchedulesPending' => $pmStats['pmSchedulesPending'],
            'pmSchedulesCompleted' => $pmStats['pmSchedulesCompleted'], 'pmSchedulesInProgress' => $pmStats['pmSchedulesInProgress'],
            'pmCompletionRate' => $pmStats['pmCompletionRate'], 'pdmSchedulesThisMonth' => $pdmStats['pdmSchedulesThisMonth'],
            'pdmSchedulesPending' => $pdmStats['pdmSchedulesPending'], 'pdmSchedulesCompleted' => $pdmStats['pdmSchedulesCompleted'],
            'pdmCompletionRate' => $pdmStats['pdmCompletionRate'], 'workOrdersTotal' => $woStats['workOrdersTotal'],
            'workOrdersPending' => $woStats['workOrdersPending'], 'workOrdersInProgress' => $woStats['workOrdersInProgress'],
            'workOrdersCompleted' => $woStats['workOrdersCompleted'], 'workOrdersThisMonth' => $woStats['workOrdersThisMonth'],
            'recentWorkOrders' => $recentWorkOrders, 'totalMachines' => $machinesStats['totalMachines'],
            'machinesWithDowntime' => $machinesStats['machinesWithDowntime'], 'machinesWithPM' => $machinesStats['machinesWithPM'],
            'totalUsers' => $usersStats['totalUsers'], 'totalMechanics' => $usersStats['totalMechanics'],
            'activeMechanics' => $usersStats['activeMechanics'], 'totalStandards' => $standardsStats['totalStandards'],
            'activeStandards' => $standardsStats['activeStandards'], 'totalSpareparts' => $sparepartStats['totalSpareparts'],
            'lowStockSpareparts' => $sparepartStats['lowStockSpareparts'], 'totalStockValue' => $sparepartStats['totalStockValue'],
            'totalPlants' => $locationStats['totalPlants'], 'totalProcesses' => $locationStats['totalProcesses'],
            'totalLines' => $locationStats['totalLines'], 'totalRooms' => $locationStats['totalRooms'],
            'uniqueProblems' => $problemReasonActionStats['uniqueProblems'], 'uniqueReasons' => $problemReasonActionStats['uniqueReasons'],
            'uniqueActions' => $problemReasonActionStats['uniqueActions'], 'uniqueProblemMms' => $problemReasonActionStats['uniqueProblemMms'],
            'redStatusCount' => $predictiveRedStats['redStatusCount'], 'redStatusThisMonth' => $predictiveRedStats['redStatusThisMonth'],
            'totalSystems' => $machineryStats['totalSystems'], 'totalGroups' => $machineryStats['totalGroups'],
            'totalMachineTypes' => $machineryStats['totalMachineTypes'], 'totalBrands' => $machineryStats['totalBrands'],
            'totalModels' => $machineryStats['totalModels'], 'machinesWithBrand' => $machineryStats['machinesWithBrand'],
            'machinesWithModel' => $machineryStats['machinesWithModel'], 'machinesWithType' => $machineryStats['machinesWithType'],
            'upcomingPMSchedules' => $upcomingPMSchedules,
            'upcomingPDMSchedules' => $upcomingPDMSchedules,
            'mechanicStats' => $mechanicStats,
            'skillMatrix' => $skillMatrix
        ]));
    }
    
    /**
     * Dashboard Portrait View - Optimized for Portrait Monitors
     * Uses the same data as the large dashboard, just different view
     */
    public function portrait(Request $request)
    {
        // Get user's dashboard settings (same as large method)
        $user = Auth::user();
        $userSettings = $user ? $user->getDashboardSettings() : [
            'data_source' => 'downtime_erp2',
            'month' => now()->month,
            'year' => now()->year,
        ];
        
        $dataSource = $request->input('data_source', 
            $userSettings['data_source'] ?? 
            session('dashboard_data_source', 'downtime_erp2')
        );
        session(['dashboard_data_source' => $dataSource]);
        
        $filterMonth = $request->get('month', 
            $userSettings['month'] ?? 
            session('dashboard_filter_month', now()->month)
        );
        $filterYear = $request->get('year', 
            $userSettings['year'] ?? 
            session('dashboard_filter_year', now()->year)
        );
        
        $filterMonth = max(1, min(12, (int)$filterMonth));
        $filterYear = max(2000, min(2100, (int)$filterYear));
        
        session([
            'dashboard_filter_month' => $filterMonth,
            'dashboard_filter_year' => $filterYear,
        ]);
        
        $currentMonth = $filterMonth;
        $currentYear = $filterYear;
        
        // Get all stats - same as large method
        $statsCacheKey = 'dashboard_stats_' . $dataSource . '_' . $currentYear . '_' . $currentMonth;
        $stats = Cache::remember($statsCacheKey, 3600, function() use ($dataSource, $currentYear, $currentMonth) {
            if ($dataSource === 'downtime_erp2') {
                return $this->getDowntimeErp2Stats($currentYear, $currentMonth);
            } elseif ($dataSource === 'downtime_erp') {
                return $this->getDowntimeErpStats($currentYear, $currentMonth);
            } else {
                return $this->getDowntimeStats($currentYear, $currentMonth);
            }
        });
        
        // Reuse all cached data from large dashboard
        $pmCacheKey = 'pm_stats_' . $currentYear . '_' . $currentMonth;
        $pmStats = Cache::remember($pmCacheKey, 1800, function() use ($currentYear, $currentMonth) {
            $startDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $pmSchedulesThisMonth = PMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])->count();
            $pmSchedulesPending = PMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])->where('status', 'active')->count();
            $pmSchedulesInProgress = PMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])->where('status', 'active')->count();
            $pmSchedulesCompleted = PMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])->where('status', 'completed')->count();
            $pmCompletionRate = $pmSchedulesThisMonth > 0 ? ($pmSchedulesCompleted / $pmSchedulesThisMonth) * 100 : 0;
            return compact('pmSchedulesThisMonth', 'pmSchedulesPending', 'pmSchedulesInProgress', 'pmSchedulesCompleted', 'pmCompletionRate');
        });
        
        $pdmCacheKey = 'pdm_stats_' . $currentYear . '_' . $currentMonth;
        $pdmStats = Cache::remember($pdmCacheKey, 1800, function() use ($currentYear, $currentMonth) {
            $startDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $pdmSchedulesThisMonth = PDMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])->count();
            $pdmSchedulesPending = PDMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])->where('status', 'active')->count();
            $pdmSchedulesCompleted = PDMScheduling::whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])->where('status', 'completed')->count();
            $pdmCompletionRate = $pdmSchedulesThisMonth > 0 ? ($pdmSchedulesCompleted / $pdmSchedulesThisMonth) * 100 : 0;
            return compact('pdmSchedulesThisMonth', 'pdmSchedulesPending', 'pdmSchedulesCompleted', 'pdmCompletionRate');
        });
        
        $woCacheKey = 'wo_stats_' . $currentYear . '_' . $currentMonth;
        $woStats = Cache::remember($woCacheKey, 1800, function() use ($currentYear, $currentMonth) {
            $startDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            return [
                'workOrdersTotal' => WorkOrder::count(),
                'workOrdersPending' => WorkOrder::where('status', 'pending')->count(),
                'workOrdersInProgress' => WorkOrder::where('status', 'in_progress')->count(),
                'workOrdersCompleted' => WorkOrder::where('status', 'completed')->count(),
                'workOrdersThisMonth' => WorkOrder::whereBetween('order_date', [$startDate, $endDate])->count(),
            ];
        });
        
        $machinesCacheKey = 'machines_stats_' . $currentYear . '_' . $currentMonth;
        $machinesStats = Cache::remember($machinesCacheKey, 3600, function() use ($dataSource, $currentYear, $currentMonth) {
            $machinesWithPM = MachineErp::whereHas('preventiveMaintenanceSchedules')->count();
            $machinesWithPMAlt = DB::table('preventive_maintenance_schedules')->whereNotNull('machine_erp_id')->distinct('machine_erp_id')->count('machine_erp_id');
            $machinesWithPM = max($machinesWithPM, $machinesWithPMAlt);
            if ($dataSource === 'downtime_erp2') {
                $machinesWithDowntime = DowntimeErp2::whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->whereNotNull('idMachine')->where('idMachine', '!=', '')->distinct('idMachine')->count('idMachine');
            } elseif ($dataSource === 'downtime_erp') {
                $machinesWithDowntime = DowntimeErp::whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->whereNotNull('idMachine')->where('idMachine', '!=', '')->distinct('idMachine')->count('idMachine');
            } else {
                $machinesWithDowntime = Downtime::whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->distinct('machine_id')->count('machine_id');
            }
            return ['totalMachines' => MachineErp::count(), 'machinesWithDowntime' => $machinesWithDowntime, 'machinesWithPM' => $machinesWithPM];
        });
        
        $usersStats = Cache::remember('users_stats', 3600, function() use ($dataSource, $currentYear, $currentMonth) {
            $totalUsers = User::count();
            $totalMechanics = User::where('role', 'mechanic')->count();
            if ($dataSource === 'downtime_erp2') {
                $activeMechanics = DowntimeErp2::whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->whereNotNull('idMekanik')->where('idMekanik', '!=', '')->distinct('idMekanik')->count('idMekanik');
            } elseif ($dataSource === 'downtime_erp') {
                $activeMechanics = DowntimeErp::whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->whereNotNull('idMekanik')->where('idMekanik', '!=', '')->distinct('idMekanik')->count('idMekanik');
            } else {
                $activeMechanics = Downtime::whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->distinct('mekanik_id')->count('mekanik_id');
            }
            return compact('totalUsers', 'totalMechanics', 'activeMechanics');
        });
        
        $standardsStats = Cache::remember('standards_stats', 7200, function() {
            return ['totalStandards' => Standard::count(), 'activeStandards' => Standard::where('status', 'active')->count()];
        });
        
        $sparepartStats = Cache::remember('sparepart_stats', 3600, function() {
            return [
                'totalSpareparts' => PartErp::count(),
                'lowStockSpareparts' => PartErp::whereColumn('stock', '<', 'minimum_stock')->where('minimum_stock', '>', 0)->count(),
                'totalStockValue' => PartErp::sum(DB::raw('stock * COALESCE(price, 0)')),
            ];
        });
        
        $locationStats = Cache::remember('location_stats', 7200, function() {
            return ['totalPlants' => Plant::count(), 'totalProcesses' => Process::count(), 'totalLines' => Line::count(), 'totalRooms' => RoomErp::count()];
        });
        
        $problemReasonActionStats = Cache::remember('problem_reason_action_stats', 7200, function() {
            return [
                'uniqueProblems' => Problem::distinct('name')->count('name'),
                'uniqueReasons' => Reason::distinct('name')->count('name'),
                'uniqueActions' => Action::distinct('name')->count('name'),
                'uniqueProblemMms' => ProblemMm::distinct('name')->count('name'),
            ];
        });
        
        $predictiveRedStats = Cache::remember('predictive_red_stats', 1800, function() use ($currentYear, $currentMonth) {
            $startDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            return [
                'redStatusCount' => Standard::where('status', 'red')->count(),
                'redStatusThisMonth' => Standard::where('status', 'red')->whereBetween('updated_at', [$startDate, $endDate])->count(),
            ];
        });
        
        $machineryStats = Cache::remember('machinery_stats', 7200, function() {
            return [
                'totalSystems' => System::count(), 'totalGroups' => Group::count(), 'totalMachineTypes' => MachineType::count(),
                'totalBrands' => Brand::count(), 'totalModels' => Model::count(),
                'machinesWithBrand' => MachineErp::whereNotNull('brand_name')->where('brand_name', '!=', '')->count(),
                'machinesWithModel' => MachineErp::whereNotNull('model_name')->where('model_name', '!=', '')->count(),
                'machinesWithType' => MachineErp::whereNotNull('type_name')->where('type_name', '!=', '')->count(),
            ];
        });
        
        $recentWorkOrders = WorkOrder::orderBy('order_date', 'desc')->orderBy('created_at', 'desc')->limit(5)->get();
        $daysInMonth = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->daysInMonth;
        
        // Upcoming schedules
        $today = \Carbon\Carbon::today();
        $nextWeek = $today->copy()->addDays(7);
        $upcomingPMSchedules = PMScheduling::whereBetween('start_date', [$today->toDateString(), $nextWeek->toDateString()])->where('status', 'active')->orderBy('start_date', 'asc')->limit(5)->get();
        $upcomingPDMSchedules = PDMScheduling::whereBetween('start_date', [$today->toDateString(), $nextWeek->toDateString()])->where('status', 'active')->orderBy('start_date', 'asc')->limit(5)->get();
        
        // Skill matrix data
        $skillMatrixData = Cache::remember('skill_matrix_' . $currentYear . '_' . $currentMonth, 3600, function() use ($dataSource, $currentYear, $currentMonth) {
            if ($dataSource === 'downtime_erp2') {
                $baseQuery = DowntimeErp2::whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->whereNotNull('idMekanik')->where('idMekanik', '!=', '')->whereNotNull('idMachine')->where('idMachine', '!=', '');
                $mechanicStats = (clone $baseQuery)->select('idMekanik', 'nameMekanik', DB::raw('COUNT(*) as total_repairs'), DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'))->groupBy('idMekanik', 'nameMekanik')->orderBy('total_repairs', 'desc')->get();
                $skillMatrixStats = (clone $baseQuery)->select('idMekanik', 'nameMekanik', 'typeMachine', DB::raw('COUNT(DISTINCT idMachine) as machine_count'), DB::raw('COUNT(*) as repair_count'), DB::raw('AVG(CAST(duration AS DECIMAL(10,2))) as avg_duration'), DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'))->groupBy('idMekanik', 'nameMekanik', 'typeMachine')->get();
                $machinesRaw = (clone $baseQuery)->select('idMekanik', 'typeMachine', 'idMachine')->distinct()->get();
                $machinesData = [];
                foreach ($machinesRaw as $r) {
                    $key = $r->idMekanik . '_' . $r->typeMachine;
                    if (!isset($machinesData[$key])) $machinesData[$key] = [];
                    if (!in_array($r->idMachine, $machinesData[$key])) $machinesData[$key][] = $r->idMachine;
                }
                foreach ($machinesData as $k => $v) sort($machinesData[$k]);
                $skillMatrix = $skillMatrixStats->map(function($s) use ($machinesData) {
                    $s->machines_list = $machinesData[$s->idMekanik . '_' . $s->typeMachine] ?? [];
                    return $s;
                })->groupBy('idMekanik');
            } else {
                $mechanicStats = collect();
                $skillMatrix = collect();
            }
            return ['mechanicStats' => $mechanicStats ?? collect(), 'skillMatrix' => $skillMatrix ?? collect()];
        });
        $mechanicStats = $skillMatrixData['mechanicStats'];
        $skillMatrix = $skillMatrixData['skillMatrix'];
        
        return view('dashboard-portrait', array_merge($stats, [
            'dataSource' => $dataSource, 'currentMonth' => $currentMonth, 'currentYear' => $currentYear,
            'filterMonth' => $filterMonth, 'filterYear' => $filterYear, 'daysInMonth' => $daysInMonth,
            'pmSchedulesThisMonth' => $pmStats['pmSchedulesThisMonth'], 'pmSchedulesPending' => $pmStats['pmSchedulesPending'],
            'pmSchedulesCompleted' => $pmStats['pmSchedulesCompleted'], 'pmSchedulesInProgress' => $pmStats['pmSchedulesInProgress'] ?? 0,
            'pmCompletionRate' => $pmStats['pmCompletionRate'], 'pdmSchedulesThisMonth' => $pdmStats['pdmSchedulesThisMonth'],
            'pdmSchedulesPending' => $pdmStats['pdmSchedulesPending'], 'pdmSchedulesCompleted' => $pdmStats['pdmSchedulesCompleted'],
            'pdmCompletionRate' => $pdmStats['pdmCompletionRate'], 'workOrdersTotal' => $woStats['workOrdersTotal'],
            'workOrdersPending' => $woStats['workOrdersPending'], 'workOrdersInProgress' => $woStats['workOrdersInProgress'],
            'workOrdersCompleted' => $woStats['workOrdersCompleted'], 'workOrdersThisMonth' => $woStats['workOrdersThisMonth'],
            'recentWorkOrders' => $recentWorkOrders, 'totalMachines' => $machinesStats['totalMachines'],
            'machinesWithDowntime' => $machinesStats['machinesWithDowntime'], 'machinesWithPM' => $machinesStats['machinesWithPM'],
            'totalUsers' => $usersStats['totalUsers'], 'totalMechanics' => $usersStats['totalMechanics'],
            'activeMechanics' => $usersStats['activeMechanics'], 'totalStandards' => $standardsStats['totalStandards'],
            'activeStandards' => $standardsStats['activeStandards'], 'totalSpareparts' => $sparepartStats['totalSpareparts'],
            'lowStockSpareparts' => $sparepartStats['lowStockSpareparts'], 'totalStockValue' => $sparepartStats['totalStockValue'],
            'totalPlants' => $locationStats['totalPlants'], 'totalProcesses' => $locationStats['totalProcesses'],
            'totalLines' => $locationStats['totalLines'], 'totalRooms' => $locationStats['totalRooms'],
            'uniqueProblems' => $problemReasonActionStats['uniqueProblems'], 'uniqueReasons' => $problemReasonActionStats['uniqueReasons'],
            'uniqueActions' => $problemReasonActionStats['uniqueActions'], 'uniqueProblemMms' => $problemReasonActionStats['uniqueProblemMms'],
            'redStatusCount' => $predictiveRedStats['redStatusCount'], 'redStatusThisMonth' => $predictiveRedStats['redStatusThisMonth'],
            'totalSystems' => $machineryStats['totalSystems'], 'totalGroups' => $machineryStats['totalGroups'],
            'totalMachineTypes' => $machineryStats['totalMachineTypes'], 'totalBrands' => $machineryStats['totalBrands'],
            'totalModels' => $machineryStats['totalModels'], 'machinesWithBrand' => $machineryStats['machinesWithBrand'],
            'machinesWithModel' => $machineryStats['machinesWithModel'], 'machinesWithType' => $machineryStats['machinesWithType'],
            'upcomingPMSchedules' => $upcomingPMSchedules,
            'upcomingPDMSchedules' => $upcomingPDMSchedules,
            'mechanicStats' => $mechanicStats,
            'skillMatrix' => $skillMatrix
        ]));
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
        
        // Top 10 machines by downtime duration
        $topMachines = DowntimeErp2::select(
                'idMachine',
                DB::raw('MAX(typeMachine) as typeMachine'),
                DB::raw('MAX(modelMachine) as modelMachine'),
                DB::raw('MAX(brandMachine) as brandMachine'),
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

        // Top 10 MTTR (Mean Time To Repair = total duration / count)
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
            ->groupBy('idMachine')
            ->havingRaw('COUNT(*) > 0')
            ->orderBy('mttr', 'desc')
            ->limit(10)
            ->get();

        // Top 10 MTBF (Mean Time Between Failures)
        // MTBF = (Total Available Time - Total Downtime) / Number of Failures
        $totalAvailableMinutes = 30 * 24 * 60; // Assuming 30 days, 24 hours per day
        $topMTBF = DowntimeErp2::select(
                'idMachine',
                DB::raw('MAX(typeMachine) as typeMachine'),
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                DB::raw('COUNT(*) as failure_count')
            )
            ->whereNotNull('idMachine')
            ->where('idMachine', '!=', '')
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->groupBy('idMachine')
            ->havingRaw('COUNT(*) > 0')
            ->get()
            ->map(function ($item) use ($totalAvailableMinutes) {
                $operatingTime = $totalAvailableMinutes - ($item->total_duration ?? 0);
                $item->mtbf = $item->failure_count > 0 ? $operatingTime / $item->failure_count : 0;
                return $item;
            })
            ->sortByDesc('mtbf')
            ->take(10)
            ->values();

        // Top 5 plants by downtime duration
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
        
        // Downtime trend per day
        $downtimeTrend = DowntimeErp2::select(
                'date',
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                DB::raw('COUNT(*) as count')
            )
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        // Top 5 problems
        $topProblems = DowntimeErp2::select(
                'problemDowntime',
                DB::raw('COUNT(*) as problem_count')
            )
            ->whereNotNull('problemDowntime')
            ->where('problemDowntime', '!=', '')
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->groupBy('problemDowntime')
            ->orderBy('problem_count', 'desc')
            ->limit(5)
            ->get();

        // Top mekanik by downtime count
        $topMekanik = DowntimeErp2::select(
                'nameMekanik',
                DB::raw('COUNT(*) as downtime_count')
            )
            ->whereNotNull('nameMekanik')
            ->where('nameMekanik', '!=', '')
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->groupBy('nameMekanik')
            ->orderBy('downtime_count', 'desc')
            ->limit(5)
            ->get();

        // Top lines by downtime duration
        $topLines = DowntimeErp2::select(
                'line',
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration')
            )
            ->whereNotNull('line')
            ->where('line', '!=', '')
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->groupBy('line')
            ->orderBy('total_duration', 'desc')
            ->limit(5)
            ->get();

        // Recent downtime events (last 10)
        $recentDowntimeErps = DowntimeErp2::whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->orderBy('date', 'desc')
            ->orderByRaw('CAST(duration AS DECIMAL(10,2)) DESC')
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
            'topMTBF' => $topMTBF,
            'topPlants' => $topPlants,
            'downtimeTrend' => $downtimeTrend,
            'topProblems' => $topProblems,
            'topMekanik' => $topMekanik,
            'topLines' => $topLines,
            'recentDowntimeErps' => $recentDowntimeErps,
        ];
    }
    
    /**
     * Get statistics from DowntimeErp table
     */
    private function getDowntimeErpStats($currentYear, $currentMonth)
    {
        $monthDowntimeCount = DowntimeErp::whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->count();
        $monthDowntime = DowntimeErp::whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->get()->sum(function($item) { return (float) ($item->duration ?? 0); });
        $avgDowntimeDuration = $monthDowntimeCount > 0 ? $monthDowntime / $monthDowntimeCount : 0;
        $daysInMonth = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->daysInMonth;
        $avgDowntimePerDay = $monthDowntime / $daysInMonth;
        $mostProblematicMachine = DowntimeErp::select('idMachine', DB::raw('MAX(typeMachine) as typeMachine'), DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'), DB::raw('COUNT(*) as downtime_count'))->whereNotNull('idMachine')->where('idMachine', '!=', '')->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->groupBy('idMachine')->orderBy('total_duration', 'desc')->first();
        $longestDowntime = DowntimeErp::whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->orderByRaw('CAST(duration AS DECIMAL(10,2)) DESC')->first();
        $topMachines = DowntimeErp::select('idMachine', DB::raw('MAX(typeMachine) as typeMachine'), DB::raw('MAX(modelMachine) as modelMachine'), DB::raw('MAX(brandMachine) as brandMachine'), DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'), DB::raw('COUNT(*) as downtime_count'))->whereNotNull('idMachine')->where('idMachine', '!=', '')->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->groupBy('idMachine')->orderBy('total_duration', 'desc')->limit(10)->get();
        $topMTTR = DowntimeErp::select('idMachine', DB::raw('MAX(typeMachine) as typeMachine'), DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'), DB::raw('COUNT(*) as downtime_count'), DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) / COUNT(*) as mttr'))->whereNotNull('idMachine')->where('idMachine', '!=', '')->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->groupBy('idMachine')->havingRaw('COUNT(*) > 0')->orderBy('mttr', 'desc')->limit(10)->get();
        
        // Top 10 MTBF (Mean Time Between Failures)
        $totalAvailableMinutes = 30 * 24 * 60; // Assuming 30 days, 24 hours per day
        $topMTBF = DowntimeErp::select('idMachine', DB::raw('MAX(typeMachine) as typeMachine'), DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'), DB::raw('COUNT(*) as failure_count'))
            ->whereNotNull('idMachine')->where('idMachine', '!=', '')->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)
            ->groupBy('idMachine')->havingRaw('COUNT(*) > 0')->get()
            ->map(function ($item) use ($totalAvailableMinutes) {
                $operatingTime = $totalAvailableMinutes - ($item->total_duration ?? 0);
                $item->mtbf = $item->failure_count > 0 ? $operatingTime / $item->failure_count : 0;
                return $item;
            })->sortByDesc('mtbf')->take(10)->values();
        
        $topPlants = DowntimeErp::select('plant', DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'), DB::raw('COUNT(*) as downtime_count'))->whereNotNull('plant')->where('plant', '!=', '')->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->groupBy('plant')->orderBy('total_duration', 'desc')->limit(5)->get();
        $downtimeTrend = DowntimeErp::select('date', DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'), DB::raw('COUNT(*) as count'))->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->groupBy('date')->orderBy('date', 'asc')->get();
        $topProblems = DowntimeErp::select('problemDowntime', DB::raw('COUNT(*) as problem_count'))->whereNotNull('problemDowntime')->where('problemDowntime', '!=', '')->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->groupBy('problemDowntime')->orderBy('problem_count', 'desc')->limit(5)->get();
        $topMekanik = DowntimeErp::select('nameMekanik', DB::raw('COUNT(*) as downtime_count'))->whereNotNull('nameMekanik')->where('nameMekanik', '!=', '')->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->groupBy('nameMekanik')->orderBy('downtime_count', 'desc')->limit(5)->get();
        $topLines = DowntimeErp::select('line', DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'))->whereNotNull('line')->where('line', '!=', '')->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->groupBy('line')->orderBy('total_duration', 'desc')->limit(5)->get();
        $recentDowntimeErps = DowntimeErp::whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->orderBy('date', 'desc')->orderByRaw('CAST(duration AS DECIMAL(10,2)) DESC')->limit(10)->get();
        return compact('monthDowntimeCount', 'monthDowntime', 'avgDowntimeDuration', 'avgDowntimePerDay', 'mostProblematicMachine', 'longestDowntime', 'topMachines', 'topMTTR', 'topMTBF', 'topPlants', 'downtimeTrend', 'topProblems', 'topMekanik', 'topLines', 'recentDowntimeErps');
    }
    
    /**
     * Get statistics from Downtime table
     */
    private function getDowntimeStats($currentYear, $currentMonth)
    {
        $monthDowntimeCount = Downtime::whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->count();
        $monthDowntime = Downtime::whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->get()->sum(function($item) { return (float) ($item->duration ?? 0); });
        $avgDowntimeDuration = $monthDowntimeCount > 0 ? $monthDowntime / $monthDowntimeCount : 0;
        $daysInMonth = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->daysInMonth;
        $avgDowntimePerDay = $monthDowntime / $daysInMonth;
        $mostProblematicMachine = Downtime::with('machine')->select('machine_id', DB::raw('SUM(duration) as total_duration'), DB::raw('COUNT(*) as downtime_count'))->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->groupBy('machine_id')->orderBy('total_duration', 'desc')->first();
        $longestDowntime = Downtime::with('machine')->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->orderBy('duration', 'desc')->first();
        $topMachines = Downtime::with('machine.machineType')->select('machine_id', DB::raw('SUM(duration) as total_duration'), DB::raw('COUNT(*) as downtime_count'))->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->groupBy('machine_id')->orderBy('total_duration', 'desc')->limit(10)->get();
        $topMTTR = Downtime::select('machine_id', DB::raw('SUM(duration) as total_duration'), DB::raw('COUNT(*) as downtime_count'), DB::raw('SUM(duration) / COUNT(*) as mttr'))->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->groupBy('machine_id')->havingRaw('COUNT(*) > 0')->orderBy('mttr', 'desc')->limit(10)->get();
        
        // Top 10 MTBF (Mean Time Between Failures)
        $totalAvailableMinutes = 30 * 24 * 60;
        $topMTBF = Downtime::select('machine_id', DB::raw('SUM(duration) as total_duration'), DB::raw('COUNT(*) as failure_count'))
            ->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->groupBy('machine_id')->havingRaw('COUNT(*) > 0')->get()
            ->map(function ($item) use ($totalAvailableMinutes) {
                $operatingTime = $totalAvailableMinutes - ($item->total_duration ?? 0);
                $item->mtbf = $item->failure_count > 0 ? $operatingTime / $item->failure_count : 0;
                $item->idMachine = $item->machine_id; // Alias for consistency
                return $item;
            })->sortByDesc('mtbf')->take(10)->values();
        
        $topPlants = Downtime::with('machine.plant')->select('machine_id', DB::raw('SUM(duration) as total_duration'), DB::raw('COUNT(*) as downtime_count'))->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->groupBy('machine_id')->orderBy('total_duration', 'desc')->limit(5)->get()->map(function($item) { return (object)['plant' => $item->machine->plant->name ?? 'N/A', 'total_duration' => $item->total_duration, 'downtime_count' => $item->downtime_count]; });
        $downtimeTrend = Downtime::select('date', DB::raw('SUM(duration) as total_duration'), DB::raw('COUNT(*) as count'))->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->groupBy('date')->orderBy('date', 'asc')->get();
        $topProblems = Downtime::with('problem')->select('problem_id', DB::raw('COUNT(*) as problem_count'))->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->groupBy('problem_id')->orderBy('problem_count', 'desc')->limit(5)->get()->map(function($item) { return (object)['problemDowntime' => $item->problem->name ?? 'N/A', 'problem_count' => $item->problem_count]; });
        $topMekanik = Downtime::with('mekanik')->select('mekanik_id', DB::raw('COUNT(*) as downtime_count'))->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->groupBy('mekanik_id')->orderBy('downtime_count', 'desc')->limit(5)->get()->map(function($item) { return (object)['nameMekanik' => $item->mekanik->name ?? 'N/A', 'downtime_count' => $item->downtime_count]; });
        $topLines = Downtime::with('machine.line')->select('machine_id', DB::raw('SUM(duration) as total_duration'))->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->groupBy('machine_id')->orderBy('total_duration', 'desc')->limit(5)->get()->map(function($item) { return (object)['line' => $item->machine->line->name ?? 'N/A', 'total_duration' => $item->total_duration]; });
        $recentDowntimeErps = Downtime::with(['machine.machineType', 'problem', 'mekanik', 'machine.plant', 'machine.line'])->whereYear('date', $currentYear)->whereMonth('date', $currentMonth)->orderBy('date', 'desc')->orderBy('duration', 'desc')->limit(10)->get();
        return compact('monthDowntimeCount', 'monthDowntime', 'avgDowntimeDuration', 'avgDowntimePerDay', 'mostProblematicMachine', 'longestDowntime', 'topMachines', 'topMTTR', 'topMTBF', 'topPlants', 'downtimeTrend', 'topProblems', 'topMekanik', 'topLines', 'recentDowntimeErps');
    }
}

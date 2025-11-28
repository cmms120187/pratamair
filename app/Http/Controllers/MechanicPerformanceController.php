<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Downtime;
use App\Models\DowntimeErp;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MechanicPerformanceController extends Controller
{
    public function index(Request $request)
    {
        // Get data source from request or session, default to 'downtime'
        $dataSource = $request->input('data_source', session('mechanic_performance_data_source', 'downtime'));
        session(['mechanic_performance_data_source' => $dataSource]);
        
        // Default to "all" (no filter)
        $selectedMonth = $request->input('month', 'all');
        $selectedYear = $request->input('year', 'all');
        
        // Build base query
        if ($dataSource === 'downtime_erp') {
            $baseQuery = DowntimeErp::query()
                ->whereNotNull('nameMekanik')
                ->where('nameMekanik', '!=', '');
            
            // Apply month filter if not "all"
            if ($selectedMonth !== 'all' && is_numeric($selectedMonth)) {
                $baseQuery->whereMonth('date', $selectedMonth);
            }
            
            // Apply year filter if not "all"
            if ($selectedYear !== 'all' && is_numeric($selectedYear)) {
                $baseQuery->whereYear('date', $selectedYear);
            }
            
            // If both are "all", limit to last 2 years for performance
            if ($selectedMonth === 'all' && $selectedYear === 'all') {
                $baseQuery->where('date', '>=', Carbon::now()->subYears(2)->startOfYear());
            }
            
            // Apply filters
            if ($request->filled('plant')) {
                $baseQuery->where('plant', $request->plant);
            }
            if ($request->filled('process')) {
                $baseQuery->where('process', $request->process);
            }
            if ($request->filled('line')) {
                $baseQuery->where('line', $request->line);
            }
            if ($request->filled('room')) {
                $baseQuery->where('roomName', $request->room);
            }
            if ($request->filled('typeMachine')) {
                $baseQuery->where('typeMachine', $request->typeMachine);
            }
        } else {
            // Use Downtime model with joins
            $baseQuery = Downtime::query()
                ->join('users', 'downtimes.mekanik_id', '=', 'users.id')
                ->whereNotNull('users.name');
            
            // Apply month filter if not "all"
            if ($selectedMonth !== 'all' && is_numeric($selectedMonth)) {
                $baseQuery->whereMonth('downtimes.date', $selectedMonth);
            }
            
            // Apply year filter if not "all"
            if ($selectedYear !== 'all' && is_numeric($selectedYear)) {
                $baseQuery->whereYear('downtimes.date', $selectedYear);
            }
            
            // If both are "all", limit to last 2 years for performance
            if ($selectedMonth === 'all' && $selectedYear === 'all') {
                $baseQuery->where('downtimes.date', '>=', Carbon::now()->subYears(2)->startOfYear());
            }
            
            // Apply filters
            if ($request->filled('plant')) {
                $baseQuery->whereHas('machine', function($q) use ($request) {
                    $q->whereHas('plant', function($q2) use ($request) {
                        $q2->where('name', $request->plant);
                    });
                });
            }
            if ($request->filled('process')) {
                $baseQuery->whereHas('machine', function($q) use ($request) {
                    $q->whereHas('process', function($q2) use ($request) {
                        $q2->where('name', $request->process);
                    });
                });
            }
            if ($request->filled('line')) {
                $baseQuery->whereHas('machine', function($q) use ($request) {
                    $q->whereHas('line', function($q2) use ($request) {
                        $q2->where('name', $request->line);
                    });
                });
            }
            if ($request->filled('room')) {
                $baseQuery->whereHas('machine', function($q) use ($request) {
                    $q->whereHas('room', function($q2) use ($request) {
                        $q2->where('name', $request->room);
                    });
                });
            }
            if ($request->filled('typeMachine')) {
                $baseQuery->whereHas('machine', function($q) use ($request) {
                    $q->whereHas('machineType', function($q2) use ($request) {
                        $q2->where('name', $request->typeMachine);
                    });
                });
            }
        }
        
        // ========== MECHANIC PERFORMANCE STATISTICS ==========
        if ($dataSource === 'downtime_erp') {
            $mechanicStats = (clone $baseQuery)
                ->select(
                    'idMekanik',
                    'nameMekanik',
                    DB::raw('COUNT(*) as total_repairs'),
                    DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                    DB::raw('AVG(CAST(duration AS DECIMAL(10,2))) as avg_duration'),
                    DB::raw('MIN(CAST(duration AS DECIMAL(10,2))) as min_duration'),
                    DB::raw('MAX(CAST(duration AS DECIMAL(10,2))) as max_duration')
                )
                ->groupBy('idMekanik', 'nameMekanik')
                ->orderBy('total_repairs', 'desc')
                ->get();
            
            // ========== SKILL MATRIX: Type Machines per Mechanic ==========
            $skillMatrixStats = (clone $baseQuery)
                ->select(
                    'idMekanik',
                    'nameMekanik',
                    'typeMachine',
                    DB::raw('COUNT(DISTINCT idMachine) as machine_count'),
                    DB::raw('COUNT(*) as repair_count'),
                    DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                    DB::raw('AVG(CAST(duration AS DECIMAL(10,2))) as avg_duration')
                )
                ->whereNotNull('typeMachine')
                ->where('typeMachine', '!=', '')
                ->whereNotNull('idMachine')
                ->where('idMachine', '!=', '')
                ->groupBy('idMekanik', 'nameMekanik', 'typeMachine')
                ->get();
            
            $machinesDataRaw = (clone $baseQuery)
                ->select('idMekanik', 'typeMachine', 'idMachine')
                ->whereNotNull('typeMachine')
                ->where('typeMachine', '!=', '')
                ->whereNotNull('idMachine')
                ->where('idMachine', '!=', '')
                ->distinct()
                ->orderBy('idMekanik')
                ->orderBy('typeMachine')
                ->orderBy('idMachine')
                ->get();
        } else {
            $mechanicStats = (clone $baseQuery)
                ->select(
                    DB::raw('users.id as idMekanik'),
                    DB::raw('users.name as nameMekanik'),
                    DB::raw('COUNT(*) as total_repairs'),
                    DB::raw('SUM(downtimes.duration) as total_duration'),
                    DB::raw('AVG(downtimes.duration) as avg_duration'),
                    DB::raw('MIN(downtimes.duration) as min_duration'),
                    DB::raw('MAX(downtimes.duration) as max_duration')
                )
                ->groupBy('users.id', 'users.name')
                ->orderBy('total_repairs', 'desc')
                ->get();
            
            // ========== SKILL MATRIX: Type Machines per Mechanic ==========
            $skillMatrixStats = (clone $baseQuery)
                ->select(
                    DB::raw('users.id as idMekanik'),
                    DB::raw('users.name as nameMekanik'),
                    DB::raw('machine_types.name as typeMachine'),
                    DB::raw('COUNT(DISTINCT machines.idMachine) as machine_count'),
                    DB::raw('COUNT(*) as repair_count'),
                    DB::raw('SUM(downtimes.duration) as total_duration'),
                    DB::raw('AVG(downtimes.duration) as avg_duration')
                )
                ->join('machines', 'downtimes.machine_id', '=', 'machines.id')
                ->leftJoin('machine_types', 'machines.type_id', '=', 'machine_types.id')
                ->whereNotNull('machine_types.name')
                ->whereNotNull('machines.idMachine')
                ->groupBy('users.id', 'users.name', 'machine_types.name')
                ->get();
            
            $machinesDataRaw = (clone $baseQuery)
                ->select(
                    DB::raw('users.id as idMekanik'),
                    DB::raw('machine_types.name as typeMachine'),
                    DB::raw('machines.idMachine as idMachine')
                )
                ->join('machines', 'downtimes.machine_id', '=', 'machines.id')
                ->leftJoin('machine_types', 'machines.type_id', '=', 'machine_types.id')
                ->whereNotNull('machine_types.name')
                ->whereNotNull('machines.idMachine')
                ->distinct()
                ->orderBy('users.id')
                ->orderBy('machine_types.name')
                ->orderBy('machines.idMachine')
                ->get();
        }
        
        // Group by mechanic and type machine manually for better performance
        $machinesData = [];
        foreach ($machinesDataRaw as $row) {
            $key = $row->idMekanik . '_' . $row->typeMachine;
            if (!isset($machinesData[$key])) {
                $machinesData[$key] = [];
            }
            if (!in_array($row->idMachine, $machinesData[$key])) {
                $machinesData[$key][] = $row->idMachine;
            }
        }
        
        // Sort each machine list
        foreach ($machinesData as $key => $machines) {
            sort($machinesData[$key]);
        }
        
        // Combine stats with machine lists
        $skillMatrix = $skillMatrixStats->map(function($stat) use ($machinesData) {
            $key = $stat->idMekanik . '_' . $stat->typeMachine;
            $stat->machines_list = $machinesData[$key] ?? [];
            return $stat;
        })->groupBy('idMekanik');
        
        // ========== TOP MECHANICS ==========
        if ($dataSource === 'downtime_erp') {
            $topMechanics = (clone $baseQuery)
                ->select(
                    'idMekanik',
                    'nameMekanik',
                    DB::raw('COUNT(*) as total_repairs'),
                    DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                    DB::raw('AVG(CAST(duration AS DECIMAL(10,2))) as avg_duration')
                )
                ->groupBy('idMekanik', 'nameMekanik')
                ->orderBy('total_repairs', 'desc')
                ->limit(10)
                ->get();
            
            // Get unique values for filters
            $filterQuery = DowntimeErp::query();
            if ($selectedMonth !== 'all' && is_numeric($selectedMonth)) {
                $filterQuery->whereMonth('date', $selectedMonth);
            }
            if ($selectedYear !== 'all' && is_numeric($selectedYear)) {
                $filterQuery->whereYear('date', $selectedYear);
            }
            if ($selectedMonth === 'all' && $selectedYear === 'all') {
                $filterQuery->where('date', '>=', Carbon::now()->subYears(2)->startOfYear());
            }
            
            $plants = (clone $filterQuery)->distinct()->whereNotNull('plant')->where('plant', '!=', '')->orderBy('plant')->pluck('plant')->unique();
            $processes = (clone $filterQuery)->distinct()->whereNotNull('process')->where('process', '!=', '')->orderBy('process')->pluck('process')->unique();
            $lines = (clone $filterQuery)->distinct()->whereNotNull('line')->where('line', '!=', '')->orderBy('line')->pluck('line')->unique();
            $rooms = (clone $filterQuery)->distinct()->whereNotNull('roomName')->where('roomName', '!=', '')->orderBy('roomName')->pluck('roomName')->unique();
            $typeMachines = (clone $filterQuery)->distinct()->whereNotNull('typeMachine')->where('typeMachine', '!=', '')->orderBy('typeMachine')->pluck('typeMachine')->unique();
        } else {
            $topMechanics = (clone $baseQuery)
                ->select(
                    DB::raw('users.id as idMekanik'),
                    DB::raw('users.name as nameMekanik'),
                    DB::raw('COUNT(*) as total_repairs'),
                    DB::raw('SUM(downtimes.duration) as total_duration'),
                    DB::raw('AVG(downtimes.duration) as avg_duration')
                )
                ->groupBy('users.id', 'users.name')
                ->orderBy('total_repairs', 'desc')
                ->limit(10)
                ->get();
            
            // Get unique values for filters
            $filterQuery = Downtime::query();
            if ($selectedMonth !== 'all' && is_numeric($selectedMonth)) {
                $filterQuery->whereMonth('date', $selectedMonth);
            }
            if ($selectedYear !== 'all' && is_numeric($selectedYear)) {
                $filterQuery->whereYear('date', $selectedYear);
            }
            if ($selectedMonth === 'all' && $selectedYear === 'all') {
                $filterQuery->where('date', '>=', Carbon::now()->subYears(2)->startOfYear());
            }
            
            $plants = \App\Models\Plant::whereHas('machines.downtimes', function($q) use ($filterQuery) {
                $q->whereIn('downtimes.id', $filterQuery->pluck('id'));
            })->orderBy('name')->pluck('name')->unique();
            
            $processes = \App\Models\Process::whereHas('machines.downtimes', function($q) use ($filterQuery) {
                $q->whereIn('downtimes.id', $filterQuery->pluck('id'));
            })->orderBy('name')->pluck('name')->unique();
            
            $lines = \App\Models\Line::whereHas('machines.downtimes', function($q) use ($filterQuery) {
                $q->whereIn('downtimes.id', $filterQuery->pluck('id'));
            })->orderBy('name')->pluck('name')->unique();
            
            $rooms = \App\Models\Room::whereHas('machines.downtimes', function($q) use ($filterQuery) {
                $q->whereIn('downtimes.id', $filterQuery->pluck('id'));
            })->orderBy('name')->pluck('name')->unique();
            
            $typeMachines = \App\Models\MachineType::whereHas('machines.downtimes', function($q) use ($filterQuery) {
                $q->whereIn('downtimes.id', $filterQuery->pluck('id'));
            })->orderBy('name')->pluck('name')->unique();
        }
        
        return view('mechanic_performance.index', compact(
            'dataSource',
            'mechanicStats',
            'skillMatrix',
            'topMechanics',
            'selectedMonth',
            'selectedYear',
            'plants',
            'processes',
            'lines',
            'rooms',
            'typeMachines'
        ));
    }
}


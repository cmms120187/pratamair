<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Downtime;
use App\Models\DowntimeErp;
use App\Models\DowntimeErp2;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MTTRMTBFController extends Controller
{
    public function index(Request $request)
    {
        // Get data source from request or session, default to 'downtime_erp2'
        $dataSource = $request->input('data_source', session('mttr_mtbf_data_source', 'downtime_erp2'));
        session(['mttr_mtbf_data_source' => $dataSource]);
        
        // Default to current month and year
        $selectedMonth = $request->input('month', now()->month);
        $selectedYear = $request->input('year', now()->year);
        
        // Build base query
        if ($dataSource === 'downtime_erp2') {
            $baseQuery = DowntimeErp2::query()
                ->whereNotNull('idMachine')
                ->where('idMachine', '!=', '')
                ->whereYear('date', $selectedYear)
                ->whereMonth('date', $selectedMonth);
            
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
        } elseif ($dataSource === 'downtime_erp') {
            $baseQuery = DowntimeErp::query()
                ->whereNotNull('idMachine')
                ->where('idMachine', '!=', '')
                ->whereYear('date', $selectedYear)
                ->whereMonth('date', $selectedMonth);
            
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
                ->join('machines', 'downtimes.machine_id', '=', 'machines.id')
                ->whereNotNull('machines.idMachine')
                ->whereYear('downtimes.date', $selectedYear)
                ->whereMonth('downtimes.date', $selectedMonth);
            
            // Apply filters
            if ($request->filled('plant')) {
                $baseQuery->join('plants', 'machines.plant_id', '=', 'plants.id')
                    ->where('plants.name', $request->plant);
            }
            if ($request->filled('process')) {
                $baseQuery->join('processes', 'machines.process_id', '=', 'processes.id')
                    ->where('processes.name', $request->process);
            }
            if ($request->filled('line')) {
                $baseQuery->join('lines', 'machines.line_id', '=', 'lines.id')
                    ->where('lines.name', $request->line);
            }
            if ($request->filled('room')) {
                $baseQuery->join('rooms', 'machines.room_id', '=', 'rooms.id')
                    ->where('rooms.name', $request->room);
            }
            if ($request->filled('typeMachine')) {
                $baseQuery->join('machine_types', 'machines.type_id', '=', 'machine_types.id')
                    ->where('machine_types.name', $request->typeMachine);
            }
        }
        
        // Calculate available time (assume 24 hours operation per day)
        $daysInMonth = Carbon::create($selectedYear, $selectedMonth, 1)->daysInMonth;
        $hoursPerDay = 24; // Can be adjusted based on operation hours
        $totalAvailableMinutes = $daysInMonth * $hoursPerDay * 60;
        
        // ========== MTTR (Mean Time To Repair) ==========
        // MTTR = Total Duration / Total Frequency
        if ($dataSource === 'downtime_erp2' || $dataSource === 'downtime_erp') {
            $mttrData = (clone $baseQuery)->select(
                    'idMachine',
                    DB::raw('MAX(typeMachine) as typeMachine'),
                    DB::raw('MAX(plant) as plant'),
                    DB::raw('MAX(line) as line'),
                    DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                    DB::raw('COUNT(*) as downtime_count'),
                    DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) / COUNT(*) as mttr')
                )
                ->havingRaw('COUNT(*) > 0')
                ->groupBy('idMachine')
                ->orderBy('mttr', 'desc')
                ->get();
            
            $overallDowntimeCount = (clone $baseQuery)->count();
            $overallDowntimeDuration = (clone $baseQuery)->get()->sum(function($item) {
                return (float) ($item->duration ?? 0);
            });
        } else {
            $mttrData = (clone $baseQuery)->select(
                    DB::raw('machines.idMachine as idMachine'),
                    DB::raw('MAX(machine_types.name) as typeMachine'),
                    DB::raw('MAX(plants.name) as plant'),
                    DB::raw('MAX(lines.name) as line'),
                    DB::raw('SUM(downtimes.duration) as total_duration'),
                    DB::raw('COUNT(*) as downtime_count'),
                    DB::raw('SUM(downtimes.duration) / COUNT(*) as mttr')
                )
                ->leftJoin('machine_types', 'machines.type_id', '=', 'machine_types.id')
                ->leftJoin('plants', 'machines.plant_id', '=', 'plants.id')
                ->leftJoin('lines', 'machines.line_id', '=', 'lines.id')
                ->havingRaw('COUNT(*) > 0')
                ->groupBy('machines.idMachine')
                ->orderBy('mttr', 'desc')
                ->get();
            
            $overallDowntimeCount = (clone $baseQuery)->count();
            $overallDowntimeDuration = (clone $baseQuery)->sum('duration');
        }
        
        $overallMTTR = $overallDowntimeCount > 0 ? $overallDowntimeDuration / $overallDowntimeCount : 0;
        
        // ========== MTBF (Mean Time Between Failures) ==========
        // MTBF = (Total Available Time - Total Downtime) / Number of Failures
        if ($dataSource === 'downtime_erp2' || $dataSource === 'downtime_erp') {
            $mtbfData = (clone $baseQuery)->select(
                    'idMachine',
                    DB::raw('MAX(typeMachine) as typeMachine'),
                    DB::raw('MAX(plant) as plant'),
                    DB::raw('MAX(line) as line'),
                    DB::raw('COUNT(*) as failure_count'),
                    DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration')
                )
                ->groupBy('idMachine')
                ->get()
                ->map(function($item) use ($totalAvailableMinutes) {
                    $totalDowntime = (float) ($item->total_duration ?? 0);
                    $operatingTime = $totalAvailableMinutes - $totalDowntime;
                    $failureCount = $item->failure_count ?? 0;
                    $mtbf = $failureCount > 0 ? $operatingTime / $failureCount : 0;
                    
                    return [
                        'idMachine' => $item->idMachine,
                        'typeMachine' => $item->typeMachine,
                        'plant' => $item->plant,
                        'line' => $item->line,
                        'failure_count' => $failureCount,
                        'total_duration' => $totalDowntime,
                        'operating_time' => $operatingTime,
                        'mtbf' => $mtbf
                    ];
                })
                ->sortByDesc('mtbf')
                ->values();
        } else {
            $mtbfData = (clone $baseQuery)->select(
                    DB::raw('machines.idMachine as idMachine'),
                    DB::raw('MAX(machine_types.name) as typeMachine'),
                    DB::raw('MAX(plants.name) as plant'),
                    DB::raw('MAX(lines.name) as line'),
                    DB::raw('COUNT(*) as failure_count'),
                    DB::raw('SUM(downtimes.duration) as total_duration')
                )
                ->leftJoin('machine_types', 'machines.type_id', '=', 'machine_types.id')
                ->leftJoin('plants', 'machines.plant_id', '=', 'plants.id')
                ->leftJoin('lines', 'machines.line_id', '=', 'lines.id')
                ->groupBy('machines.idMachine')
                ->get()
                ->map(function($item) use ($totalAvailableMinutes) {
                    $totalDowntime = (float) ($item->total_duration ?? 0);
                    $operatingTime = $totalAvailableMinutes - $totalDowntime;
                    $failureCount = $item->failure_count ?? 0;
                    $mtbf = $failureCount > 0 ? $operatingTime / $failureCount : 0;
                    
                    return [
                        'idMachine' => $item->idMachine,
                        'typeMachine' => $item->typeMachine,
                        'plant' => $item->plant,
                        'line' => $item->line,
                        'failure_count' => $failureCount,
                        'total_duration' => $totalDowntime,
                        'operating_time' => $operatingTime,
                        'mtbf' => $mtbf
                    ];
                })
                ->sortByDesc('mtbf')
                ->values();
        }
        
        // Overall MTBF
        $overallOperatingTime = $totalAvailableMinutes - $overallDowntimeDuration;
        $overallMTBF = $overallDowntimeCount > 0 ? $overallOperatingTime / $overallDowntimeCount : 0;
        
        // Top 10 MTTR (Highest)
        if ($dataSource === 'downtime_erp2' || $dataSource === 'downtime_erp') {
            $top10MTTR = (clone $baseQuery)->select(
                    'idMachine',
                    DB::raw('MAX(typeMachine) as typeMachine'),
                    DB::raw('MAX(plant) as plant'),
                    DB::raw('MAX(line) as line'),
                    DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                    DB::raw('COUNT(*) as downtime_count'),
                    DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) / COUNT(*) as mttr')
                )
                ->havingRaw('COUNT(*) > 0')
                ->groupBy('idMachine')
                ->orderBy('mttr', 'desc')
                ->limit(10)
                ->get();
            
            $top10MTBF = (clone $baseQuery)->select(
                    'idMachine',
                    DB::raw('MAX(typeMachine) as typeMachine'),
                    DB::raw('MAX(plant) as plant'),
                    DB::raw('MAX(line) as line'),
                    DB::raw('COUNT(*) as failure_count'),
                    DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration')
                )
                ->groupBy('idMachine')
                ->get()
                ->map(function($item) use ($totalAvailableMinutes) {
                    $totalDowntime = (float) ($item->total_duration ?? 0);
                    $operatingTime = $totalAvailableMinutes - $totalDowntime;
                    $failureCount = $item->failure_count ?? 0;
                    $mtbf = $failureCount > 0 ? $operatingTime / $failureCount : 0;
                    
                    return [
                        'idMachine' => $item->idMachine,
                        'typeMachine' => $item->typeMachine,
                        'plant' => $item->plant,
                        'line' => $item->line,
                        'failure_count' => $failureCount,
                        'total_duration' => $totalDowntime,
                        'operating_time' => $operatingTime,
                        'mtbf' => $mtbf
                    ];
                })
                ->sortByDesc('mtbf')
                ->take(10)
                ->values();
            
            // Get unique values for filters
            $filterModel = ($dataSource === 'downtime_erp2') ? DowntimeErp2::class : DowntimeErp::class;
            $plants = $filterModel::whereYear('date', $selectedYear)->whereMonth('date', $selectedMonth)->distinct()->whereNotNull('plant')->where('plant', '!=', '')->orderBy('plant')->pluck('plant')->unique();
            $processes = $filterModel::whereYear('date', $selectedYear)->whereMonth('date', $selectedMonth)->distinct()->whereNotNull('process')->where('process', '!=', '')->orderBy('process')->pluck('process')->unique();
            $lines = $filterModel::whereYear('date', $selectedYear)->whereMonth('date', $selectedMonth)->distinct()->whereNotNull('line')->where('line', '!=', '')->orderBy('line')->pluck('line')->unique();
            $rooms = $filterModel::whereYear('date', $selectedYear)->whereMonth('date', $selectedMonth)->distinct()->whereNotNull('roomName')->where('roomName', '!=', '')->orderBy('roomName')->pluck('roomName')->unique();
            $typeMachines = $filterModel::whereYear('date', $selectedYear)->whereMonth('date', $selectedMonth)->distinct()->whereNotNull('typeMachine')->where('typeMachine', '!=', '')->orderBy('typeMachine')->pluck('typeMachine')->unique();
            $machines = $filterModel::whereYear('date', $selectedYear)->whereMonth('date', $selectedMonth)->distinct()->whereNotNull('idMachine')->where('idMachine', '!=', '')->orderBy('idMachine')->pluck('idMachine')->unique();
        } else {
            $top10MTTR = (clone $baseQuery)->select(
                    DB::raw('machines.idMachine as idMachine'),
                    DB::raw('MAX(machine_types.name) as typeMachine'),
                    DB::raw('MAX(plants.name) as plant'),
                    DB::raw('MAX(lines.name) as line'),
                    DB::raw('SUM(downtimes.duration) as total_duration'),
                    DB::raw('COUNT(*) as downtime_count'),
                    DB::raw('SUM(downtimes.duration) / COUNT(*) as mttr')
                )
                ->leftJoin('machine_types', 'machines.type_id', '=', 'machine_types.id')
                ->leftJoin('plants', 'machines.plant_id', '=', 'plants.id')
                ->leftJoin('lines', 'machines.line_id', '=', 'lines.id')
                ->havingRaw('COUNT(*) > 0')
                ->groupBy('machines.idMachine')
                ->orderBy('mttr', 'desc')
                ->limit(10)
                ->get();
            
            $top10MTBF = (clone $baseQuery)->select(
                    DB::raw('machines.idMachine as idMachine'),
                    DB::raw('MAX(machine_types.name) as typeMachine'),
                    DB::raw('MAX(plants.name) as plant'),
                    DB::raw('MAX(lines.name) as line'),
                    DB::raw('COUNT(*) as failure_count'),
                    DB::raw('SUM(downtimes.duration) as total_duration')
                )
                ->leftJoin('machine_types', 'machines.type_id', '=', 'machine_types.id')
                ->leftJoin('plants', 'machines.plant_id', '=', 'plants.id')
                ->leftJoin('lines', 'machines.line_id', '=', 'lines.id')
                ->groupBy('machines.idMachine')
                ->get()
                ->map(function($item) use ($totalAvailableMinutes) {
                    $totalDowntime = (float) ($item->total_duration ?? 0);
                    $operatingTime = $totalAvailableMinutes - $totalDowntime;
                    $failureCount = $item->failure_count ?? 0;
                    $mtbf = $failureCount > 0 ? $operatingTime / $failureCount : 0;
                    
                    return [
                        'idMachine' => $item->idMachine,
                        'typeMachine' => $item->typeMachine,
                        'plant' => $item->plant,
                        'line' => $item->line,
                        'failure_count' => $failureCount,
                        'total_duration' => $totalDowntime,
                        'operating_time' => $operatingTime,
                        'mtbf' => $mtbf
                    ];
                })
                ->sortByDesc('mtbf')
                ->take(10)
                ->values();
            
            // Get unique values for filters
            $plants = \App\Models\Plant::whereHas('machines.downtimes', function($q) use ($selectedYear, $selectedMonth) {
                $q->whereYear('date', $selectedYear)->whereMonth('date', $selectedMonth);
            })->orderBy('name')->pluck('name')->unique();
            $processes = \App\Models\Process::whereHas('machines.downtimes', function($q) use ($selectedYear, $selectedMonth) {
                $q->whereYear('date', $selectedYear)->whereMonth('date', $selectedMonth);
            })->orderBy('name')->pluck('name')->unique();
            $lines = \App\Models\Line::whereHas('machines.downtimes', function($q) use ($selectedYear, $selectedMonth) {
                $q->whereYear('date', $selectedYear)->whereMonth('date', $selectedMonth);
            })->orderBy('name')->pluck('name')->unique();
            $rooms = \App\Models\Room::whereHas('machines.downtimes', function($q) use ($selectedYear, $selectedMonth) {
                $q->whereYear('date', $selectedYear)->whereMonth('date', $selectedMonth);
            })->orderBy('name')->pluck('name')->unique();
            $typeMachines = \App\Models\MachineType::whereHas('machines.downtimes', function($q) use ($selectedYear, $selectedMonth) {
                $q->whereYear('date', $selectedYear)->whereMonth('date', $selectedMonth);
            })->orderBy('name')->pluck('name')->unique();
            $machines = \App\Models\Machine::whereHas('downtimes', function($q) use ($selectedYear, $selectedMonth) {
                $q->whereYear('date', $selectedYear)->whereMonth('date', $selectedMonth);
            })->whereNotNull('idMachine')->orderBy('idMachine')->pluck('idMachine')->unique();
        }
        
        // Pagination
        $perPage = 15;
        $mttrCurrentPage = $request->input('mttr_page', 1);
        $mtbfCurrentPage = $request->input('mtbf_page', 1);
        
        $mttrPaginated = $mttrData->slice(($mttrCurrentPage - 1) * $perPage, $perPage)->values();
        $mtbfPaginated = $mtbfData->slice(($mtbfCurrentPage - 1) * $perPage, $perPage)->values();
        
        $mttrTotalPages = ceil($mttrData->count() / $perPage);
        $mtbfTotalPages = ceil($mtbfData->count() / $perPage);
        
        return view('mttr_mtbf.index', compact(
            'dataSource',
            'mttrData',
            'mtbfData',
            'mttrPaginated',
            'mtbfPaginated',
            'mttrCurrentPage',
            'mtbfCurrentPage',
            'mttrTotalPages',
            'mtbfTotalPages',
            'perPage',
            'overallMTTR',
            'overallMTBF',
            'overallDowntimeCount',
            'overallDowntimeDuration',
            'overallOperatingTime',
            'totalAvailableMinutes',
            'selectedMonth',
            'selectedYear',
            'daysInMonth',
            'plants',
            'processes',
            'lines',
            'rooms',
            'typeMachines',
            'machines',
            'top10MTTR',
            'top10MTBF'
        ));
    }
}

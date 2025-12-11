<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Downtime;
use App\Models\DowntimeErp;
use App\Models\DowntimeErp2;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SummaryDowntimeController extends Controller
{
    public function index(Request $request)
    {
        // Get data source from request or session, default to 'downtime_erp2'
        $dataSource = $request->input('data_source', session('summary_downtime_data_source', 'downtime_erp2'));
        session(['summary_downtime_data_source' => $dataSource]);
        
        // Default to current month and year
        $selectedMonth = $request->input('month', now()->month);
        $selectedYear = $request->input('year', now()->year);
        
        if ($dataSource === 'downtime_erp2') {
            $stats = $this->getDowntimeErp2Stats($request, $selectedYear, $selectedMonth);
        } elseif ($dataSource === 'downtime_erp') {
            $stats = $this->getDowntimeErpStats($request, $selectedYear, $selectedMonth);
        } else {
            $stats = $this->getDowntimeStats($request, $selectedYear, $selectedMonth);
        }
        
        return view('summary_downtime.index', array_merge($stats, [
            'dataSource' => $dataSource,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
        ]));
    }
    
    private function getDowntimeErp2Stats($request, $selectedYear, $selectedMonth)
    {
        // Build base query for current month
        $baseQuery = DowntimeErp2::query()
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
        if ($request->filled('machine')) {
            $baseQuery->where('idMachine', $request->machine);
        }
        
        // ========== LINE CHART: Downtime per Tanggal ==========
        $downtimePerDate = (clone $baseQuery)
            ->select(
                'date',
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        // ========== BASELINE: Rata-rata downtime bulan sebelumnya ==========
        $previousMonth = $selectedMonth - 1;
        $previousYear = $selectedYear;
        if ($previousMonth == 0) {
            $previousMonth = 12;
            $previousYear = $selectedYear - 1;
        }
        
        $previousMonthQuery = DowntimeErp2::query()
            ->whereYear('date', $previousYear)
            ->whereMonth('date', $previousMonth);
        
        // Apply same filters to previous month
        if ($request->filled('plant')) {
            $previousMonthQuery->where('plant', $request->plant);
        }
        if ($request->filled('process')) {
            $previousMonthQuery->where('process', $request->process);
        }
        if ($request->filled('line')) {
            $previousMonthQuery->where('line', $request->line);
        }
        if ($request->filled('room')) {
            $previousMonthQuery->where('roomName', $request->room);
        }
        if ($request->filled('typeMachine')) {
            $previousMonthQuery->where('typeMachine', $request->typeMachine);
        }
        if ($request->filled('machine')) {
            $previousMonthQuery->where('idMachine', $request->machine);
        }
        
        $previousMonthTotal = $previousMonthQuery->get()->sum(function($item) {
            return (float) ($item->duration ?? 0);
        });
        
        $daysInPreviousMonth = Carbon::create($previousYear, $previousMonth, 1)->daysInMonth;
        $baselineAverage = $daysInPreviousMonth > 0 ? $previousMonthTotal / $daysInPreviousMonth : 0;
        
        // ========== PIE CHART: Top 5 ID Mesin Downtime Tertinggi ==========
        $top5Machines = (clone $baseQuery)
            ->select(
                'idMachine',
                DB::raw('MAX(typeMachine) as typeMachine'),
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->whereNotNull('idMachine')
            ->where('idMachine', '!=', '')
            ->groupBy('idMachine')
            ->orderBy('total_duration', 'desc')
            ->limit(5)
            ->get();
        
        // ========== PIE CHART: Top 5 Problem Downtime Tertinggi ==========
        $top5Problems = (clone $baseQuery)
            ->select(
                'problemDowntime',
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->whereNotNull('problemDowntime')
            ->where('problemDowntime', '!=', '')
            ->groupBy('problemDowntime')
            ->orderBy('total_duration', 'desc')
            ->limit(5)
            ->get();
        
        // ========== BAR CHART: Downtime by Plant ==========
        $downtimeByPlantQuery = DowntimeErp2::query()
            ->whereYear('date', $selectedYear)
            ->whereMonth('date', $selectedMonth);
        
        // Apply other filters but NOT plant filter
        if ($request->filled('process')) {
            $downtimeByPlantQuery->where('process', $request->process);
        }
        if ($request->filled('line')) {
            $downtimeByPlantQuery->where('line', $request->line);
        }
        if ($request->filled('room')) {
            $downtimeByPlantQuery->where('roomName', $request->room);
        }
        if ($request->filled('typeMachine')) {
            $downtimeByPlantQuery->where('typeMachine', $request->typeMachine);
        }
        if ($request->filled('machine')) {
            $downtimeByPlantQuery->where('idMachine', $request->machine);
        }
        
        $downtimeByPlant = $downtimeByPlantQuery
            ->select(
                'plant',
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->whereNotNull('plant')
            ->where('plant', '!=', '')
            ->groupBy('plant')
            ->orderBy('total_duration', 'desc')
            ->get();
        
        // ========== INFORMASI DOWNTIME TERTINGGI ==========
        $longestDowntime = (clone $baseQuery)
            ->orderByRaw('CAST(duration AS DECIMAL(10,2)) DESC')
            ->first();
        
        $highestMachineDowntime = (clone $baseQuery)
            ->select(
                'idMachine',
                DB::raw('MAX(typeMachine) as typeMachine'),
                DB::raw('MAX(plant) as plant'),
                DB::raw('MAX(line) as line'),
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->whereNotNull('idMachine')
            ->where('idMachine', '!=', '')
            ->groupBy('idMachine')
            ->orderBy('total_duration', 'desc')
            ->first();
        
        // Get unique values for filters
        $plants = DowntimeErp2::whereYear('date', $selectedYear)
            ->whereMonth('date', $selectedMonth)
            ->distinct()
            ->whereNotNull('plant')
            ->where('plant', '!=', '')
            ->orderBy('plant')
            ->pluck('plant')
            ->unique();
        
        $processes = DowntimeErp2::whereYear('date', $selectedYear)
            ->whereMonth('date', $selectedMonth)
            ->distinct()
            ->whereNotNull('process')
            ->where('process', '!=', '')
            ->orderBy('process')
            ->pluck('process')
            ->unique();
        
        $lines = DowntimeErp2::whereYear('date', $selectedYear)
            ->whereMonth('date', $selectedMonth)
            ->distinct()
            ->whereNotNull('line')
            ->where('line', '!=', '')
            ->orderBy('line')
            ->pluck('line')
            ->unique();
        
        $rooms = DowntimeErp2::whereYear('date', $selectedYear)
            ->whereMonth('date', $selectedMonth)
            ->distinct()
            ->whereNotNull('roomName')
            ->where('roomName', '!=', '')
            ->orderBy('roomName')
            ->pluck('roomName')
            ->unique();
        
        $typeMachines = DowntimeErp2::whereYear('date', $selectedYear)
            ->whereMonth('date', $selectedMonth)
            ->distinct()
            ->whereNotNull('typeMachine')
            ->where('typeMachine', '!=', '')
            ->orderBy('typeMachine')
            ->pluck('typeMachine')
            ->unique();
        
        $machines = DowntimeErp2::whereYear('date', $selectedYear)
            ->whereMonth('date', $selectedMonth)
            ->distinct()
            ->whereNotNull('idMachine')
            ->where('idMachine', '!=', '')
            ->orderBy('idMachine')
            ->pluck('idMachine')
            ->unique();
        
        return [
            'downtimePerDate' => $downtimePerDate,
            'baselineAverage' => $baselineAverage,
            'top5Machines' => $top5Machines,
            'top5Problems' => $top5Problems,
            'downtimeByPlant' => $downtimeByPlant,
            'longestDowntime' => $longestDowntime,
            'highestMachineDowntime' => $highestMachineDowntime,
            'plants' => $plants,
            'processes' => $processes,
            'lines' => $lines,
            'rooms' => $rooms,
            'typeMachines' => $typeMachines,
            'machines' => $machines,
        ];
    }
    
    private function getDowntimeErpStats($request, $selectedYear, $selectedMonth)
    {
        // Build base query for current month
        $baseQuery = DowntimeErp::query()
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
        if ($request->filled('machine')) {
            $baseQuery->where('idMachine', $request->machine);
        }
        
        // ========== LINE CHART: Downtime per Tanggal ==========
        $downtimePerDate = (clone $baseQuery)
            ->select(
                'date',
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        // ========== BASELINE: Rata-rata downtime bulan sebelumnya ==========
        $previousMonth = $selectedMonth - 1;
        $previousYear = $selectedYear;
        if ($previousMonth == 0) {
            $previousMonth = 12;
            $previousYear = $selectedYear - 1;
        }
        
        $previousMonthQuery = DowntimeErp::query()
            ->whereYear('date', $previousYear)
            ->whereMonth('date', $previousMonth);
        
        // Apply same filters to previous month
        if ($request->filled('plant')) {
            $previousMonthQuery->where('plant', $request->plant);
        }
        if ($request->filled('process')) {
            $previousMonthQuery->where('process', $request->process);
        }
        if ($request->filled('line')) {
            $previousMonthQuery->where('line', $request->line);
        }
        if ($request->filled('room')) {
            $previousMonthQuery->where('roomName', $request->room);
        }
        if ($request->filled('typeMachine')) {
            $previousMonthQuery->where('typeMachine', $request->typeMachine);
        }
        if ($request->filled('machine')) {
            $previousMonthQuery->where('idMachine', $request->machine);
        }
        
        $previousMonthTotal = $previousMonthQuery->get()->sum(function($item) {
            return (float) ($item->duration ?? 0);
        });
        
        $daysInPreviousMonth = Carbon::create($previousYear, $previousMonth, 1)->daysInMonth;
        $baselineAverage = $daysInPreviousMonth > 0 ? $previousMonthTotal / $daysInPreviousMonth : 0;
        
        // ========== PIE CHART: Top 5 ID Mesin Downtime Tertinggi ==========
        $top5Machines = (clone $baseQuery)
            ->select(
                'idMachine',
                DB::raw('MAX(typeMachine) as typeMachine'),
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->whereNotNull('idMachine')
            ->where('idMachine', '!=', '')
            ->groupBy('idMachine')
            ->orderBy('total_duration', 'desc')
            ->limit(5)
            ->get();
        
        // ========== PIE CHART: Top 5 Problem Downtime Tertinggi ==========
        $top5Problems = (clone $baseQuery)
            ->select(
                'problemDowntime',
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->whereNotNull('problemDowntime')
            ->where('problemDowntime', '!=', '')
            ->groupBy('problemDowntime')
            ->orderBy('total_duration', 'desc')
            ->limit(5)
            ->get();
        
        // ========== BAR CHART: Downtime by Plant ==========
        $downtimeByPlantQuery = DowntimeErp::query()
            ->whereYear('date', $selectedYear)
            ->whereMonth('date', $selectedMonth);
        
        // Apply other filters but NOT plant filter
        if ($request->filled('process')) {
            $downtimeByPlantQuery->where('process', $request->process);
        }
        if ($request->filled('line')) {
            $downtimeByPlantQuery->where('line', $request->line);
        }
        if ($request->filled('room')) {
            $downtimeByPlantQuery->where('roomName', $request->room);
        }
        if ($request->filled('typeMachine')) {
            $downtimeByPlantQuery->where('typeMachine', $request->typeMachine);
        }
        if ($request->filled('machine')) {
            $downtimeByPlantQuery->where('idMachine', $request->machine);
        }
        
        $downtimeByPlant = $downtimeByPlantQuery
            ->select(
                'plant',
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->whereNotNull('plant')
            ->where('plant', '!=', '')
            ->groupBy('plant')
            ->orderBy('total_duration', 'desc')
            ->get();
        
        // ========== INFORMASI DOWNTIME TERTINGGI ==========
        $longestDowntime = (clone $baseQuery)
            ->orderByRaw('CAST(duration AS DECIMAL(10,2)) DESC')
            ->first();
        
        $highestMachineDowntime = (clone $baseQuery)
            ->select(
                'idMachine',
                DB::raw('MAX(typeMachine) as typeMachine'),
                DB::raw('MAX(plant) as plant'),
                DB::raw('MAX(line) as line'),
                DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->whereNotNull('idMachine')
            ->where('idMachine', '!=', '')
            ->groupBy('idMachine')
            ->orderBy('total_duration', 'desc')
            ->first();
        
        // Get unique values for filters
        $plants = DowntimeErp::whereYear('date', $selectedYear)
            ->whereMonth('date', $selectedMonth)
            ->distinct()
            ->whereNotNull('plant')
            ->where('plant', '!=', '')
            ->orderBy('plant')
            ->pluck('plant')
            ->unique();
        
        $processes = DowntimeErp::whereYear('date', $selectedYear)
            ->whereMonth('date', $selectedMonth)
            ->distinct()
            ->whereNotNull('process')
            ->where('process', '!=', '')
            ->orderBy('process')
            ->pluck('process')
            ->unique();
        
        $lines = DowntimeErp::whereYear('date', $selectedYear)
            ->whereMonth('date', $selectedMonth)
            ->distinct()
            ->whereNotNull('line')
            ->where('line', '!=', '')
            ->orderBy('line')
            ->pluck('line')
            ->unique();
        
        $rooms = DowntimeErp::whereYear('date', $selectedYear)
            ->whereMonth('date', $selectedMonth)
            ->distinct()
            ->whereNotNull('roomName')
            ->where('roomName', '!=', '')
            ->orderBy('roomName')
            ->pluck('roomName')
            ->unique();
        
        $typeMachines = DowntimeErp::whereYear('date', $selectedYear)
            ->whereMonth('date', $selectedMonth)
            ->distinct()
            ->whereNotNull('typeMachine')
            ->where('typeMachine', '!=', '')
            ->orderBy('typeMachine')
            ->pluck('typeMachine')
            ->unique();
        
        $machines = DowntimeErp::whereYear('date', $selectedYear)
            ->whereMonth('date', $selectedMonth)
            ->distinct()
            ->whereNotNull('idMachine')
            ->where('idMachine', '!=', '')
            ->orderBy('idMachine')
            ->pluck('idMachine')
            ->unique();
        
        return [
            'downtimePerDate' => $downtimePerDate,
            'baselineAverage' => $baselineAverage,
            'top5Machines' => $top5Machines,
            'top5Problems' => $top5Problems,
            'downtimeByPlant' => $downtimeByPlant,
            'longestDowntime' => $longestDowntime,
            'highestMachineDowntime' => $highestMachineDowntime,
            'plants' => $plants,
            'processes' => $processes,
            'lines' => $lines,
            'rooms' => $rooms,
            'typeMachines' => $typeMachines,
            'machines' => $machines,
        ];
    }
    
    private function getDowntimeStats($request, $selectedYear, $selectedMonth)
    {
        // Build base query for current month
        $baseQuery = Downtime::query()
            ->whereYear('date', $selectedYear)
            ->whereMonth('date', $selectedMonth);
        
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
        if ($request->filled('machine')) {
            $baseQuery->whereHas('machine', function($q) use ($request) {
                $q->where('idMachine', $request->machine);
            });
        }
        
        // ========== LINE CHART: Downtime per Tanggal ==========
        $downtimePerDate = (clone $baseQuery)
            ->select(
                'date',
                DB::raw('SUM(duration) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        // ========== BASELINE: Rata-rata downtime bulan sebelumnya ==========
        $previousMonth = $selectedMonth - 1;
        $previousYear = $selectedYear;
        if ($previousMonth == 0) {
            $previousMonth = 12;
            $previousYear = $selectedYear - 1;
        }
        
        $previousMonthQuery = Downtime::query()
            ->whereYear('date', $previousYear)
            ->whereMonth('date', $previousMonth);
        
        // Apply same filters to previous month
        if ($request->filled('plant')) {
            $previousMonthQuery->whereHas('machine', function($q) use ($request) {
                $q->whereHas('plant', function($q2) use ($request) {
                    $q2->where('name', $request->plant);
                });
            });
        }
        if ($request->filled('process')) {
            $previousMonthQuery->whereHas('machine', function($q) use ($request) {
                $q->whereHas('process', function($q2) use ($request) {
                    $q2->where('name', $request->process);
                });
            });
        }
        if ($request->filled('line')) {
            $previousMonthQuery->whereHas('machine', function($q) use ($request) {
                $q->whereHas('line', function($q2) use ($request) {
                    $q2->where('name', $request->line);
                });
            });
        }
        if ($request->filled('room')) {
            $previousMonthQuery->whereHas('machine', function($q) use ($request) {
                $q->whereHas('room', function($q2) use ($request) {
                    $q2->where('name', $request->room);
                });
            });
        }
        if ($request->filled('typeMachine')) {
            $previousMonthQuery->whereHas('machine', function($q) use ($request) {
                $q->whereHas('machineType', function($q2) use ($request) {
                    $q2->where('name', $request->typeMachine);
                });
            });
        }
        if ($request->filled('machine')) {
            $previousMonthQuery->whereHas('machine', function($q) use ($request) {
                $q->where('idMachine', $request->machine);
            });
        }
        
        $previousMonthTotal = $previousMonthQuery->sum('duration');
        $daysInPreviousMonth = Carbon::create($previousYear, $previousMonth, 1)->daysInMonth;
        $baselineAverage = $daysInPreviousMonth > 0 ? $previousMonthTotal / $daysInPreviousMonth : 0;
        
        // ========== PIE CHART: Top 5 ID Mesin Downtime Tertinggi ==========
        $top5Machines = (clone $baseQuery)
            ->select(
                DB::raw('machines.idMachine as idMachine'),
                DB::raw('MAX(machine_types.name) as typeMachine'),
                DB::raw('SUM(downtimes.duration) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->join('machines', 'downtimes.machine_id', '=', 'machines.id')
            ->leftJoin('machine_types', 'machines.type_id', '=', 'machine_types.id')
            ->whereNotNull('machines.idMachine')
            ->groupBy('machines.idMachine')
            ->orderBy('total_duration', 'desc')
            ->limit(5)
            ->get();
        
        // ========== PIE CHART: Top 5 Problem Downtime Tertinggi ==========
        $top5Problems = (clone $baseQuery)
            ->select(
                'problems.name as problemDowntime',
                DB::raw('SUM(downtimes.duration) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->join('problems', 'downtimes.problem_id', '=', 'problems.id')
            ->whereNotNull('problems.name')
            ->groupBy('problems.name')
            ->orderBy('total_duration', 'desc')
            ->limit(5)
            ->get();
        
        // ========== BAR CHART: Downtime by Plant ==========
        $downtimeByPlantQuery = Downtime::query()
            ->whereYear('date', $selectedYear)
            ->whereMonth('date', $selectedMonth);
        
        // Apply other filters but NOT plant filter
        if ($request->filled('process')) {
            $downtimeByPlantQuery->whereHas('machine', function($q) use ($request) {
                $q->whereHas('process', function($q2) use ($request) {
                    $q2->where('name', $request->process);
                });
            });
        }
        if ($request->filled('line')) {
            $downtimeByPlantQuery->whereHas('machine', function($q) use ($request) {
                $q->whereHas('line', function($q2) use ($request) {
                    $q2->where('name', $request->line);
                });
            });
        }
        if ($request->filled('room')) {
            $downtimeByPlantQuery->whereHas('machine', function($q) use ($request) {
                $q->whereHas('room', function($q2) use ($request) {
                    $q2->where('name', $request->room);
                });
            });
        }
        if ($request->filled('typeMachine')) {
            $downtimeByPlantQuery->whereHas('machine', function($q) use ($request) {
                $q->whereHas('machineType', function($q2) use ($request) {
                    $q2->where('name', $request->typeMachine);
                });
            });
        }
        if ($request->filled('machine')) {
            $downtimeByPlantQuery->whereHas('machine', function($q) use ($request) {
                $q->where('idMachine', $request->machine);
            });
        }
        
        $downtimeByPlant = $downtimeByPlantQuery
            ->select(
                'plants.name as plant',
                DB::raw('SUM(downtimes.duration) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->join('machines', 'downtimes.machine_id', '=', 'machines.id')
            ->join('plants', 'machines.plant_id', '=', 'plants.id')
            ->whereNotNull('plants.name')
            ->groupBy('plants.name')
            ->orderBy('total_duration', 'desc')
            ->get();
        
        // ========== INFORMASI DOWNTIME TERTINGGI ==========
        $longestDowntime = (clone $baseQuery)
            ->orderBy('duration', 'desc')
            ->first();
        
        $highestMachineDowntime = (clone $baseQuery)
            ->select(
                DB::raw('machines.idMachine as idMachine'),
                DB::raw('MAX(machine_types.name) as typeMachine'),
                DB::raw('MAX(plants.name) as plant'),
                DB::raw('MAX(lines.name) as line'),
                DB::raw('SUM(downtimes.duration) as total_duration'),
                DB::raw('COUNT(*) as downtime_count')
            )
            ->join('machines', 'downtimes.machine_id', '=', 'machines.id')
            ->leftJoin('machine_types', 'machines.type_id', '=', 'machine_types.id')
            ->leftJoin('plants', 'machines.plant_id', '=', 'plants.id')
            ->leftJoin('lines', 'machines.line_id', '=', 'lines.id')
            ->whereNotNull('machines.idMachine')
            ->groupBy('machines.idMachine')
            ->orderBy('total_duration', 'desc')
            ->first();
        
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
        
        return [
            'downtimePerDate' => $downtimePerDate,
            'baselineAverage' => $baselineAverage,
            'top5Machines' => $top5Machines,
            'top5Problems' => $top5Problems,
            'downtimeByPlant' => $downtimeByPlant,
            'longestDowntime' => $longestDowntime,
            'highestMachineDowntime' => $highestMachineDowntime,
            'plants' => $plants,
            'processes' => $processes,
            'lines' => $lines,
            'rooms' => $rooms,
            'typeMachines' => $typeMachines,
            'machines' => $machines,
        ];
    }
}

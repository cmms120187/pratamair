<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Downtime;
use App\Models\DowntimeErp;
use App\Models\DowntimeErp2;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ParetoMachineController extends Controller
{
    /**
     * Display Pareto Machine Report
     */
    public function index(Request $request)
    {
        // Default to "all" for month and year
        $selectedMonth = $request->input('month', 'all');
        $selectedYear = $request->input('year', 'all');
        $dataSource = $request->input('data_source', 'downtime_erp2'); // 'downtime', 'downtime_erp', or 'downtime_erp2'
        
        // Build base query
        if ($dataSource === 'downtime_erp2') {
            $baseQuery = DowntimeErp2::query();
            
            // Apply year filter if not "all"
            if ($selectedYear !== 'all') {
                $baseQuery->whereYear('date', $selectedYear);
            }
            
            // Apply month filter if not "all"
            if ($selectedMonth !== 'all') {
                $baseQuery->whereMonth('date', $selectedMonth);
            }
            
            // Get machine downtime data
            $machineData = (clone $baseQuery)
                ->select(
                    'idMachine',
                    'typeMachine',
                    DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                    DB::raw('COUNT(*) as frequency')
                )
                ->groupBy('idMachine', 'typeMachine')
                ->orderByDesc('total_duration')
                ->get();
        } elseif ($dataSource === 'downtime_erp') {
            $baseQuery = DowntimeErp::query();
            
            // Apply year filter if not "all"
            if ($selectedYear !== 'all') {
                $baseQuery->whereYear('date', $selectedYear);
            }
            
            // Apply month filter if not "all"
            if ($selectedMonth !== 'all') {
                $baseQuery->whereMonth('date', $selectedMonth);
            }
            
            // Get machine downtime data
            $machineData = (clone $baseQuery)
                ->select(
                    'idMachine',
                    'typeMachine',
                    DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                    DB::raw('COUNT(*) as frequency')
                )
                ->groupBy('idMachine', 'typeMachine')
                ->orderByDesc('total_duration')
                ->get();
        } else {
            $baseQuery = Downtime::query()
                ->whereNotNull('machine_id');
            
            // Apply year filter if not "all"
            if ($selectedYear !== 'all') {
                $baseQuery->whereYear('date', $selectedYear);
            }
            
            // Apply month filter if not "all"
            if ($selectedMonth !== 'all') {
                $baseQuery->whereMonth('date', $selectedMonth);
            }
            
            // Get machine downtime data
            $machineData = (clone $baseQuery)
                ->select(
                    'machine_id',
                    DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                    DB::raw('COUNT(*) as frequency')
                )
                ->with('machine.machineType')
                ->groupBy('machine_id')
                ->orderByDesc('total_duration')
                ->get()
                ->map(function($item) {
                    return (object)[
                        'idMachine' => $item->machine->idMachine ?? '-',
                        'typeMachine' => $item->machine->machineType->name ?? '-',
                        'total_duration' => $item->total_duration,
                        'frequency' => $item->frequency,
                    ];
                });
        }
        
        // Calculate total downtime for percentage
        $totalDowntime = $machineData->sum('total_duration');
        
        // Build Pareto data
        $paretoData = [];
        $cumulativeDuration = 0;
        $cumulativePercentage = 0;
        
        foreach ($machineData as $index => $machine) {
            $cumulativeDuration += $machine->total_duration;
            $cumulativePercentage = $totalDowntime > 0 ? ($cumulativeDuration / $totalDowntime) * 100 : 0;
            
            $paretoData[] = [
                'machine_id' => $machine->idMachine,
                'machine_type' => $machine->typeMachine ?? '-',
                'duration' => round($machine->total_duration, 2),
                'frequency' => $machine->frequency,
                'percentage' => $totalDowntime > 0 ? round(($machine->total_duration / $totalDowntime) * 100, 2) : 0,
                'cumulative_duration' => round($cumulativeDuration, 2),
                'cumulative_percentage' => round($cumulativePercentage, 2),
                'rank' => $index + 1,
            ];
        }
        
        // Get top 20 machines (80/20 rule focus)
        $topMachines = collect($paretoData)->take(20);
        
        // Statistics
        $stats = [
            'total_machines' => count($paretoData),
            'total_downtime' => round($totalDowntime, 2),
            'total_frequency' => $machineData->sum('frequency'),
            'avg_downtime_per_machine' => count($paretoData) > 0 ? round($totalDowntime / count($paretoData), 2) : 0,
            'top_20_percentage' => count($paretoData) > 0 ? round(($topMachines->sum('duration') / $totalDowntime) * 100, 2) : 0,
        ];
        
        // Get unique values for filters
        $months = range(1, 12);
        $years = range(now()->year - 5, now()->year);
        
        return view('pareto-machine.index', compact(
            'paretoData',
            'topMachines',
            'stats',
            'selectedMonth',
            'selectedYear',
            'dataSource',
            'months',
            'years'
        ));
    }
}

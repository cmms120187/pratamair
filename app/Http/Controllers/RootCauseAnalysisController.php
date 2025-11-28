<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Downtime;
use App\Models\DowntimeErp;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RootCauseAnalysisController extends Controller
{
    /**
     * Display Root Cause Analysis
     */
    public function index(Request $request)
    {
        // Default to "all" for month and year
        $selectedMonth = $request->input('month', 'all');
        $selectedYear = $request->input('year', 'all');
        $dataSource = $request->input('data_source', 'downtime'); // 'downtime' or 'downtime_erp'
        
        // Build base query
        if ($dataSource === 'downtime_erp') {
            $baseQuery = DowntimeErp::query();
            
            // Apply year filter if not "all"
            if ($selectedYear !== 'all') {
                $baseQuery->whereYear('date', $selectedYear);
            }
            
            // Apply month filter if not "all"
            if ($selectedMonth !== 'all') {
                $baseQuery->whereMonth('date', $selectedMonth);
            }
            
            // Get problem/reason analysis
            $problemData = (clone $baseQuery)
                ->select(
                    'problemDowntime',
                    DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                    DB::raw('COUNT(*) as frequency')
                )
                ->whereNotNull('problemDowntime')
                ->where('problemDowntime', '!=', '')
                ->groupBy('problemDowntime')
                ->orderByDesc('frequency')
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
            
            // Get problem/reason analysis
            $problemData = (clone $baseQuery)
                ->select(
                    'problem_id',
                    'reason_id',
                    DB::raw('SUM(CAST(duration AS DECIMAL(10,2))) as total_duration'),
                    DB::raw('COUNT(*) as frequency')
                )
                ->with(['problem', 'reason'])
                ->groupBy('problem_id', 'reason_id')
                ->orderByDesc('frequency')
                ->orderByDesc('total_duration')
                ->get()
                ->map(function($item) {
                    return (object)[
                        'problemDowntime' => ($item->problem->name ?? 'Unknown') . ($item->reason ? ' - ' . $item->reason->name : ''),
                        'total_duration' => $item->total_duration,
                        'frequency' => $item->frequency,
                    ];
                });
        }
        
        // Calculate total for percentage
        $totalFrequency = $problemData->sum('frequency');
        $totalDuration = $problemData->sum('total_duration');
        
        // Build analysis data
        $analysisData = [];
        $cumulativeFrequency = 0;
        $cumulativePercentage = 0;
        
        foreach ($problemData as $index => $problem) {
            $cumulativeFrequency += $problem->frequency;
            $cumulativePercentage = $totalFrequency > 0 ? ($cumulativeFrequency / $totalFrequency) * 100 : 0;
            
            $analysisData[] = [
                'root_cause' => $problem->problemDowntime ?? 'Unknown',
                'frequency' => $problem->frequency,
                'duration' => round($problem->total_duration, 2),
                'frequency_percentage' => $totalFrequency > 0 ? round(($problem->frequency / $totalFrequency) * 100, 2) : 0,
                'duration_percentage' => $totalDuration > 0 ? round(($problem->total_duration / $totalDuration) * 100, 2) : 0,
                'cumulative_frequency' => $cumulativeFrequency,
                'cumulative_percentage' => round($cumulativePercentage, 2),
                'avg_duration' => $problem->frequency > 0 ? round($problem->total_duration / $problem->frequency, 2) : 0,
                'rank' => $index + 1,
            ];
        }
        
        // Statistics
        $stats = [
            'total_root_causes' => count($analysisData),
            'total_frequency' => $totalFrequency,
            'total_duration' => round($totalDuration, 2),
            'avg_frequency_per_cause' => count($analysisData) > 0 ? round($totalFrequency / count($analysisData), 2) : 0,
            'avg_duration_per_incident' => $totalFrequency > 0 ? round($totalDuration / $totalFrequency, 2) : 0,
        ];
        
        // Get unique values for filters
        $months = range(1, 12);
        $years = range(now()->year - 5, now()->year);
        
        return view('root-cause-analysis.index', compact(
            'analysisData',
            'stats',
            'selectedMonth',
            'selectedYear',
            'dataSource',
            'months',
            'years'
        ));
    }
}


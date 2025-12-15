<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductionDailyGrade;
use App\Models\ProductionHourly;
use App\Models\DowntimeErp2;
use App\Models\Line;
use App\Models\Process;
use App\Models\Plant;
use App\Models\RoomErp;
use App\Services\OeeCalculationService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OeeController extends Controller
{
    protected $oeeService;
    
    public function __construct(OeeCalculationService $oeeService)
    {
        $this->oeeService = $oeeService;
    }
    
    /**
     * Display OEE report
     */
    public function index(Request $request)
    {
        // Get filter parameters - default to current month
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $lineId = $request->input('line_id');
        $processId = $request->input('process_id');
        
        // Build query for production daily data - date range with eager loading
        $query = ProductionDailyGrade::with(['line.plant', 'process'])
            ->whereBetween('production_date', [$startDate, $endDate]);
        
        if ($lineId) {
            $query->where('line_id', $lineId);
        }
        
        if ($processId) {
            $query->where('process_id', $processId);
        }
        
        // Get production data
        $productionData = $query->orderBy('production_date', 'asc')
            ->orderBy('line_id', 'asc')
            ->get();
        
        // OPTIMIZATION: Pre-load all data needed (BATCH QUERIES instead of N+1)
        $productionDates = $productionData->pluck('production_date')
            ->map(function($date) {
                return $date->format('Y-m-d');
            })
            ->unique()
            ->values()
            ->toArray();
        
        $lineIds = $productionData->pluck('line_id')->unique()->filter()->values()->toArray();
        $processIds = $productionData->pluck('process_id')->unique()->filter()->values()->toArray();
        $productionIds = $productionData->pluck('id')->toArray();
        
        // Batch query ProductionHourly - group by line_id, process_id, and date
        $hourlyDataRaw = ProductionHourly::whereIn('line_id', $lineIds)
            ->whereIn('process_id', $processIds)
            ->whereIn(DB::raw('DATE(production_date)'), $productionDates)
            ->whereNotNull('total_production')
            ->where('total_production', '!=', '')
            ->get()
            ->groupBy(function($item) {
                return $item->line_id . '-' . $item->process_id . '-' . $item->production_date->format('Y-m-d');
            });
        
        // Batch query DowntimeErp2
        $downtimeDataRaw = DowntimeErp2::whereIn('date', $productionDates)
            ->where('include_oee', true)
            ->get()
            ->groupBy(function($item) {
                return $item->date . '-' . ($item->plant ?? '') . '-' . ($item->process ?? '') . '-' . ($item->line ?? '');
            });
        
        // Batch query ProductionDailyDowntime
        $productionDowntimeDataRaw = \App\Models\ProductionDailyDowntime::whereIn('production_daily_grade_id', $productionIds)
            ->where('include_oee', true)
            ->get()
            ->groupBy('production_daily_grade_id');
        
        // Calculate OEE for each production record using pre-loaded data
        $oeeData = [];
        foreach ($productionData as $production) {
            // Ensure production_date is Carbon instance
            $productionDate = $production->production_date instanceof \Carbon\Carbon 
                ? $production->production_date 
                : \Carbon\Carbon::parse($production->production_date);
            $dateKey = $productionDate->format('Y-m-d');
            $lineProcessKey = $production->line_id . '-' . $production->process_id . '-' . $dateKey;
            
            // Get hourly data for this production
            $hourlyRecords = $hourlyDataRaw->get($lineProcessKey) ?? collect();
            
            // Get downtime records for this production
            $line = $production->line;
            $process = $production->process;
            $plant = $line ? $line->plant : null;
            
            $plantName = $plant ? $plant->name : null;
            $processName = $process ? $process->name : null;
            $lineName = $line ? $line->name : null;
            
            $downtimeKey = $dateKey . '-' . ($plantName ?? '') . '-' . ($processName ?? '') . '-' . ($lineName ?? '');
            $downtimeRecords = $downtimeDataRaw->get($downtimeKey) ?? collect();
            
            // Get production downtimes for this production
            $productionDowntimes = $productionDowntimeDataRaw->get($production->id) ?? collect();
            
            // Use service to calculate OEE
            $oeeResult = $this->oeeService->calculateOeeForProduction(
                $production,
                $hourlyRecords,
                $downtimeRecords,
                $productionDowntimes
            );
            
            $oeeData[] = $oeeResult;
        }
        
        // Get lines and processes for filters
        $lines = Line::with('process')->orderBy('name', 'asc')->get();
        $processes = Process::orderBy('name', 'asc')->get();
        
        // Calculate summary statistics for chart - grouped by date and line
        $summaryData = [
            'labels' => [], // Unique dates
            'datasets' => [], // One dataset per line
        ];
        
        // Get unique dates
        $uniqueDates = [];
        foreach ($oeeData as $data) {
            $dateKey = $data['production_date']->format('Y-m-d');
            if (!in_array($dateKey, $uniqueDates)) {
                $uniqueDates[] = $dateKey;
            }
        }
        sort($uniqueDates);
        
        // Format dates for labels
        foreach ($uniqueDates as $date) {
            $summaryData['labels'][] = Carbon::parse($date)->format('d M');
        }
        
        // Get unique lines
        $uniqueLines = [];
        foreach ($oeeData as $data) {
            $lineKey = $data['line'] ? $data['line']->id : 'unknown';
            $lineName = $data['line'] ? $data['line']->name : 'Unknown';
            if (!isset($uniqueLines[$lineKey])) {
                $uniqueLines[$lineKey] = $lineName;
            }
        }
        
        // Create dataset for each line
        $lineColors = [
            'rgba(59, 130, 246, 0.7)',   // Blue
            'rgba(34, 197, 94, 0.7)',    // Green
            'rgba(234, 179, 8, 0.7)',    // Yellow
            'rgba(239, 68, 68, 0.7)',    // Red
            'rgba(168, 85, 247, 0.7)',   // Purple
            'rgba(236, 72, 153, 0.7)',   // Pink
            'rgba(14, 165, 233, 0.7)',   // Sky
            'rgba(251, 146, 60, 0.7)',   // Orange
            'rgba(20, 184, 166, 0.7)',   // Teal
            'rgba(139, 92, 246, 0.7)',   // Violet
        ];
        
        $colorIndex = 0;
        foreach ($uniqueLines as $lineId => $lineName) {
            $bgColor = $lineColors[$colorIndex % count($lineColors)];
            $borderColor = str_replace('0.7', '1', $bgColor);
            
            $lineData = [
                'label' => $lineName,
                'data' => [],
                'availability' => [],
                'performance' => [],
                'quality' => [],
                'backgroundColor' => $bgColor,
                'borderColor' => $borderColor,
                'borderWidth' => 2
            ];
            
            // For each date, find OEE for this line
            foreach ($uniqueDates as $date) {
                $found = false;
                foreach ($oeeData as $data) {
                    $dataDate = $data['production_date']->format('Y-m-d');
                    $dataLineId = $data['line'] ? $data['line']->id : 'unknown';
                    
                    if ($dataDate === $date && $dataLineId == $lineId) {
                        $lineData['data'][] = round($data['oee'], 2);
                        $lineData['availability'][] = round($data['availability'], 2);
                        $lineData['performance'][] = round($data['performance'], 2);
                        $lineData['quality'][] = round($data['quality'], 2);
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $lineData['data'][] = null; // No data for this date
                    $lineData['availability'][] = null;
                    $lineData['performance'][] = null;
                    $lineData['quality'][] = null;
                }
            }
            
            $summaryData['datasets'][] = $lineData;
            $colorIndex++;
        }
        
        // Get unique plants from actual oeeData (not from RoomErp)
        // This ensures we only show plants that have data in the selected date range
        $uniquePlants = [];
        foreach ($oeeData as $data) {
            if ($data['plant'] && $data['plant']->name) {
                $plantName = $data['plant']->name;
                if (!isset($uniquePlants[$plantName])) {
                    $uniquePlants[$plantName] = $plantName;
                }
            }
        }
        
        // Sort plants alphabetically
        ksort($uniquePlants);
        
        // Calculate OEE per Plant with different colors
        $plantOeeData = [
            'labels' => [],
            'availability' => [],
            'performance' => [],
            'quality' => [],
            'oee' => [],
            'colors' => [], // Different color for each plant
        ];
        
        // Color palette for plants
        $plantColors = [
            'rgba(59, 130, 246, 0.7)',   // Blue
            'rgba(34, 197, 94, 0.7)',    // Green
            'rgba(234, 179, 8, 0.7)',    // Yellow
            'rgba(239, 68, 68, 0.7)',    // Red
            'rgba(168, 85, 247, 0.7)',   // Purple
            'rgba(236, 72, 153, 0.7)',   // Pink
            'rgba(14, 165, 233, 0.7)',   // Sky
            'rgba(251, 146, 60, 0.7)',   // Orange
        ];
        
        $colorIndex = 0;
        foreach ($uniquePlants as $plantName) {
            // Filter oeeData by plant name - only from actual data in date range
            $plantOeeRecords = array_filter($oeeData, function($data) use ($plantName) {
                $dataPlantName = $data['plant'] ? $data['plant']->name : null;
                return $dataPlantName === $plantName;
            });
            
            if (count($plantOeeRecords) > 0) {
                $plantOeeData['labels'][] = $plantName;
                
                // Calculate averages for this plant across the entire date range
                $availabilities = array_column($plantOeeRecords, 'availability');
                $performances = array_column($plantOeeRecords, 'performance');
                $qualities = array_column($plantOeeRecords, 'quality');
                $oees = array_column($plantOeeRecords, 'oee');
                
                // Calculate average OEE for this plant in the selected date range
                $plantOeeData['availability'][] = round(array_sum($availabilities) / count($availabilities), 2);
                $plantOeeData['performance'][] = round(array_sum($performances) / count($performances), 2);
                $plantOeeData['quality'][] = round(array_sum($qualities) / count($qualities), 2);
                $plantOeeData['oee'][] = round(array_sum($oees) / count($oees), 2);
                $plantOeeData['colors'][] = $plantColors[$colorIndex % count($plantColors)];
                
                $colorIndex++;
            }
        }
        
        return view('oee.index', compact('oeeData', 'lines', 'processes', 'startDate', 'endDate', 'lineId', 'processId', 'summaryData', 'plantOeeData'));
    }
}


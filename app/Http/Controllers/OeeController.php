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
use Carbon\Carbon;

class OeeController extends Controller
{
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
        
        // Build query for production daily data - date range
        $query = ProductionDailyGrade::with(['line', 'process'])
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
        
        // Calculate OEE for each production record
        $oeeData = [];
        foreach ($productionData as $production) {
            // Get Grade A from ProductionHourly
            $gradeA = ProductionHourly::where('line_id', $production->line_id)
                ->where('process_id', $production->process_id)
                ->whereDate('production_date', $production->production_date)
                ->where('hour', 0)
                ->whereNotNull('total_production')
                ->where('total_production', '!=', '')
                ->value('total_production');
            
            // If not found with hour = 0, try sum of all hours
            if (!$gradeA) {
                $gradeA = ProductionHourly::where('line_id', $production->line_id)
                    ->where('process_id', $production->process_id)
                    ->whereDate('production_date', $production->production_date)
                    ->whereNotNull('total_production')
                    ->where('total_production', '!=', '')
                    ->sum('total_production');
            }
            
            $gradeA = (int) $gradeA;
            $gradeB = $production->grade_b ?? 0;
            $gradeC = $production->grade_c ?? 0;
            $totalProduction = $gradeA + $gradeB + $gradeC;
            
            // Get target_per_hour
            $targetPerHour = ProductionHourly::where('line_id', $production->line_id)
                ->where('process_id', $production->process_id)
                ->whereDate('production_date', $production->production_date)
                ->where('hour', 0)
                ->value('target_per_hour');
            
            if ($targetPerHour === null) {
                $targetPerHour = $production->target_per_hour ?? 0;
            }
            
            // Calculate production hours (end_time - start_time - break_duration)
            $productionHours = 0;
            if ($production->start_time && $production->end_time) {
                $startParts = explode(':', $production->start_time);
                $endParts = explode(':', $production->end_time);
                
                $startMinutes = (int)$startParts[0] * 60 + (int)($startParts[1] ?? 0);
                $endMinutes = (int)$endParts[0] * 60 + (int)($endParts[1] ?? 0);
                
                // Handle case where end_time is next day
                if ($endMinutes < $startMinutes) {
                    $endMinutes += 24 * 60;
                }
                
                $totalMinutes = $endMinutes - $startMinutes;
                $totalHours = $totalMinutes / 60;
                $breakDuration = $production->break_duration ?? 0;
                $productionHours = max(0, $totalHours - $breakDuration);
            }
            
            // Get downtime from DowntimeErp2 where include_oee = true
            // Match by date, plant, process, and line
            $line = $production->line;
            $process = $production->process;
            
            // Get plant from line (line has plant_id)
            $plant = $line ? $line->plant : null;
            
            $plantName = $plant ? $plant->name : null;
            $processName = $process ? $process->name : null;
            $lineName = $line ? $line->name : null;
            
            // Get downtime records where include_oee = true
            // Match by specific production date, plant, process, and line
            $downtimeRecords = DowntimeErp2::where('date', $production->production_date)
                ->where('include_oee', true)
                ->where(function($q) use ($plantName, $processName, $lineName) {
                    if ($plantName) {
                        $q->where('plant', $plantName);
                    }
                    if ($processName) {
                        $q->where('process', $processName);
                    }
                    if ($lineName) {
                        $q->where('line', $lineName);
                    }
                })
                ->get();
            
            // Calculate total downtime in minutes from DowntimeErp2 (machine breakdown)
            $totalDowntimeMinutes = 0;
            foreach ($downtimeRecords as $downtime) {
                // Parse duration string (format: "X minutes")
                $durationStr = $downtime->duration ?? '';
                if (preg_match('/(\d+)\s*minutes?/i', $durationStr, $matches)) {
                    $totalDowntimeMinutes += (int)$matches[1];
                }
            }
            
            // Get production downtimes (process, quality, material, etc.) where include_oee = true
            $productionDowntimes = \App\Models\ProductionDailyDowntime::where('production_daily_grade_id', $production->id)
                ->where('include_oee', true)
                ->get();
            
            // Add production downtime minutes
            foreach ($productionDowntimes as $prodDowntime) {
                $totalDowntimeMinutes += $prodDowntime->duration_minutes;
            }
            
            $totalDowntimeHours = $totalDowntimeMinutes / 60;
            
            // Calculate OEE components
            // Planned Production Time = Production Hours
            $plannedProductionTime = $productionHours;
            
            // Operating Time = Planned Production Time - Downtime
            $operatingTime = max(0, $plannedProductionTime - $totalDowntimeHours);
            
            // Availability = (Operating Time / Planned Production Time) × 100
            $availability = $plannedProductionTime > 0 
                ? ($operatingTime / $plannedProductionTime) * 100 
                : 0;
            
            // Target Output = Target per Hour × Production Hours
            $targetOutput = $targetPerHour * $productionHours;
            
            // Performance = (Actual Output / Target Output) × 100
            $performance = $targetOutput > 0 
                ? ($totalProduction / $targetOutput) * 100 
                : 0;
            
            // Quality = (Good Units / Total Units) × 100
            $quality = $totalProduction > 0 
                ? ($gradeA / $totalProduction) * 100 
                : 0;
            
            // OEE = Availability × Performance × Quality / 10000
            $oee = ($availability * $performance * $quality) / 10000;
            
            $oeeData[] = [
                'production' => $production,
                'line' => $line,
                'process' => $process,
                'plant' => $plant,
                'production_date' => $production->production_date,
                'production_hours' => $productionHours,
                'target_per_hour' => $targetPerHour,
                'target_output' => $targetOutput,
                'grade_a' => $gradeA,
                'grade_b' => $gradeB,
                'grade_c' => $gradeC,
                'total_production' => $totalProduction,
                'total_downtime_hours' => $totalDowntimeHours,
                'total_downtime_minutes' => $totalDowntimeMinutes,
                'downtime_count' => $downtimeRecords->count(),
                'planned_production_time' => $plannedProductionTime,
                'operating_time' => $operatingTime,
                'availability' => $availability,
                'performance' => $performance,
                'quality' => $quality,
                'oee' => $oee,
            ];
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


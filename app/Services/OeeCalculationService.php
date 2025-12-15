<?php

namespace App\Services;

use App\Models\ProductionDailyGrade;
use Illuminate\Support\Collection;

class OeeCalculationService
{
    /**
     * Calculate OEE for a production record using pre-loaded data
     */
    public function calculateOeeForProduction(
        ProductionDailyGrade $production,
        \Illuminate\Support\Collection $hourlyRecords,
        \Illuminate\Support\Collection $downtimeRecords,
        \Illuminate\Support\Collection $productionDowntimes
    ): array {
        // Get Grade A from pre-loaded hourly data
        $gradeA = $hourlyRecords->where('hour', 0)->first()?->total_production 
            ?? $hourlyRecords->sum(function($item) {
                return is_numeric($item->total_production) ? (int)$item->total_production : 0;
            }) 
            ?? 0;
        $gradeA = (int) $gradeA;
        
        $gradeB = $production->grade_b ?? 0;
        $gradeC = $production->grade_c ?? 0;
        $totalProduction = $gradeA + $gradeB + $gradeC;
        
        // Get target_per_hour from pre-loaded data
        $targetPerHour = $hourlyRecords->where('hour', 0)->first()?->target_per_hour 
            ?? $production->target_per_hour 
            ?? 0;
        
        // Calculate production hours
        $productionHours = $this->calculateProductionHours($production);
        
        // Get downtime from pre-loaded data (already passed as collections)
        $line = $production->line;
        $process = $production->process;
        $plant = $line ? $line->plant : null;
        
        // Calculate total downtime minutes
        $totalDowntimeMinutes = $this->calculateTotalDowntimeMinutes($downtimeRecords, $productionDowntimes);
        $totalDowntimeHours = $totalDowntimeMinutes / 60;
        
        // Calculate OEE components
        $plannedProductionTime = $productionHours;
        $operatingTime = max(0, $plannedProductionTime - $totalDowntimeHours);
        
        $availability = $plannedProductionTime > 0 
            ? ($operatingTime / $plannedProductionTime) * 100 
            : 0;
        
        $targetOutput = $targetPerHour * $productionHours;
        $performance = $targetOutput > 0 
            ? ($totalProduction / $targetOutput) * 100 
            : 0;
        
        $quality = $totalProduction > 0 
            ? ($gradeA / $totalProduction) * 100 
            : 0;
        
        $oee = ($availability * $performance * $quality) / 10000;
        
        return [
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
    
    /**
     * Calculate production hours from start_time, end_time, and break_duration
     */
    private function calculateProductionHours(ProductionDailyGrade $production): float
    {
        if (!$production->start_time || !$production->end_time) {
            return 0;
        }
        
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
        
        return max(0, $totalHours - $breakDuration);
    }
    
    /**
     * Calculate total downtime minutes from downtime records
     */
    private function calculateTotalDowntimeMinutes(Collection $downtimeRecords, Collection $productionDowntimes): int
    {
        $total = 0;
        
        // Parse downtime duration from DowntimeErp2 (format: "X minutes")
        foreach ($downtimeRecords as $downtime) {
            $durationStr = $downtime->duration ?? '';
            if (preg_match('/(\d+)\s*minutes?/i', $durationStr, $matches)) {
                $total += (int)$matches[1];
            }
        }
        
        // Add production downtime minutes
        foreach ($productionDowntimes as $prodDowntime) {
            $total += $prodDowntime->duration_minutes ?? 0;
        }
        
        return $total;
    }
}


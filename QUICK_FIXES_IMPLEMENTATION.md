# Quick Fixes - Implementasi Optimasi Prioritas Tinggi

## 🚨 Fix N+1 Query Problem di OeeController

### File: `app/Http/Controllers/OeeController.php`

**Masalah:** Query di dalam loop menyebabkan N+1 problem

**Solusi:**

```php
public function index(Request $request)
{
    // ... existing code untuk get filter parameters ...
    
    // Get production data dengan eager loading
    $productionData = $query->orderBy('production_date', 'asc')
        ->orderBy('line_id', 'asc')
        ->with(['line.plant', 'process'])
        ->get();
    
    // Pre-load semua data yang diperlukan (BATCH QUERY)
    $productionDates = $productionData->pluck('production_date')->unique()->map(function($date) {
        return $date->format('Y-m-d');
    })->toArray();
    
    $lineProcessPairs = $productionData->map(function($prod) {
        return [
            'line_id' => $prod->line_id,
            'process_id' => $prod->process_id,
            'date' => $prod->production_date->format('Y-m-d')
        ];
    })->unique(function($item) {
        return $item['line_id'] . '-' . $item['process_id'] . '-' . $item['date'];
    });
    
    // Batch query ProductionHourly
    $hourlyData = ProductionHourly::whereIn('line_id', $productionData->pluck('line_id')->unique())
        ->whereIn('process_id', $productionData->pluck('process_id')->unique())
        ->whereIn(DB::raw('DATE(production_date)'), $productionDates)
        ->get()
        ->groupBy(function($item) {
            return $item->line_id . '-' . $item->process_id . '-' . $item->production_date->format('Y-m-d');
        });
    
    // Batch query DowntimeErp2
    $downtimeData = DowntimeErp2::whereIn('date', $productionDates)
        ->where('include_oee', true)
        ->get()
        ->groupBy(function($item) {
            return $item->date . '-' . ($item->plant ?? '') . '-' . ($item->process ?? '') . '-' . ($item->line ?? '');
        });
    
    // Batch query ProductionDailyDowntime
    $productionDowntimeData = \App\Models\ProductionDailyDowntime::whereIn('production_daily_grade_id', $productionData->pluck('id'))
        ->where('include_oee', true)
        ->get()
        ->groupBy('production_daily_grade_id');
    
    // Calculate OEE for each production record (tanpa query di loop)
    $oeeData = [];
    foreach ($productionData as $production) {
        $dateKey = $production->production_date->format('Y-m-d');
        $lineProcessKey = $production->line_id . '-' . $production->process_id . '-' . $dateKey;
        
        // Get Grade A from pre-loaded data
        $hourlyRecords = $hourlyData->get($lineProcessKey) ?? collect();
        $gradeA = $hourlyRecords->where('hour', 0)->first()?->total_production 
            ?? $hourlyRecords->sum('total_production') 
            ?? 0;
        $gradeA = (int) $gradeA;
        
        // Get target_per_hour from pre-loaded data
        $targetPerHour = $hourlyRecords->where('hour', 0)->first()?->target_per_hour 
            ?? $production->target_per_hour 
            ?? 0;
        
        // ... existing code untuk calculate production hours ...
        
        // Get downtime from pre-loaded data
        $line = $production->line;
        $process = $production->process;
        $plant = $line ? $line->plant : null;
        
        $plantName = $plant ? $plant->name : null;
        $processName = $process ? $process->name : null;
        $lineName = $line ? $line->name : null;
        
        $downtimeKey = $dateKey . '-' . ($plantName ?? '') . '-' . ($processName ?? '') . '-' . ($lineName ?? '');
        $downtimeRecords = $downtimeData->get($downtimeKey) ?? collect();
        
        // Calculate total downtime minutes
        $totalDowntimeMinutes = 0;
        foreach ($downtimeRecords as $downtime) {
            $durationStr = $downtime->duration ?? '';
            if (preg_match('/(\d+)\s*minutes?/i', $durationStr, $matches)) {
                $totalDowntimeMinutes += (int)$matches[1];
            }
        }
        
        // Get production downtimes from pre-loaded data
        $productionDowntimes = $productionDowntimeData->get($production->id) ?? collect();
        foreach ($productionDowntimes as $prodDowntime) {
            $totalDowntimeMinutes += $prodDowntime->duration_minutes;
        }
        
        // ... rest of existing calculation code ...
    }
    
    // ... rest of existing code ...
}
```

---

## ⚡ Implementasi Caching untuk Dashboard

### File: `app/Http/Controllers/DashboardController.php`

**Tambahkan di bagian atas class:**

```php
use Illuminate\Support\Facades\Cache;

public function index(Request $request)
{
    // ... existing code untuk get data source dan filter ...
    
    // Cache key berdasarkan filter
    $cacheKey = 'dashboard_stats_' . $dataSource . '_' . $currentYear . '_' . $currentMonth;
    
    // Cache untuk 1 jam (3600 detik)
    $stats = Cache::remember($cacheKey, 3600, function() use ($currentYear, $currentMonth, $dataSource) {
        if ($dataSource === 'downtime_erp2') {
            return $this->getDowntimeErp2Stats($currentYear, $currentMonth);
        } elseif ($dataSource === 'downtime_erp') {
            return $this->getDowntimeErpStats($currentYear, $currentMonth);
        } else {
            return $this->getDowntimeStats($currentYear, $currentMonth);
        }
    });
    
    // Cache untuk PM stats (lebih lama karena jarang berubah)
    $pmCacheKey = 'pm_stats_' . $currentYear . '_' . $currentMonth;
    $pmStats = Cache::remember($pmCacheKey, 7200, function() use ($currentYear, $currentMonth) {
        return [
            'pmSchedulesThisMonth' => PreventiveMaintenanceSchedule::whereYear('start_date', $currentYear)
                ->whereMonth('start_date', $currentMonth)
                ->count(),
            // ... other PM stats ...
        ];
    });
    
    // ... rest of existing code ...
}
```

**Tambahkan cache invalidation saat data berubah:**

```php
// Di controller yang mengubah downtime/maintenance data
use Illuminate\Support\Facades\Cache;

public function store(Request $request)
{
    // ... existing store logic ...
    
    // Clear dashboard cache
    Cache::forget('dashboard_stats_' . $dataSource . '_' . $year . '_' . $month);
    
    return redirect()->route('downtimes.index');
}
```

---

## 🗄️ Database Indexing

### Buat Migration Baru: `database/migrations/YYYY_MM_DD_HHMMSS_add_performance_indexes.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Index untuk downtime_erp2
        Schema::table('downtime_erp2', function (Blueprint $table) {
            $table->index(['date', 'include_oee'], 'idx_date_include_oee');
            $table->index(['plant', 'process', 'line', 'date'], 'idx_location_date');
            $table->index(['idMachine', 'date'], 'idx_machine_date');
            $table->index(['date', 'problemDowntime'], 'idx_date_problem');
        });
        
        // Index untuk production_daily_grades
        Schema::table('production_daily_grades', function (Blueprint $table) {
            $table->index(['production_date', 'line_id', 'process_id'], 'idx_prod_date_line_process');
        });
        
        // Index untuk production_hourly
        Schema::table('production_hourly', function (Blueprint $table) {
            $table->index(['production_date', 'line_id', 'process_id', 'hour'], 'idx_prod_hourly_composite');
        });
        
        // Index untuk preventive_maintenance_schedules
        Schema::table('preventive_maintenance_schedules', function (Blueprint $table) {
            $table->index(['start_date', 'status'], 'idx_pm_date_status');
            $table->index(['machine_erp_id', 'start_date'], 'idx_pm_machine_date');
        });
        
        // Index untuk downtimes (jika menggunakan)
        if (Schema::hasTable('downtimes')) {
            Schema::table('downtimes', function (Blueprint $table) {
                $table->index(['date', 'machine_id'], 'idx_downtime_date_machine');
                $table->index(['mekanik_id', 'date'], 'idx_downtime_mechanic_date');
            });
        }
    }
    
    public function down(): void
    {
        Schema::table('downtime_erp2', function (Blueprint $table) {
            $table->dropIndex('idx_date_include_oee');
            $table->dropIndex('idx_location_date');
            $table->dropIndex('idx_machine_date');
            $table->dropIndex('idx_date_problem');
        });
        
        Schema::table('production_daily_grades', function (Blueprint $table) {
            $table->dropIndex('idx_prod_date_line_process');
        });
        
        Schema::table('production_hourly', function (Blueprint $table) {
            $table->dropIndex('idx_prod_hourly_composite');
        });
        
        Schema::table('preventive_maintenance_schedules', function (Blueprint $table) {
            $table->dropIndex('idx_pm_date_status');
            $table->dropIndex('idx_pm_machine_date');
        });
        
        if (Schema::hasTable('downtimes')) {
            Schema::table('downtimes', function (Blueprint $table) {
                $table->dropIndex('idx_downtime_date_machine');
                $table->dropIndex('idx_downtime_mechanic_date');
            });
        }
    }
};
```

**Jalankan migration:**
```bash
php artisan migrate
```

---

## 🔧 Service Layer untuk OEE Calculation

### Buat File: `app/Services/OeeCalculationService.php`

```php
<?php

namespace App\Services;

use App\Models\ProductionDailyGrade;
use App\Models\ProductionHourly;
use App\Models\DowntimeErp2;
use Illuminate\Support\Facades\DB;

class OeeCalculationService
{
    public function calculateOeeForProduction(ProductionDailyGrade $production, array $hourlyData, array $downtimeData, array $productionDowntimeData): array
    {
        // Extract data dari pre-loaded arrays
        $dateKey = $production->production_date->format('Y-m-d');
        $lineProcessKey = $production->line_id . '-' . $production->process_id . '-' . $dateKey;
        
        // Get Grade A
        $gradeA = $hourlyData[$lineProcessKey]['grade_a'] ?? 0;
        $gradeB = $production->grade_b ?? 0;
        $gradeC = $production->grade_c ?? 0;
        $totalProduction = $gradeA + $gradeB + $gradeC;
        
        // Get target per hour
        $targetPerHour = $hourlyData[$lineProcessKey]['target_per_hour'] ?? $production->target_per_hour ?? 0;
        
        // Calculate production hours
        $productionHours = $this->calculateProductionHours($production);
        
        // Get downtime
        $line = $production->line;
        $process = $production->process;
        $plant = $line ? $line->plant : null;
        
        $plantName = $plant ? $plant->name : null;
        $processName = $process ? $process->name : null;
        $lineName = $line ? $line->name : null;
        
        $downtimeKey = $dateKey . '-' . ($plantName ?? '') . '-' . ($processName ?? '') . '-' . ($lineName ?? '');
        $totalDowntimeMinutes = $this->calculateTotalDowntimeMinutes(
            $downtimeData[$downtimeKey] ?? [],
            $productionDowntimeData[$production->id] ?? []
        );
        
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
            'planned_production_time' => $plannedProductionTime,
            'operating_time' => $operatingTime,
            'availability' => $availability,
            'performance' => $performance,
            'quality' => $quality,
            'oee' => $oee,
        ];
    }
    
    private function calculateProductionHours(ProductionDailyGrade $production): float
    {
        if (!$production->start_time || !$production->end_time) {
            return 0;
        }
        
        $startParts = explode(':', $production->start_time);
        $endParts = explode(':', $production->end_time);
        
        $startMinutes = (int)$startParts[0] * 60 + (int)($startParts[1] ?? 0);
        $endMinutes = (int)$endParts[0] * 60 + (int)($endParts[1] ?? 0);
        
        if ($endMinutes < $startMinutes) {
            $endMinutes += 24 * 60;
        }
        
        $totalMinutes = $endMinutes - $startMinutes;
        $totalHours = $totalMinutes / 60;
        $breakDuration = $production->break_duration ?? 0;
        
        return max(0, $totalHours - $breakDuration);
    }
    
    private function calculateTotalDowntimeMinutes(array $downtimeRecords, array $productionDowntimes): int
    {
        $total = 0;
        
        foreach ($downtimeRecords as $downtime) {
            $durationStr = $downtime->duration ?? '';
            if (preg_match('/(\d+)\s*minutes?/i', $durationStr, $matches)) {
                $total += (int)$matches[1];
            }
        }
        
        foreach ($productionDowntimes as $prodDowntime) {
            $total += $prodDowntime->duration_minutes;
        }
        
        return $total;
    }
}
```

**Update OeeController untuk menggunakan Service:**

```php
use App\Services\OeeCalculationService;

class OeeController extends Controller
{
    protected $oeeService;
    
    public function __construct(OeeCalculationService $oeeService)
    {
        $this->oeeService = $oeeService;
    }
    
    // ... dalam method index, gunakan service:
    foreach ($productionData as $production) {
        $oeeData[] = $this->oeeService->calculateOeeForProduction(
            $production, 
            $hourlyData, 
            $downtimeData, 
            $productionDowntimeData
        );
    }
}
```

---

## 📧 Email Notifications Setup

### Install Laravel Mail Package (jika belum)

```bash
composer require laravel/horizon  # Untuk queue monitoring
```

### Buat Notification Class: `app/Notifications/DowntimeAlert.php`

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DowntimeAlert extends Notification
{
    use Queueable;
    
    protected $downtime;
    
    public function __construct($downtime)
    {
        $this->downtime = $downtime;
    }
    
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }
    
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Downtime Alert: ' . $this->downtime->idMachine)
            ->line('New downtime detected:')
            ->line('Machine: ' . $this->downtime->idMachine)
            ->line('Problem: ' . $this->downtime->problemDowntime)
            ->line('Duration: ' . $this->downtime->duration)
            ->action('View Details', url('/downtimes/' . $this->downtime->id));
    }
    
    public function toArray($notifiable)
    {
        return [
            'downtime_id' => $this->downtime->id,
            'machine' => $this->downtime->idMachine,
            'problem' => $this->downtime->problemDowntime,
        ];
    }
}
```

**Gunakan di Controller:**

```php
use App\Notifications\DowntimeAlert;

// Di method store/update
if ($downtime->duration > 60) { // Alert jika > 1 jam
    $managers = User::whereIn('role', ['manager', 'general_manager'])->get();
    foreach ($managers as $manager) {
        $manager->notify(new DowntimeAlert($downtime));
    }
}
```

---

## ✅ Checklist Implementasi

- [ ] Fix N+1 queries di OeeController
- [ ] Implementasi caching untuk Dashboard
- [ ] Buat migration untuk database indexes
- [ ] Buat Service Layer untuk OEE calculation
- [ ] Setup email notifications
- [ ] Test performance improvement
- [ ] Monitor query performance dengan Laravel Debugbar/Telescope

---

## 📊 Expected Performance Improvement

- **Query Count:** Dari ~1000+ queries menjadi ~10-20 queries per page load
- **Page Load Time:** Dari 3-5 detik menjadi <1 detik
- **Database Load:** Reduced by 80-90%
- **Cache Hit Rate:** Target 70-80% untuk dashboard

---

**Catatan:** Test semua perubahan di development environment terlebih dahulu sebelum deploy ke production!


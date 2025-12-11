<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionHourly extends Model
{
    protected $table = 'production_hourly';

    protected $fillable = [
        'line_id',
        'process_id',
        'production_date',
        'hour',
        'target_per_hour',
        'total_production',
        'notes',
    ];

    protected $casts = [
        'production_date' => 'date',
        'hour' => 'integer',
        'target_per_hour' => 'integer',
        // total_production is now string to allow "(istirahat)"
    ];

    // Relationships
    public function line()
    {
        return $this->belongsTo(Line::class);
    }

    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    // Relationship with daily grades
    public function dailyGrade()
    {
        return $this->hasOne(ProductionDailyGrade::class, 'line_id', 'line_id')
            ->where('process_id', $this->process_id)
            ->whereDate('production_date', $this->production_date);
    }
    
    // Accessor for Grade A (calculated from daily grades if total_production is numeric)
    public function getGradeAAttribute()
    {
        if ($this->total_production === '(istirahat)' || !is_numeric($this->total_production)) {
            return null; // Cannot calculate if istirahat
        }
        
        $dailyGrade = $this->dailyGrade;
        if (!$dailyGrade) {
            return max(0, (int)$this->total_production);
        }
        
        $total = (int)$this->total_production;
        $gradeB = $dailyGrade->grade_b ?? 0;
        $gradeC = $dailyGrade->grade_c ?? 0;
        
        return max(0, $total - $gradeB - $gradeC);
    }
}

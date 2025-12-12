<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionDailyGrade extends Model
{
    protected $table = 'production_daily_grades';

    protected $fillable = [
        'line_id',
        'process_id',
        'production_date',
        'target_per_hour',
        'start_time',
        'end_time',
        'break_duration',
        'grade_b',
        'grade_c',
    ];

    protected $casts = [
        'production_date' => 'date',
        'start_time' => 'string',
        'end_time' => 'string',
        'break_duration' => 'decimal:1',
        'target_per_hour' => 'integer',
        'grade_b' => 'integer',
        'grade_c' => 'integer',
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

    public function downtimes()
    {
        return $this->hasMany(ProductionDailyDowntime::class);
    }
}

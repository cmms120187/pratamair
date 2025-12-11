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
        'grade_b',
        'grade_c',
    ];

    protected $casts = [
        'production_date' => 'date',
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
}

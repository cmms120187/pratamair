<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PredictiveMaintenanceExecution extends Model
{
    protected $fillable = [
        'schedule_id',
        'scheduled_date',
        'actual_start_time',
        'actual_end_time',
        'status',
        'performed_by',
        'measured_value',
        'measurement_status',
        'findings',
        'actions_taken',
        'notes',
        'checklist',
        'cost',
        'photo_before',
        'photo_after',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'measured_value' => 'decimal:4',
        'checklist' => 'array',
        'cost' => 'decimal:2',
    ];

    // Relationships
    public function schedule()
    {
        return $this->belongsTo(PredictiveMaintenanceSchedule::class, 'schedule_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeNormal($query)
    {
        return $query->where('measurement_status', 'normal');
    }

    public function scopeWarning($query)
    {
        return $query->where('measurement_status', 'warning');
    }

    public function scopeCritical($query)
    {
        return $query->where('measurement_status', 'critical');
    }

    // Calculate duration in minutes
    public function getDurationAttribute()
    {
        if ($this->actual_start_time && $this->actual_end_time) {
            return $this->actual_start_time->diffInMinutes($this->actual_end_time);
        }
        return null;
    }

    // Auto calculate measurement_status based on standard
    public function calculateMeasurementStatus()
    {
        if ($this->measured_value === null || !$this->schedule || !$this->schedule->standard) {
            return null;
        }

        return $this->schedule->standard->getMeasurementStatus($this->measured_value);
    }
}

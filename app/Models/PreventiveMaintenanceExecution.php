<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreventiveMaintenanceExecution extends Model
{
    protected $fillable = [
        'schedule_id',
        'scheduled_date',
        'actual_start_time',
        'actual_end_time',
        'status',
        'performed_by',
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
        'checklist' => 'array',
        'cost' => 'decimal:2',
    ];

    // Relationships
    public function schedule()
    {
        return $this->belongsTo(PreventiveMaintenanceSchedule::class, 'schedule_id');
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

    // Calculate duration in minutes
    public function getDurationAttribute()
    {
        if ($this->actual_start_time && $this->actual_end_time) {
            return $this->actual_start_time->diffInMinutes($this->actual_end_time);
        }
        return null;
    }
}

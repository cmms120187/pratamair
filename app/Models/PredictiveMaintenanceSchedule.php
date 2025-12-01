<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PredictiveMaintenanceSchedule extends Model
{
    protected $fillable = [
        'machine_erp_id',
        'maintenance_point_id',
        'standard_id',
        'title',
        'description',
        'frequency_type',
        'frequency_value',
        'start_date',
        'end_date',
        'preferred_time',
        'estimated_duration',
        'status',
        'assigned_to',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relationships
    public function machineErp()
    {
        return $this->belongsTo(MachineErp::class, 'machine_erp_id');
    }
    
    // Keep machine() for backward compatibility (deprecated)
    public function machine()
    {
        return $this->belongsTo(MachineErp::class, 'machine_erp_id');
    }

    public function maintenancePoint()
    {
        return $this->belongsTo(MaintenancePoint::class);
    }

    public function standard()
    {
        return $this->belongsTo(Standard::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function executions()
    {
        return $this->hasMany(PredictiveMaintenanceExecution::class, 'schedule_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now()->toDateString());
    }
}

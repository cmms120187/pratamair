<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionSchedule extends Model
{
    protected $fillable = [
        'machine_erp_id',
        'template_id',
        'title',
        'description',
        'frequency',
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
        'preferred_time' => 'datetime',
    ];

    // Relationships
    public function machineErp()
    {
        return $this->belongsTo(MachineErp::class, 'machine_erp_id');
    }

    public function template()
    {
        return $this->belongsTo(InspectionTemplate::class, 'template_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class, 'schedule_id');
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenancePoint extends Model
{
    protected $fillable = [
        'machine_type_id',
        'category',
        'standard_id',
        'frequency_type',
        'frequency_value',
        'name',
        'instruction',
        'sequence',
        'duration',
        'photo',
    ];

    // Relationships
    public function machineType()
    {
        return $this->belongsTo(MachineType::class);
    }

    // Scopes
    public function scopeAutonomous($query)
    {
        return $query->where('category', 'autonomous');
    }

    public function scopePreventive($query)
    {
        return $query->where('category', 'preventive');
    }

    public function scopePredictive($query)
    {
        return $query->where('category', 'predictive');
    }

    public function preventiveMaintenanceSchedules()
    {
        return $this->hasMany(PreventiveMaintenanceSchedule::class);
    }

    public function standard()
    {
        return $this->belongsTo(Standard::class);
    }
}

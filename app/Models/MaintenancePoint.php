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
        'photo_id',
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

    // Photo relationship
    public function photoModel()
    {
        return $this->belongsTo(Photo::class, 'photo_id');
    }

    // Get photo URL (prioritize photo_id, fallback to photo path)
    public function getPhotoUrlAttribute()
    {
        if ($this->photo_id && $this->photoModel) {
            return route('photos.show', $this->photo_id);
        }
        if ($this->photo) {
            return asset('public-storage/' . $this->photo);
        }
        return null;
    }

    public function standard()
    {
        return $this->belongsTo(Standard::class);
    }
}

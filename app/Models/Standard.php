<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Standard extends Model
{
    protected $fillable = [
        'name',
        'reference_type',
        'reference_code',
        'reference_name',
        'class',
        'unit',
        'min_value',
        'max_value',
        'target_value',
        'description',
        'keterangan',
        'photo',
        'machine_type_id',
        'status',
    ];

    protected $casts = [
        'min_value' => 'decimal:4',
        'max_value' => 'decimal:4',
        'target_value' => 'decimal:4',
    ];

    // Relationships
    public function machineTypes()
    {
        return $this->belongsToMany(MachineType::class, 'machine_type_standard');
    }
    
    // Keep old relationship for backward compatibility (if needed)
    public function machineType()
    {
        return $this->belongsToMany(MachineType::class, 'machine_type_standard')->first();
    }

    public function predictiveMaintenanceSchedules()
    {
        return $this->hasMany(PredictiveMaintenanceSchedule::class);
    }

    public function variants()
    {
        return $this->hasMany(StandardVariant::class);
    }

    // Photos relationship - many-to-many
    public function photos()
    {
        return $this->belongsToMany(StandardPhoto::class, 'standard_standard_photo', 'standard_id', 'standard_photo_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Helper method to check if a value is within standard
    public function isValueWithinStandard($value)
    {
        if ($this->min_value !== null && $value < $this->min_value) {
            return false;
        }
        if ($this->max_value !== null && $value > $this->max_value) {
            return false;
        }
        return true;
    }

    // Get measurement status based on value
    public function getMeasurementStatus($value)
    {
        if (!$this->isValueWithinStandard($value)) {
            // Check if critical (far from range) or warning (close to limit)
            if ($this->min_value !== null && $value < $this->min_value) {
                $diff = abs($value - $this->min_value);
                $range = $this->max_value !== null ? ($this->max_value - $this->min_value) : abs($this->min_value);
                return ($diff > $range * 0.2) ? 'critical' : 'warning';
            }
            if ($this->max_value !== null && $value > $this->max_value) {
                $diff = abs($value - $this->max_value);
                $range = $this->max_value !== null ? ($this->max_value - $this->min_value) : abs($this->max_value);
                return ($diff > $range * 0.2) ? 'critical' : 'warning';
            }
        }
        return 'normal';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionTemplateParameter extends Model
{
    protected $table = 'inspection_template_parameters';
    
    protected $fillable = [
        'inspection_template_id',
        'parameter_name',
        'unit',
        'min_value',
        'max_value',
        'sequence',
        'instruction',
        'photo',
    ];

    protected $casts = [
        'min_value' => 'decimal:4',
        'max_value' => 'decimal:4',
        'sequence' => 'integer',
    ];

    // Relationships
    public function template()
    {
        return $this->belongsTo(InspectionTemplate::class, 'inspection_template_id');
    }

    public function parameterValues()
    {
        return $this->hasMany(InspectionParameterValue::class, 'template_parameter_id');
    }

    // Helper methods
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return null;
    }

    public function checkValueStatus($value)
    {
        if ($this->min_value === null && $this->max_value === null) {
            return 'normal'; // No range defined
        }

        $value = (float) $value;
        $min = $this->min_value !== null ? (float) $this->min_value : PHP_FLOAT_MIN;
        $max = $this->max_value !== null ? (float) $this->max_value : PHP_FLOAT_MAX;

        if ($value < $min || $value > $max) {
            return 'critical';
        }

        // Check if value is within 10% margin from boundaries (warning)
        $range = $max - $min;
        if ($range > 0) {
            $margin = $range * 0.1; // 10% margin
            if (($this->min_value !== null && $value <= ($min + $margin)) || 
                ($this->max_value !== null && $value >= ($max - $margin))) {
                return 'warning';
            }
        }

        return 'normal';
    }
}

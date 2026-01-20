<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionParameterValue extends Model
{
    protected $table = 'inspection_parameter_values';
    
    protected $fillable = [
        'inspection_id',
        'template_parameter_id',
        'parameter_value',
        'status',
        'notes',
    ];

    protected $casts = [
        'parameter_value' => 'decimal:4',
        'status' => 'string',
    ];

    // Relationships
    public function inspection()
    {
        return $this->belongsTo(Inspection::class, 'inspection_id');
    }

    public function templateParameter()
    {
        return $this->belongsTo(InspectionTemplateParameter::class, 'template_parameter_id');
    }
}

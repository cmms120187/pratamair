<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionTemplate extends Model
{
    protected $table = 'inspection_templates';
    
    protected $fillable = [
        'machine_type_id',
        'name',
        'description',
        'status',
        'frequency',
    ];

    protected $casts = [
        'status' => 'string',
        'frequency' => 'string',
    ];

    // Relationships
    public function machineType()
    {
        return $this->belongsTo(MachineType::class, 'machine_type_id');
    }

    public function parameters()
    {
        return $this->hasMany(InspectionTemplateParameter::class, 'inspection_template_id')->orderBy('sequence');
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class, 'template_id');
    }
}

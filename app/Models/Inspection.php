<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    protected $table = 'inspections';
    protected $fillable = [
        'machine_erp_id', 'inspection_date', 'performed_by', 'notes', 'template_id', 'schedule_id'
    ];

    protected $casts = [
        'inspection_date' => 'date',
    ];

    public function machine()
    {
        return $this->belongsTo(MachineErp::class, 'machine_erp_id');
    }

    public function template()
    {
        return $this->belongsTo(InspectionTemplate::class, 'template_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'performed_by');
    }

    public function parameterValues()
    {
        return $this->hasMany(InspectionParameterValue::class, 'inspection_id');
    }

    public function schedule()
    {
        return $this->belongsTo(InspectionSchedule::class, 'schedule_id');
    }
}

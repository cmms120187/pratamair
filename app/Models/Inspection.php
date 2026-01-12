<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    protected $table = 'inspections';
    protected $fillable = [
        'machine_erp_id', 'inspection_date', 'performed_by', 'notes', 'template_id'
    ];

    public function machine()
    {
        return $this->belongsTo(MachineErp::class, 'machine_erp_id');
    }

    public function template()
    {
        return $this->belongsTo(InspectionTemplate::class, 'template_id');
    }
}

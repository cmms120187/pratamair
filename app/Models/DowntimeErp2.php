<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DowntimeErp2 extends Model
{
    protected $table = 'downtime_erp2';
    
    protected $fillable = [
        'date',
        'kode_room',
        'plant',
        'process',
        'line',
        'roomName',
        'include_oee',
        'idMachine',
        'typeMachine',
        'modelMachine',
        'brandMachine',
        'stopProduction',
        'responMechanic',
        'startProduction',
        'duration',
        'Standar_Time',
        'problemDowntime',
        'Problem_MM',
        'reasonDowntime',
        'actionDowtime',
        'Part',
        'part_erp_id',
        'part_quantity',
        'idMekanik',
        'nameMekanik',
        'idLeader',
        'nameLeader',
        'idGL',
        'nameGL',
        'idCoord',
        'nameCoord',
        'system_id'
    ];

    protected $casts = [
        'include_oee' => 'boolean',
        'date' => 'date',
    ];

    /**
     * Get the system that owns the downtime.
     */
    public function system()
    {
        return $this->belongsTo(System::class);
    }

    /**
     * Get the part (PartErp) used in this downtime.
     */
    public function partErp()
    {
        return $this->belongsTo(PartErp::class, 'part_erp_id');
    }
}

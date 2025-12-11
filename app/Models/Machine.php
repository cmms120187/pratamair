<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    protected $fillable = [
        'idMachine', 'plant_id', 'process_id', 'line_id', 'room_id', 'type_id', 'brand_id', 'model_id',
        'serial_number', 'tahun_production', 'no_document', 'photo'
    ];

    // Relationships
    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function line()
    {
        return $this->belongsTo(Line::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function machineType()
    {
        return $this->belongsTo(MachineType::class, 'type_id');
    }

    // Accessor to get group through machineType
    public function getGroupAttribute()
    {
        return $this->machineType->groupRelation ?? null;
    }

    // Accessor to get systems through machineType
    public function getSystemsAttribute()
    {
        return $this->machineType->systems ?? collect();
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function model()
    {
        return $this->belongsTo(\App\Models\Model::class, 'model_id');
    }

    public function downtimes()
    {
        return $this->hasMany(Downtime::class);
    }

    public function preventiveMaintenanceSchedules()
    {
        return $this->hasMany(PreventiveMaintenanceSchedule::class);
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function machineErp()
    {
        return $this->hasOne(MachineErp::class, 'idMachine', 'idMachine');
    }
}

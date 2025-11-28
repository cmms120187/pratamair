<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineType extends Model
{
    protected $fillable = ['name', 'model', 'group', 'group_id', 'brand', 'description', 'photo'];

    // Relationships
    public function groupRelation()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
    
    // Accessor to get group relation (avoid conflict with 'group' column)
    public function getGroupModelAttribute()
    {
        return $this->groupRelation;
    }

    public function models()
    {
        return $this->hasMany(\App\Models\Model::class, 'type_id');
    }

    public function machines()
    {
        return $this->hasMany(Machine::class, 'type_id');
    }

    public function maintenancePoints()
    {
        return $this->hasMany(MaintenancePoint::class);
    }

    public function systems()
    {
        return $this->belongsToMany(System::class, 'machine_type_system');
    }
}

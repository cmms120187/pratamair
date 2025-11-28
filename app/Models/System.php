<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    protected $fillable = [
        'nama_sistem',
        'deskripsi'
    ];

    public function machineTypes()
    {
        return $this->belongsToMany(\App\Models\MachineType::class, 'machine_type_system');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_system');
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    protected $fillable = ['name', 'brand_id', 'type_id', 'photo'];

    // Relationships
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function machineType()
    {
        return $this->belongsTo(MachineType::class, 'type_id');
    }

    public function machines()
    {
        return $this->hasMany(Machine::class, 'model_id');
    }
}

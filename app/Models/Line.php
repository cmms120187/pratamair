<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Line extends Model
{
    protected $fillable = ['name', 'plant_id', 'process_id'];

    // Relationships
    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function machines()
    {
        return $this->hasMany(Machine::class);
    }
}

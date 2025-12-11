<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['name', 'plant_id', 'line_id', 'process_id', 'category', 'description'];

    // Relationships
    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function line()
    {
        return $this->belongsTo(Line::class);
    }

    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function machines()
    {
        return $this->hasMany(Machine::class);
    }
}

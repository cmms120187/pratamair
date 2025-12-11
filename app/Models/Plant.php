<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plant extends Model
{
    protected $fillable = ['name'];

    // Relationships
    public function lines()
    {
        return $this->hasMany(Line::class);
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

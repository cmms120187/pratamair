<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['name'];

    // Relationships
    public function models()
    {
        return $this->hasMany(\App\Models\Model::class);
    }

    public function machines()
    {
        return $this->hasMany(Machine::class);
    }
}

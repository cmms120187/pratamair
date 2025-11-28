<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Problem extends Model
{
    protected $fillable = ['name', 'group', 'problem_header', 'problem_mm'];

    public function downtimes()
    {
        return $this->hasMany(Downtime::class);
    }

    public function systems()
    {
        return $this->belongsToMany(System::class, 'problem_system');
    }
}

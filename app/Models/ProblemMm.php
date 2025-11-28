<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProblemMm extends Model
{
    protected $fillable = ['name'];

    public function downtimes()
    {
        return $this->hasMany(Downtime::class, 'problem_mm_id');
    }
}

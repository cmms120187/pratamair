<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reason extends Model
{
    protected $fillable = ['name', 'system_id', 'problem_id'];
    
    public function system()
    {
        return $this->belongsTo(System::class);
    }
    
    public function problem()
    {
        return $this->belongsTo(Problem::class);
    }

    public function downtimes()
    {
        return $this->hasMany(Downtime::class);
    }
}

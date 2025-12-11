<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    protected $fillable = ['name', 'system_id', 'problem_id', 'reason_id'];
    
    public function system()
    {
        return $this->belongsTo(System::class);
    }
    
    public function problem()
    {
        return $this->belongsTo(Problem::class);
    }
    
    public function reason()
    {
        return $this->belongsTo(Reason::class);
    }

    public function downtimes()
    {
        return $this->hasMany(Downtime::class);
    }
}

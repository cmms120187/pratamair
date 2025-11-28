<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Downtime extends Model
{
    protected $fillable = [
        'machine_id',
        'date',
        'stopProduction',
        'responMechanic',
        'startProduction',
        'duration',
        'standard_time',
        'problem_id',
        'problem_mm_id',
        'reason_id',
        'action_id',
        'group_id',
        'part',
        'mekanik_id',
        'leader_id',
        'coord_id',
    ];

    protected $casts = [
        'date' => 'date',
        'stopProduction' => 'datetime',
        'responMechanic' => 'datetime',
        'startProduction' => 'datetime',
    ];

    // Relationships
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function plant()
    {
        return $this->hasOneThrough(Plant::class, Machine::class, 'id', 'id', 'machine_id', 'plant_id');
    }

    public function room()
    {
        return $this->hasOneThrough(Room::class, Machine::class, 'id', 'id', 'machine_id', 'room_id');
    }

    public function problem()
    {
        return $this->belongsTo(Problem::class);
    }

    public function problemMm()
    {
        return $this->belongsTo(ProblemMm::class, 'problem_mm_id');
    }

    public function reason()
    {
        return $this->belongsTo(Reason::class);
    }

    public function action()
    {
        return $this->belongsTo(Action::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function mekanik()
    {
        return $this->belongsTo(User::class, 'mekanik_id');
    }

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function coord()
    {
        return $this->belongsTo(User::class, 'coord_id');
    }
}

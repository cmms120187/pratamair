<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DowntimeErp extends Model
{
    protected $table = 'downtime_erp';
    protected $fillable = [
        'date','plant','process','line','roomName','idMachine','typeMachine','modelMachine','brandMachine','stopProduction','responMechanic','startProduction','duration','Standar_Time','problemDowntime','Problem_MM','reasonDowntime','actionDowtime','Part','idMekanik','nameMekanik','idLeader','nameLeader','idGL','nameGL','idCoord','nameCoord','groupProblem'
    ];
}

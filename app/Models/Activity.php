<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'date',
        'kode_room',
        'plant',
        'process',
        'line',
        'room_name',
        'start',
        'stop',
        'duration',
        'description',
        'remarks',
        'id_mekanik',
        'nama_mekanik',
        'id_mesin',
        'photos'
    ];

    protected $casts = [
        'photos' => 'array',
    ];

    /**
     * Calculate duration in minutes from start and stop time
     */
    public function calculateDuration()
    {
        if (empty($this->start) || empty($this->stop)) {
            return null;
        }

        try {
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $this->start);
            $stopTime = \Carbon\Carbon::createFromFormat('H:i', $this->stop);
            
            // Handle case where stop time is next day
            if ($stopTime->lt($startTime)) {
                $stopTime->addDay();
            }
            
            return $startTime->diffInMinutes($stopTime);
        } catch (\Exception $e) {
            return null;
        }
    }
}

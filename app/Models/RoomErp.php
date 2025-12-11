<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomErp extends Model
{
    protected $table = 'room_erp';
    
    protected $fillable = [
        'kode_room',
        'name',
        'category',
        'plant_name',
        'line_name',
        'process_name',
        'description'
    ];
}

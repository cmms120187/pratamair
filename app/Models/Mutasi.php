<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mutasi extends Model
{
    protected $table = 'mutasi';
    
    protected $fillable = [
        'machine_erp_id',
        'old_room_erp_id',
        'new_room_erp_id',
        'date',
        'reason',
        'description'
    ];
    
    protected $casts = [
        'date' => 'date',
    ];
    
    // Relasi ke MachineErp
    public function machineErp()
    {
        return $this->belongsTo(MachineErp::class, 'machine_erp_id');
    }
    
    // Relasi ke RoomErp (room lama)
    public function oldRoomErp()
    {
        return $this->belongsTo(RoomErp::class, 'old_room_erp_id');
    }
    
    // Relasi ke RoomErp (room baru)
    public function newRoomErp()
    {
        return $this->belongsTo(RoomErp::class, 'new_room_erp_id');
    }
}

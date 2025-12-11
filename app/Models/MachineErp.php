<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineErp extends Model
{
    protected $table = 'machine_erp';
    
    protected $fillable = [
        'idMachine',
        'kode_room',
        'plant_name',
        'process_name',
        'line_name',
        'room_name',
        'type_name',
        'brand_name',
        'model_name',
        'serial_number',
        'tahun_production',
        'no_document',
        'photo',
        'machine_type_id'
    ];

    // Relationships
    public function machineType()
    {
        return $this->belongsTo(MachineType::class, 'machine_type_id');
    }

    public function roomErp()
    {
        return $this->belongsTo(RoomErp::class, 'kode_room', 'kode_room');
    }

    /**
     * Get photo from model if machine_erp photo is not available
     * Priority: machine_erp photo > model photo > machine_type photo
     * Note: This accessor is used for display purposes only
     */
    public function getDisplayPhotoAttribute()
    {
        // If machine_erp has its own photo, return it
        $actualPhoto = $this->attributes['photo'] ?? null;
        if ($actualPhoto) {
            return $actualPhoto;
        }

        // Try to get photo from model (based on type_name and model_name)
        if ($this->type_name && $this->model_name && $this->machineType) {
            $model = \App\Models\Model::where('type_id', $this->machineType->id)
                ->where('name', $this->model_name)
                ->first();
            
            if ($model && $model->photo) {
                return $model->photo;
            }
        }

        // Fallback to machine_type photo
        if ($this->machineType && $this->machineType->photo) {
            return $this->machineType->photo;
        }

        return null;
    }
}

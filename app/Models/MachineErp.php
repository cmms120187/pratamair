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
        'photo_id',
        'machine_type_id',
        'status'
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

    public function preventiveMaintenanceSchedules()
    {
        return $this->hasMany(PreventiveMaintenanceSchedule::class, 'machine_erp_id');
    }

    public function predictiveMaintenanceSchedules()
    {
        return $this->hasMany(PredictiveMaintenanceSchedule::class, 'machine_erp_id');
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

    // Photo relationship
    public function photoModel()
    {
        return $this->belongsTo(Photo::class, 'photo_id');
    }

    // Get photo URL (prioritize photo_id, fallback to photo path, then display photo)
    public function getPhotoUrlAttribute()
    {
        if ($this->photo_id && $this->photoModel) {
            return route('photos.show', $this->photo_id);
        }
        if ($this->photo) {
            return asset('public-storage/' . $this->photo);
        }
        // Fallback to display photo (from model or machine type)
        $displayPhoto = $this->display_photo;
        if ($displayPhoto) {
            // Try to find in photos table
            $photo = Photo::where('file_path', $displayPhoto)->first();
            if ($photo) {
                return route('photos.show', $photo->id);
            }
            return asset('public-storage/' . $displayPhoto);
        }
        return null;
    }
}

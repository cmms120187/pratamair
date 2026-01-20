<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    protected $fillable = ['name', 'brand_id', 'type_id', 'photo'];

    // Relationships
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function machineType()
    {
        return $this->belongsTo(MachineType::class, 'type_id');
    }

    public function machines()
    {
        return $this->hasMany(Machine::class, 'model_id');
    }

    // Photo relationship
    public function photoModel()
    {
        return $this->belongsTo(Photo::class, 'photo_id');
    }

    // Get photo URL (prioritize photo_id, fallback to photo path)
    public function getPhotoUrlAttribute()
    {
        if ($this->photo_id && $this->photoModel) {
            return route('photos.show', $this->photo_id);
        }
        if ($this->photo) {
            return asset('public-storage/' . $this->photo);
        }
        return null;
    }
}

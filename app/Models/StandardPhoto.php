<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StandardPhoto extends Model
{
    protected $fillable = [
        'standard_id',
        'photo_path',
        'name',
    ];

    // Relationships - many-to-many
    public function standards()
    {
        return $this->belongsToMany(Standard::class, 'standard_standard_photo', 'standard_photo_id', 'standard_id');
    }
    
    // Keep old relationship for backward compatibility (if standard_id is still used)
    public function standard()
    {
        return $this->belongsTo(Standard::class);
    }
}

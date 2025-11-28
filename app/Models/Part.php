<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    protected $fillable = [
        'part_number', 'name', 'description', 'brand', 
        'unit', 'stock', 'price', 'location'
    ];

    public function systems()
    {
        return $this->belongsToMany(System::class, 'part_system');
    }
}

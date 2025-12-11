<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['name'];

    // Relationships
    public function systems()
    {
        return $this->belongsToMany(System::class, 'group_system');
    }
}

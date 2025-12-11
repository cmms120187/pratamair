<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StandardVariant extends Model
{
    protected $fillable = [
        'standard_id',
        'name',
        'min_value',
        'max_value',
        'color',
        'order',
    ];

    protected $casts = [
        'min_value' => 'decimal:4',
        'max_value' => 'decimal:4',
    ];

    // Relationships
    public function standard()
    {
        return $this->belongsTo(Standard::class);
    }
}


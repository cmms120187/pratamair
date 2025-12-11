<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartErp extends Model
{
    protected $table = 'part_erp';
    
    protected $fillable = [
        'part_number',
        'name',
        'description',
        'category',
        'brand',
        'unit',
        'stock',
        'price',
        'location'
    ];

    /**
     * Relationship with System (category)
     * category stores nama_sistem, not id
     */
    public function system()
    {
        return $this->belongsTo(System::class, 'category', 'nama_sistem');
    }

    /**
     * Relationship with MachineTypes (location - many to many)
     */
    public function machineTypes()
    {
        return $this->belongsToMany(MachineType::class, 'part_erp_machine_type', 'part_erp_id', 'machine_type_id');
    }
}

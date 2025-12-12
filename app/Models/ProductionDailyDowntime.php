<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionDailyDowntime extends Model
{
    protected $table = 'production_daily_downtimes';

    protected $fillable = [
        'production_daily_grade_id',
        'downtime_type',
        'start_time',
        'end_time',
        'duration_minutes',
        'description',
        'include_oee',
    ];

    protected $casts = [
        'start_time' => 'string',
        'end_time' => 'string',
        'duration_minutes' => 'integer',
        'include_oee' => 'boolean',
    ];

    // Relationships
    public function productionDailyGrade()
    {
        return $this->belongsTo(ProductionDailyGrade::class);
    }

    // Downtime types
    public static function getDowntimeTypes()
    {
        return [
            'process' => 'Proses Produksi',
            'quality' => 'Masalah Kualitas',
            'material' => 'Kekurangan Material',
            'changeover' => 'Changeover/Setup',
            'planned_maintenance' => 'Perawatan Terjadwal',
            'human_error' => 'Kesalahan Operator',
            'power_system' => 'Masalah Listrik/Sistem',
            'waiting_material' => 'Menunggu Material',
            'waiting_operator' => 'Menunggu Operator',
            'process_adjustment' => 'Penyesuaian Proses',
            'quality_inspection' => 'Inspeksi Kualitas',
        ];
    }
}

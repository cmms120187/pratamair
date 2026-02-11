<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartErpStockMovement extends Model
{
    protected $table = 'part_erp_stock_movements';

    protected $fillable = [
        'part_erp_id',
        'type',
        'document_type',
        'document_number',
        'quantity',
        'balance_after',
        'notes',
        'user_id',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'balance_after' => 'integer',
    ];

    public const DOCUMENT_TYPES = ['MR', 'PO', 'MO'];
    public const MOVEMENT_TYPES = ['in' => 'Masuk', 'out' => 'Keluar', 'adjustment' => 'Penyesuaian'];
    public const REFERENCE_TYPES = [
        'manual' => 'Manual (MR/PO/MO)',
        'downtime_erp2' => 'Downtime ERP2',
        'downtime_erp' => 'Downtime ERP',
        'preventive_maintenance_execution' => 'Preventive Maintenance',
        'work_order' => 'Work Order',
        'other' => 'Lainnya',
    ];

    public function partErp()
    {
        return $this->belongsTo(PartErp::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Resolve reference model (downtime, PM execution, work order, etc.)
     */
    public function getReferenceModel()
    {
        if (!$this->reference_type || !$this->reference_id) {
            return null;
        }
        return match ($this->reference_type) {
            'downtime_erp2' => \App\Models\DowntimeErp2::find($this->reference_id),
            'downtime_erp' => \App\Models\DowntimeErp::find($this->reference_id),
            'preventive_maintenance_execution' => \App\Models\PreventiveMaintenanceExecution::find($this->reference_id),
            'work_order' => \App\Models\WorkOrder::find($this->reference_id),
            default => null,
        };
    }

    /**
     * Human-readable label for reference (with link route if applicable).
     */
    public function getReferenceLabel(): string
    {
        if (!$this->reference_type || !$this->reference_id) {
            return $this->document_type && $this->document_number
                ? "{$this->document_type} #{$this->document_number}"
                : '-';
        }
        $model = $this->getReferenceModel();
        if (!$model) {
            return "{$this->reference_type} #{$this->reference_id}";
        }
        $label = $this->reference_type . ' #' . $this->reference_id;
        if ($model instanceof \App\Models\DowntimeErp2) {
            $dateStr = $model->date ? \Carbon\Carbon::parse($model->date)->format('d/m/Y') : '-';
            $label = 'Downtime ERP2 #' . $model->id . ' (' . $dateStr . ', ' . ($model->idMachine ?? '-') . ')';
        } elseif ($model instanceof \App\Models\DowntimeErp) {
            $label = 'Downtime ERP #' . $model->id . ' (' . ($model->date ? \Carbon\Carbon::parse($model->date)->format('d/m/Y') : '-') . ')';
        } elseif ($model instanceof \App\Models\PreventiveMaintenanceExecution) {
            $pmDate = $model->scheduled_date ? \Carbon\Carbon::parse($model->scheduled_date)->format('d/m/Y') : '-';
            $label = 'PM Execution #' . $model->id . ' (' . $pmDate . ')';
        } elseif ($model instanceof \App\Models\WorkOrder) {
            $label = 'Work Order #' . $model->id;
        }
        return $label;
    }
}

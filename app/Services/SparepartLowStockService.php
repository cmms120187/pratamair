<?php

namespace App\Services;

use App\Models\PartErp;
use App\Models\User;
use App\Notifications\SparepartLowStockAlert;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class SparepartLowStockService
{
    /**
     * Check and send alerts for low stock spareparts
     * 
     * @param PartErp|null $part If provided, only check this part. Otherwise check all parts.
     * @return void
     */
    public function checkAndSendAlerts(?PartErp $part = null)
    {
        // Check if alert is enabled
        if (!config('downtime_alert.enabled', true)) {
            return;
        }

        $parts = $part ? collect([$part]) : PartErp::all();
        
        foreach ($parts as $partItem) {
            if ($this->isLowStock($partItem)) {
                $this->sendAlert($partItem);
            }
        }
    }

    /**
     * Check if part is low stock
     * 
     * @param PartErp $part
     * @return bool
     */
    public function isLowStock(PartErp $part): bool
    {
        $stock = $part->stock ?? 0;
        $minimumStock = $part->minimum_stock ?? 0;
        
        // Alert if stock is below minimum
        return $stock < $minimumStock && $minimumStock > 0;
    }

    /**
     * Send alert to recipients
     * 
     * @param PartErp $part
     * @return void
     */
    public function sendAlert(PartErp $part)
    {
        $recipients = $this->getRecipients();

        if ($recipients->isEmpty()) {
            Log::warning('Sparepart Low Stock Alert: No recipients found. Please configure downtime_alert.recipients in config file.');
            return;
        }

        foreach ($recipients as $recipient) {
            try {
                $recipient->notify(new SparepartLowStockAlert($part));
                Log::info("Sparepart Low Stock Alert sent to: {$recipient->email} for part: {$part->part_number}");
            } catch (\Exception $e) {
                Log::error("Failed to send sparepart low stock alert to {$recipient->email}: " . $e->getMessage());
            }
        }
    }

    /**
     * Get recipients for sparepart low stock alert
     * Uses same configuration as downtime alert
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getRecipients()
    {
        $config = config('downtime_alert.recipients', []);
        $recipients = collect();

        // Priority 1: Get by specific emails
        if (!empty($config['emails'])) {
            $emailUsers = User::whereIn('email', $config['emails'])->get();
            $recipients = $recipients->merge($emailUsers);
        }

        // Priority 2: Get by specific user IDs
        if (!empty($config['user_ids'])) {
            $idUsers = User::whereIn('id', $config['user_ids'])->get();
            $recipients = $recipients->merge($idUsers);
        }

        // Priority 3: Get by roles
        if ($recipients->isEmpty() && !empty($config['roles'])) {
            $roleUsers = User::whereIn('role', $config['roles'])->get();
            $recipients = $recipients->merge($roleUsers);
        }

        // Remove duplicates by email
        return $recipients->unique('email');
    }

    /**
     * Get all low stock parts
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowStockParts()
    {
        return PartErp::whereColumn('stock', '<', 'minimum_stock')
            ->where('minimum_stock', '>', 0)
            ->get();
    }
}


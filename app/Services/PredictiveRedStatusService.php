<?php

namespace App\Services;

use App\Models\PredictiveMaintenanceExecution;
use App\Models\User;
use App\Notifications\PredictiveRedStatusAlert;
use Illuminate\Support\Facades\Log;

class PredictiveRedStatusService
{
    /**
     * Check and send alerts for predictive red status
     * 
     * @param PredictiveMaintenanceExecution|null $execution If provided, only check this execution. Otherwise check recent executions.
     * @return void
     */
    public function checkAndSendAlerts(?PredictiveMaintenanceExecution $execution = null)
    {
        // Check if alert is enabled
        if (!config('downtime_alert.enabled', true)) {
            return;
        }

        if ($execution) {
            if ($this->isRedStatus($execution)) {
                $this->sendAlert($execution);
            }
        } else {
            // Check recent executions with red status (last 24 hours)
            $recentRedExecutions = PredictiveMaintenanceExecution::where('measurement_status', 'critical')
                ->where('created_at', '>=', now()->subDay())
                ->get();
            
            foreach ($recentRedExecutions as $exec) {
                $this->sendAlert($exec);
            }
        }
    }

    /**
     * Check if execution has red status
     * 
     * @param PredictiveMaintenanceExecution $execution
     * @return bool
     */
    public function isRedStatus(PredictiveMaintenanceExecution $execution): bool
    {
        // Red status = critical
        return $execution->measurement_status === 'critical';
    }

    /**
     * Send alert to recipients
     * 
     * @param PredictiveMaintenanceExecution $execution
     * @return void
     */
    public function sendAlert(PredictiveMaintenanceExecution $execution)
    {
        $recipients = $this->getRecipients();

        if ($recipients->isEmpty()) {
            Log::warning('Predictive Red Status Alert: No recipients found. Please configure downtime_alert.recipients in config file.');
            return;
        }

        foreach ($recipients as $recipient) {
            try {
                $recipient->notify(new PredictiveRedStatusAlert($execution));
                Log::info("Predictive Red Status Alert sent to: {$recipient->email} for execution: {$execution->id}");
            } catch (\Exception $e) {
                Log::error("Failed to send predictive red status alert to {$recipient->email}: " . $e->getMessage());
            }
        }
    }

    /**
     * Get recipients for predictive red status alert
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
     * Get all red status executions
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRedStatusExecutions()
    {
        return PredictiveMaintenanceExecution::where('measurement_status', 'critical')
            ->with(['schedule.standard', 'schedule.machine'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}


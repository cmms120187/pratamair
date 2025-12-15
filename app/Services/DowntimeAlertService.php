<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\DowntimeAlert;
use Illuminate\Support\Facades\Log;

class DowntimeAlertService
{
    /**
     * Get recipients for downtime alert
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecipients()
    {
        $config = config('downtime_alert.recipients');
        $recipients = collect();

        // Priority 1: Get by specific emails (highest priority)
        if (!empty($config['emails'])) {
            $emailUsers = User::whereIn('email', $config['emails'])->get();
            $recipients = $recipients->merge($emailUsers);
        }

        // Priority 2: Get by specific user IDs
        if (!empty($config['user_ids'])) {
            $idUsers = User::whereIn('id', $config['user_ids'])->get();
            $recipients = $recipients->merge($idUsers);
        }

        // Priority 3: Get by roles (if no specific emails/IDs set)
        if ($recipients->isEmpty() && !empty($config['roles'])) {
            $roleUsers = User::whereIn('role', $config['roles'])->get();
            $recipients = $recipients->merge($roleUsers);
        }

        // Remove duplicates by email
        return $recipients->unique('email');
    }

    /**
     * Check if downtime should trigger alert
     * 
     * @param object $downtime
     * @param string $downtimeType
     * @param int|null $durationMinutes
     * @return bool
     */
    public function shouldSendAlert($downtime, string $downtimeType = 'downtime_erp2', ?int $durationMinutes = null): bool
    {
        // Check if alert is enabled
        if (!config('downtime_alert.enabled', true)) {
            return false;
        }

        $alertTypes = config('downtime_alert.alert_types', []);

        // Check duration threshold
        if ($alertTypes['duration'] ?? true) {
            $threshold = config('downtime_alert.duration_threshold', 60);
            if ($durationMinutes !== null && $durationMinutes > $threshold) {
                return true;
            }
        }

        // Check critical problem
        if ($alertTypes['critical_problem'] ?? true) {
            $problem = $this->getProblemInfo($downtime, $downtimeType);
            $criticalProblems = config('downtime_alert.critical_problems', []);
            foreach ($criticalProblems as $critical) {
                if (stripos($problem, $critical) !== false) {
                    return true;
                }
            }
        }

        // Check critical machine
        if ($alertTypes['critical_machine'] ?? true) {
            $machineId = $this->getMachineId($downtime, $downtimeType);
            $criticalMachines = config('downtime_alert.critical_machines', []);
            if (in_array($machineId, $criticalMachines)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Send downtime alert to recipients
     * 
     * @param object $downtime
     * @param string $downtimeType
     * @param int|null $durationMinutes
     * @return void
     */
    public function sendAlert($downtime, string $downtimeType = 'downtime_erp2', ?int $durationMinutes = null)
    {
        if (!$this->shouldSendAlert($downtime, $downtimeType, $durationMinutes)) {
            return;
        }

        $recipients = $this->getRecipients();

        if ($recipients->isEmpty()) {
            Log::warning('Downtime Alert: No recipients found. Please configure downtime_alert.recipients in config file.');
            return;
        }

        foreach ($recipients as $recipient) {
            try {
                $recipient->notify(new DowntimeAlert($downtime, $downtimeType, $durationMinutes));
                Log::info("Downtime Alert sent to: {$recipient->email}");
            } catch (\Exception $e) {
                Log::error("Failed to send downtime alert to {$recipient->email}: " . $e->getMessage());
            }
        }
    }

    /**
     * Get problem info from downtime
     */
    private function getProblemInfo($downtime, string $downtimeType): string
    {
        if ($downtimeType === 'downtime_erp2' || $downtimeType === 'downtime_erp') {
            return $downtime->problemDowntime ?? '';
        }
        return $downtime->problem->name ?? '';
    }

    /**
     * Get machine ID from downtime
     */
    private function getMachineId($downtime, string $downtimeType): string
    {
        if ($downtimeType === 'downtime_erp2' || $downtimeType === 'downtime_erp') {
            return $downtime->idMachine ?? '';
        }
        return $downtime->machine->idMachine ?? '';
    }
}


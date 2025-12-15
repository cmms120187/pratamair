<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DowntimeAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected $downtime;
    protected $downtimeType; // 'downtime_erp2', 'downtime_erp', or 'downtime'
    protected $durationMinutes;

    /**
     * Create a new notification instance.
     */
    public function __construct($downtime, string $downtimeType = 'downtime_erp2', ?int $durationMinutes = null)
    {
        $this->downtime = $downtime;
        $this->downtimeType = $downtimeType;
        $this->durationMinutes = $durationMinutes;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $machine = $this->getMachineInfo();
        $problem = $this->getProblemInfo();
        $duration = $this->getDurationInfo();
        $location = $this->getLocationInfo();
        
        $subject = 'Downtime Alert: ' . $machine . ' - ' . $problem;
        
        $message = (new MailMessage)
            ->subject($subject)
            ->line('**Downtime Alert Detected**')
            ->line('A new downtime has been recorded in the system.')
            ->line('')
            ->line('**Machine:** ' . $machine)
            ->line('**Location:** ' . $location)
            ->line('**Problem:** ' . $problem)
            ->line('**Duration:** ' . $duration)
            ->line('**Date:** ' . $this->getDateInfo());
        
        // Add action button based on downtime type
        if ($this->downtimeType === 'downtime_erp2') {
            $route = 'downtime-erp2.show';
            $id = $this->downtime->id ?? null;
        } elseif ($this->downtimeType === 'downtime_erp') {
            $route = 'downtime_erp.show';
            $id = $this->downtime->id ?? null;
        } else {
            $route = 'downtimes.show';
            $id = $this->downtime->id ?? null;
        }
        
        if ($id) {
            $message->action('View Details', url(route($route, $id)));
        }
        
        $message->line('')
            ->line('This is an automated notification from TPM ERP OEE System.');
        
        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'downtime_id' => $this->downtime->id ?? null,
            'downtime_type' => $this->downtimeType,
            'machine' => $this->getMachineInfo(),
            'problem' => $this->getProblemInfo(),
            'duration' => $this->getDurationInfo(),
            'location' => $this->getLocationInfo(),
            'date' => $this->getDateInfo(),
        ];
    }

    /**
     * Get machine information
     */
    private function getMachineInfo(): string
    {
        if ($this->downtimeType === 'downtime_erp2' || $this->downtimeType === 'downtime_erp') {
            return ($this->downtime->idMachine ?? 'Unknown') . ' - ' . ($this->downtime->typeMachine ?? 'N/A');
        } else {
            // For Downtime model
            return $this->downtime->machine->idMachine ?? 'Unknown';
        }
    }

    /**
     * Get problem information
     */
    private function getProblemInfo(): string
    {
        if ($this->downtimeType === 'downtime_erp2' || $this->downtimeType === 'downtime_erp') {
            return $this->downtime->problemDowntime ?? 'Not specified';
        } else {
            return $this->downtime->problem->name ?? 'Not specified';
        }
    }

    /**
     * Get duration information
     */
    private function getDurationInfo(): string
    {
        if ($this->durationMinutes !== null) {
            $hours = floor($this->durationMinutes / 60);
            $minutes = $this->durationMinutes % 60;
            return $hours > 0 ? "{$hours} hours {$minutes} minutes" : "{$minutes} minutes";
        }
        
        if ($this->downtimeType === 'downtime_erp2' || $this->downtimeType === 'downtime_erp') {
            return $this->downtime->duration ?? 'Unknown';
        } else {
            return ($this->downtime->duration ?? 0) . ' minutes';
        }
    }

    /**
     * Get location information
     */
    private function getLocationInfo(): string
    {
        if ($this->downtimeType === 'downtime_erp2' || $this->downtimeType === 'downtime_erp') {
            $parts = array_filter([
                $this->downtime->plant ?? null,
                $this->downtime->process ?? null,
                $this->downtime->line ?? null,
            ]);
            return implode(' > ', $parts) ?: 'Unknown';
        } else {
            // For Downtime model
            $machine = $this->downtime->machine ?? null;
            if ($machine) {
                $parts = array_filter([
                    $machine->plant->name ?? null,
                    $machine->process->name ?? null,
                    $machine->line->name ?? null,
                ]);
                return implode(' > ', $parts) ?: 'Unknown';
            }
            return 'Unknown';
        }
    }

    /**
     * Get date information
     */
    private function getDateInfo(): string
    {
        $date = $this->downtime->date ?? $this->downtime->created_at ?? now();
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        return $date->format('d M Y H:i');
    }
}

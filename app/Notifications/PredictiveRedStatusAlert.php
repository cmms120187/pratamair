<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PredictiveRedStatusAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected $execution;

    /**
     * Create a new notification instance.
     */
    public function __construct($execution)
    {
        $this->execution = $execution;
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
        $schedule = $this->execution->schedule ?? null;
        $standard = $schedule->standard ?? null;
        $machine = $schedule->machine ?? null;
        
        $measuredValue = $this->execution->measured_value ?? null;
        $measurementStatus = $this->execution->measurement_status ?? 'critical';
        $scheduledDate = $this->execution->scheduled_date ? $this->execution->scheduled_date->format('d M Y') : 'N/A';
        
        $subject = 'Predictive Maintenance Red Status Alert: ' . ($standard->name ?? 'Unknown Standard');
        
        $message = (new MailMessage)
            ->subject($subject)
            ->line('**Predictive Maintenance Red Status Alert**')
            ->line('A predictive maintenance measurement has been recorded with CRITICAL (RED) status.')
            ->line('')
            ->line('**Standard Name:** ' . ($standard->name ?? 'N/A'))
            ->line('**Machine:** ' . ($machine->idMachine ?? 'N/A') . ' - ' . ($machine->typeMachine ?? 'N/A'))
            ->line('**Measured Value:** ' . ($measuredValue ?? 'N/A') . ' ' . ($standard->unit ?? ''))
            ->line('**Status:** CRITICAL (RED)')
            ->line('**Scheduled Date:** ' . $scheduledDate);
        
        if ($standard) {
            $message->line('**Min Value:** ' . ($standard->min_value ?? 'N/A') . ' ' . ($standard->unit ?? ''))
                    ->line('**Max Value:** ' . ($standard->max_value ?? 'N/A') . ' ' . ($standard->unit ?? ''))
                    ->line('**Target Value:** ' . ($standard->target_value ?? 'N/A') . ' ' . ($standard->unit ?? ''));
        }
        
        if ($this->execution->findings) {
            $message->line('**Findings:** ' . $this->execution->findings);
        }
        
        if ($this->execution->actions_taken) {
            $message->line('**Actions Taken:** ' . $this->execution->actions_taken);
        }
        
        $message->action('View Execution Details', url(route('predictive-maintenance.monitoring.show', $this->execution->id)))
            ->line('')
            ->line('⚠️ **IMMEDIATE ACTION REQUIRED**')
            ->line('This measurement indicates a critical condition that requires immediate attention.');
        
        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $schedule = $this->execution->schedule ?? null;
        $standard = $schedule->standard ?? null;
        $machine = $schedule->machine ?? null;
        
        return [
            'execution_id' => $this->execution->id ?? null,
            'schedule_id' => $this->execution->schedule_id ?? null,
            'standard_name' => $standard->name ?? null,
            'machine_id' => $machine->idMachine ?? null,
            'machine_type' => $machine->typeMachine ?? null,
            'measured_value' => $this->execution->measured_value ?? null,
            'measurement_status' => $this->execution->measurement_status ?? 'critical',
            'scheduled_date' => $this->execution->scheduled_date ? $this->execution->scheduled_date->format('Y-m-d') : null,
            'findings' => $this->execution->findings ?? null,
            'actions_taken' => $this->execution->actions_taken ?? null,
        ];
    }
}

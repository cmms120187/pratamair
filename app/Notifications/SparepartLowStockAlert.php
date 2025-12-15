<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SparepartLowStockAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected $part;

    /**
     * Create a new notification instance.
     */
    public function __construct($part)
    {
        $this->part = $part;
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
        $stock = $this->part->stock ?? 0;
        $minimumStock = $this->part->minimum_stock ?? 0;
        $shortage = $minimumStock - $stock;
        
        $subject = 'Sparepart Low Stock Alert: ' . ($this->part->part_number ?? 'Unknown') . ' - ' . ($this->part->name ?? 'N/A');
        
        return (new MailMessage)
            ->subject($subject)
            ->line('**Sparepart Low Stock Alert**')
            ->line('A sparepart stock has fallen below the minimum threshold.')
            ->line('')
            ->line('**Part Number:** ' . ($this->part->part_number ?? 'N/A'))
            ->line('**Part Name:** ' . ($this->part->name ?? 'N/A'))
            ->line('**Current Stock:** ' . $stock . ' ' . ($this->part->unit ?? ''))
            ->line('**Minimum Stock:** ' . $minimumStock . ' ' . ($this->part->unit ?? ''))
            ->line('**Shortage:** ' . $shortage . ' ' . ($this->part->unit ?? ''))
            ->line('**Location:** ' . ($this->part->location ?? 'N/A'))
            ->line('**Category:** ' . ($this->part->category ?? 'N/A'))
            ->action('View Part Details', url(route('part-erp.show', $this->part->id)))
            ->line('')
            ->line('Please replenish the stock immediately to avoid production disruption.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'part_id' => $this->part->id ?? null,
            'part_number' => $this->part->part_number ?? null,
            'part_name' => $this->part->name ?? null,
            'current_stock' => $this->part->stock ?? 0,
            'minimum_stock' => $this->part->minimum_stock ?? 0,
            'shortage' => ($this->part->minimum_stock ?? 0) - ($this->part->stock ?? 0),
            'location' => $this->part->location ?? null,
            'category' => $this->part->category ?? null,
        ];
    }
}

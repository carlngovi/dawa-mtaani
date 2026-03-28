<?php

namespace App\Notifications;

use App\Models\SpotterFollowUp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SpotterFollowUpOverdueNotification extends Notification
{
    use Queueable;

    public function __construct(public SpotterFollowUp $followUp)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $pharmacyName = $this->followUp->submission?->pharmacy ?? 'Unknown';

        return [
            'message' => "Spotter follow-up overdue for {$pharmacyName}. Due date was {$this->followUp->follow_up_date->toDateString()}.",
            'follow_up_id' => $this->followUp->id,
            'submission_id' => $this->followUp->spotter_submission_id,
            'next_step' => $this->followUp->next_step,
            'follow_up_date' => $this->followUp->follow_up_date->toDateString(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pharmacyName = $this->followUp->submission?->pharmacy ?? 'Unknown';
        $dueDate = $this->followUp->follow_up_date->format('d M Y');

        return (new MailMessage)
            ->subject("[Overdue] Spotter Follow-up — {$pharmacyName}")
            ->greeting('Spotter Follow-up Overdue')
            ->line("**Pharmacy:** {$pharmacyName}")
            ->line("**Follow-up Date:** {$dueDate}")
            ->line("**Next Step:** " . str_replace('_', ' ', $this->followUp->next_step ?? 'N/A'))
            ->line("**Rep Notes:** " . ($this->followUp->rep_notes ?? 'None'))
            ->action('View in Admin Panel', url('/admin/spotter/followups'))
            ->line('This follow-up is now overdue. Please take action.');
    }
}

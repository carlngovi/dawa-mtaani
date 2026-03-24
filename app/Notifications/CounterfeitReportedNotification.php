<?php

namespace App\Notifications;

use App\Models\PatientCounterfeitReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CounterfeitReportedNotification extends Notification
{
    use Queueable;

    public function __construct(public PatientCounterfeitReport $report)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => "Counterfeit report received for product {$this->report->product->generic_name} at facility {$this->report->facility->facility_name}. Status: OPEN.",
            'report_id'   => $this->report->id,
            'facility_id' => $this->report->facility_id,
            'product_id'  => $this->report->product_id,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $productName  = $this->report->product->generic_name;
        $facilityName = $this->report->facility->facility_name;

        return (new MailMessage)
            ->subject("[URGENT] Counterfeit Report — {$productName}")
            ->greeting('Counterfeit Report Received')
            ->line("**Facility:** {$facilityName}")
            ->line("**Product:** {$productName}")
            ->line("**Notes:** " . ($this->report->report_notes ?? 'None provided'))
            ->line("**Reported at:** {$this->report->created_at->format('d M Y, H:i')}")
            ->action('Review in Admin Panel', url('/admin/counterfeit-reports/' . $this->report->id))
            ->line('Please investigate this report urgently.');
    }
}

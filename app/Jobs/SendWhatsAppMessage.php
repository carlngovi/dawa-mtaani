<?php

namespace App\Jobs;

use App\Contracts\SmsGatewayInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        private readonly string $phone,
        private readonly string $template,
        private readonly array  $variables = [],
    ) {}

    public function handle(SmsGatewayInterface $smsGateway): void
    {
        // Format the message from template + variables
        $message = $this->renderTemplate($this->template, $this->variables);

        $sent = $smsGateway->sendSms($this->phone, $message);

        if (! $sent) {
            Log::warning('SendWhatsAppMessage: failed to send', [
                'phone'    => $this->phone,
                'template' => $this->template,
            ]);

            throw new \RuntimeException("WhatsApp message failed for template {$this->template}");
        }
    }

    private function renderTemplate(string $template, array $variables): string
    {
        return match ($template) {
            'COPAY_FAILED' => sprintf(
                "Dawa Mtaani: Payment for order %s was not completed. Reason: %s. Tap here to retry: %s",
                $variables['order_ref'] ?? '',
                $variables['failure_reason'] ?? 'Unknown',
                $variables['retry_url'] ?? '',
            ),
            'COPAY_ESCALATED' => sprintf(
                "Dawa Mtaani Admin: Order %s from %s has been escalated for manual co-pay intervention. Escalated at: %s",
                $variables['order_ref'] ?? '',
                $variables['facility_name'] ?? '',
                $variables['escalated_at'] ?? '',
            ),
            default => implode(' ', $variables),
        };
    }
}

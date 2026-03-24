<?php

namespace App\Services\WhatsApp;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OutboundNotificationService
{
    public function send(string $phone, string $category, array $variables = []): bool
    {
        $template = DB::table('whatsapp_templates')
            ->where('category', $category)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            Log::warning('OutboundNotificationService: template not found', [
                'category' => $category,
            ]);
            return false;
        }

        $message = $this->buildMessage($template, $variables);

        // Log outbound message
        try {
            DB::table('whatsapp_messages')->insert([
                'facility_id'           => $variables['facility_id'] ?? null,
                'direction'             => 'OUTBOUND',
                'message_type'          => 'TEXT',
                'content'               => $message,
                'processed_successfully' => true,
                'created_at'            => Carbon::now('UTC'),
            ]);
        } catch (\Throwable $e) {
            Log::warning('OutboundNotificationService: failed to log message', [
                'error' => $e->getMessage(),
            ]);
        }

        // TODO: actual Meta API call implemented when WHATSAPP_ACCESS_TOKEN is configured
        Log::info('OutboundNotificationService: message queued', [
            'phone'    => $phone,
            'category' => $category,
        ]);

        return true;
    }

    private function buildMessage(object $template, array $variables): string
    {
        $templateVars = json_decode($template->variables ?? '[]', true);
        $message = $template->template_name;

        foreach ($templateVars as $index => $varName) {
            $value = $variables[$varName] ?? '';
            $message = str_replace('{{' . ($index + 1) . '}}', $value, $message);
        }

        return $message;
    }

    public function sendOrderConfirmation(string $phone, string $orderRef, float $total, string $paymentType, int $facilityId): bool
    {
        return $this->send($phone, 'ORDER_CONFIRMATION', [
            'order_reference' => $orderRef,
            'total'           => $total,
            'payment_type'    => $paymentType,
            'facility_id'     => $facilityId,
        ]);
    }

    public function sendDeliveryUpdate(string $phone, string $orderRef, string $status, int $facilityId): bool
    {
        return $this->send($phone, 'DELIVERY_UPDATE', [
            'order_reference' => $orderRef,
            'status'          => $status,
            'facility_id'     => $facilityId,
        ]);
    }

    public function sendPaymentReminder(string $phone, float $amountDue, string $dueDate, int $daysOverdue, int $facilityId): bool
    {
        return $this->send($phone, 'PAYMENT_REMINDER', [
            'amount_due'   => $amountDue,
            'due_date'     => $dueDate,
            'days_overdue' => $daysOverdue,
            'facility_id'  => $facilityId,
        ]);
    }

    public function sendCreditAlert(string $phone, string $trancheName, float $balance, float $utilisationPct, int $facilityId): bool
    {
        return $this->send($phone, 'CREDIT_ALERT', [
            'tranche_name'    => $trancheName,
            'balance'         => $balance,
            'utilisation_pct' => $utilisationPct,
            'facility_id'     => $facilityId,
        ]);
    }

    public function sendCopayFailed(string $phone, string $orderRef, string $failureReason, int $facilityId): bool
    {
        return $this->send($phone, 'COPAY_FAILED', [
            'order_reference' => $orderRef,
            'failure_reason'  => $failureReason,
            'facility_id'     => $facilityId,
        ]);
    }

    public function sendWelcome(string $phone, string $facilityName, int $facilityId): bool
    {
        return $this->send($phone, 'WELCOME_ONBOARDED', [
            'facility_name' => $facilityName,
            'facility_id'   => $facilityId,
        ]);
    }
}

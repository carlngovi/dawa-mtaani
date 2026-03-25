<?php

namespace App\Services\WhatsApp;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MessageProcessor
{
    public function __construct(
        private readonly IntentDetector $intentDetector
    ) {}

    public function process(string $phone, string $messageText, string $messageId): array
    {
        $start = microtime(true);
        $now = Carbon::now('UTC');

        // Get or create session
        $session = $this->getOrCreateSession($phone, $now);

        // Check if session expired
        if (Carbon::parse($session->expires_at)->isPast()) {
            $session = $this->resetSession($session->id, $phone, $now);
        }

        // Update last activity
        DB::table('whatsapp_sessions')
            ->where('id', $session->id)
            ->update([
                'last_activity_at' => $now,
                'expires_at'       => $now->copy()->addMinutes(30),
                'updated_at'       => $now,
            ]);

        // Detect intent
        $intent = $this->intentDetector->detect($messageText);

        // Route based on session state and intent
        $response = $this->route($session, $intent, $messageText, $phone, $now);

        // Log message
        $durationMs = (int) ((microtime(true) - $start) * 1000);

        try {
            DB::table('whatsapp_messages')->insert([
                'whatsapp_message_id'   => $messageId,
                'facility_id'           => $session->facility_id,
                'session_id'            => $session->id,
                'direction'             => 'INBOUND',
                'message_type'          => 'TEXT',
                'content'               => $messageText,
                'intent_detected'       => $intent,
                'processed_successfully' => true,
                'processing_time_ms'    => $durationMs,
                'created_at'            => $now,
            ]);
        } catch (\Throwable $e) {
            Log::warning('MessageProcessor: failed to log message', [
                'error' => $e->getMessage(),
            ]);
        }

        return $response;
    }

    private function route(
        object $session,
        string $intent,
        string $messageText,
        string $phone,
        Carbon $now
    ): array {
        // If not authenticated — try to authenticate
        if (! $session->facility_id) {
            return $this->handleAuthentication($session, $phone, $now);
        }

        $state = $session->session_state;

        // Handle cancel from any state
        if ($intent === 'CANCEL') {
            $this->updateSessionState($session->id, 'IDLE', null);
            return ['reply' => 'Order cancelled. Send HELP for commands.'];
        }

        // ORDER_BUILDING state — accumulate items
        if ($state === 'ORDER_BUILDING') {
            if ($intent === 'CONFIRM') {
                return $this->handleOrderConfirm($session, $now);
            }
            return $this->handleOrderLine($session, $messageText, $now);
        }

        // ORDER_CONFIRMING state
        if ($state === 'ORDER_CONFIRMING') {
            if ($intent === 'CONFIRM') {
                return $this->handleOrderSubmit($session, $now);
            }
            return ['reply' => 'Reply YES to confirm your order or CANCEL to abort.'];
        }

        // Route by intent from IDLE
        return match ($intent) {
            'ORDER'     => $this->handleOrderStart($session, $now),
            'STOCK'     => $this->handleStock($session, $messageText),
            'CREDIT'    => $this->handleCredit($session),
            'REPAYMENT' => $this->handleRepayment($session),
            default     => $this->handleHelp(),
        };
    }

    private function handleAuthentication(object $session, string $phone, Carbon $now): array
    {
        // Try to find facility by phone
        $facility = DB::table('facilities')
            ->where('phone', $phone)
            ->where('facility_status', 'ACTIVE')
            ->first();

        if (! $facility) {
            // Try users table
            $user = DB::table('users')->where('phone', $phone)->first();
            if ($user && $user->facility_id) {
                $facility = DB::table('facilities')->where('id', $user->facility_id)->first();
            }
        }

        if (! $facility) {
            return [
                'reply' => 'Your number is not registered on Dawa Mtaani. ' .
                           'Please contact your network administrator to register.',
            ];
        }

        DB::table('whatsapp_sessions')
            ->where('id', $session->id)
            ->update([
                'facility_id'          => $facility->id,
                'session_state'        => 'IDLE',
                'authenticated_at'     => $now,
                'authentication_method' => 'LINKED_PHONE',
                'updated_at'           => $now,
            ]);

        return [
            'reply' => "Welcome, {$facility->facility_name}! Send HELP for available commands.",
        ];
    }

    private function handleOrderStart(object $session, Carbon $now): array
    {
        $this->updateSessionState($session->id, 'ORDER_BUILDING', ['lines' => []]);

        return [
            'reply' => "Order started. Send items in format:\nSKU QUANTITY\n\nExample: AMX500 10\n\nSend DONE when finished or CANCEL to abort.",
        ];
    }

    private function handleOrderLine(object $session, string $messageText, Carbon $now): array
    {
        $line = app(IntentDetector::class)->extractOrderLine($messageText);

        if (! $line) {
            return [
                'reply' => "Format not recognised. Send items as: SKU QUANTITY\nExample: AMX500 10\n\nSend DONE when finished.",
            ];
        }

        // Look up product by SKU
        $product = DB::table('products')
            ->where('sku_code', $line['sku_code'])
            ->where('is_active', true)
            ->first();

        if (! $product) {
            return ['reply' => "SKU {$line['sku_code']} not found in catalogue. Check the SKU and try again."];
        }

        $context = json_decode($session->session_context ?? '{}', true);
        $context['lines'][] = [
            'product_id' => $product->id,
            'sku_code'   => $line['sku_code'],
            'quantity'   => $line['quantity'],
        ];

        DB::table('whatsapp_sessions')
            ->where('id', $session->id)
            ->update([
                'session_context' => json_encode($context),
                'session_state'   => 'ORDER_BUILDING',
                'updated_at'      => $now,
            ]);

        $count = count($context['lines']);
        return ['reply' => "{$line['sku_code']} x{$line['quantity']} added. ({$count} item(s) in order)\n\nContinue adding or send DONE to confirm."];
    }

    private function handleOrderConfirm(object $session, Carbon $now): array
    {
        $context = json_decode($session->session_context ?? '{}', true);
        $lines = $context['lines'] ?? [];

        if (empty($lines)) {
            $this->updateSessionState($session->id, 'IDLE', null);
            return ['reply' => 'No items in order. Send ORDER to start again.'];
        }

        // Build summary
        $summary = "Order summary:\n";
        foreach ($lines as $line) {
            $summary .= "- {$line['sku_code']} x{$line['quantity']}\n";
        }
        $summary .= "\nSend YES to confirm or CANCEL to abort.";

        $this->updateSessionState($session->id, 'ORDER_CONFIRMING', $context);

        return ['reply' => $summary];
    }

    private function handleOrderSubmit(object $session, Carbon $now): array
    {
        $context = json_decode($session->session_context ?? '{}', true);
        $lines = $context['lines'] ?? [];

        if (empty($lines)) {
            $this->updateSessionState($session->id, 'IDLE', null);
            return ['reply' => 'No items to submit.'];
        }

        // Build order lines for OrderPlacementService
        $orderLines = [];
        foreach ($lines as $line) {
            $priceList = DB::table('wholesale_price_lists')
                ->where('product_id', $line['product_id'])
                ->where('is_active', true)
                ->where('stock_status', '!=', 'OUT_OF_STOCK')
                ->first();

            if (! $priceList) {
                continue;
            }

            $orderLines[] = [
                'product_id'    => $line['product_id'],
                'price_list_id' => $priceList->id,
                'quantity'      => $line['quantity'],
                'payment_type'  => 'CASH',
            ];
        }

        if (empty($orderLines)) {
            $this->updateSessionState($session->id, 'IDLE', null);
            return ['reply' => 'None of the items are currently available. Order cancelled.'];
        }

        try {
            $orderService = app(\App\Services\OrderPlacementService::class);
            $result = $orderService->placeOrder(
                facilityId: $session->facility_id,
                placedByUserId: DB::table('users')
                    ->where('facility_id', $session->facility_id)
                    ->value('id'),
                lines: $orderLines,
                orderType: 'CASH',
                sourceChannel: 'WHATSAPP',
            );

            $this->updateSessionState($session->id, 'IDLE', null);

            return [
                'reply' => "Order confirmed!\nRef: {$result['ulid']}\nTotal: {$result['total_amount']}\n\nYou will be notified when dispatched.",
            ];

        } catch (\Throwable $e) {
            $this->updateSessionState($session->id, 'IDLE', null);
            return ['reply' => 'Order could not be placed. Please try again or contact support.'];
        }
    }

    private function handleStock(object $session, string $messageText): array
    {
        $parts = preg_split('/\s+/', trim($messageText), 2);
        $query = $parts[1] ?? '';

        if (empty($query)) {
            return ['reply' => 'Send: STOCK [product name or SKU]\nExample: STOCK AMX500'];
        }

        $products = DB::table('wholesale_price_lists as wpl')
            ->join('products as p', 'wpl.product_id', '=', 'p.id')
            ->where('wpl.is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('p.generic_name', 'like', "%{$query}%")
                  ->orWhere('p.sku_code', 'like', "%{$query}%");
            })
            ->select(['p.generic_name', 'p.sku_code', 'wpl.stock_status', 'wpl.unit_price'])
            ->limit(5)
            ->get();

        if ($products->isEmpty()) {
            return ['reply' => "No products found matching '{$query}'."];
        }

        $reply = "Stock availability:\n";
        foreach ($products as $p) {
            $status = $p->stock_status === 'IN_STOCK' ? '✓ Available' : '✗ ' . $p->stock_status;
            $reply .= "- {$p->sku_code} ({$p->generic_name}): {$status}\n";
        }

        return ['reply' => $reply];
    }

    private function handleCredit(object $session): array
    {
        return [
            'reply' => 'Credit balance query received. Feature active in Phase 2. Contact your network administrator for current balance.',
        ];
    }

    private function handleRepayment(object $session): array
    {
        return [
            'reply' => 'Repayment schedule query received. Feature active in Phase 2. Contact your network administrator for payment details.',
        ];
    }

    private function handleHelp(): array
    {
        return [
            'reply' => "Dawa Mtaani Commands:\n\nORDER — Place an order\nSTOCK [SKU] — Check availability\nCREDIT — View credit balance\nREPAY — View repayment schedule\nHELP — Show this menu\n\nExample: ORDER\nExample: STOCK AMX500",
        ];
    }

    private function getOrCreateSession(string $phone, Carbon $now): object
    {
        $session = DB::table('whatsapp_sessions')
            ->where('whatsapp_phone', $phone)
            ->first();

        if (! $session) {
            $id = DB::table('whatsapp_sessions')->insertGetId([
                'whatsapp_phone'  => $phone,
                'session_state'   => 'IDLE',
                'last_activity_at' => $now,
                'expires_at'      => $now->copy()->addMinutes(30),
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            $session = DB::table('whatsapp_sessions')->where('id', $id)->first();
        }

        return $session;
    }

    private function resetSession(int $sessionId, string $phone, Carbon $now): object
    {
        DB::table('whatsapp_sessions')
            ->where('id', $sessionId)
            ->update([
                'session_state'   => 'IDLE',
                'session_context' => null,
                'facility_id'     => null,
                'authenticated_at' => null,
                'expires_at'      => $now->copy()->addMinutes(30),
                'updated_at'      => $now,
            ]);

        return DB::table('whatsapp_sessions')->where('id', $sessionId)->first();
    }

    private function updateSessionState(int $sessionId, string $state, ?array $context): void
    {
        DB::table('whatsapp_sessions')
            ->where('id', $sessionId)
            ->update([
                'session_state'   => $state,
                'session_context' => $context ? json_encode($context) : null,
                'updated_at'      => Carbon::now('UTC'),
            ]);
    }
}

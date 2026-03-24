<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\WhatsApp\MessageProcessor;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        private readonly MessageProcessor $processor
    ) {}

    public function webhook(Request $request): Response|JsonResponse
    {
        if ($request->isMethod('GET')) {
            $verifyToken = env('WHATSAPP_VERIFY_TOKEN', '');
            $mode        = $request->query('hub_mode');
            $token       = $request->query('hub_verify_token');
            $challenge   = $request->query('hub_challenge');

            if ($mode === 'subscribe' && $token === $verifyToken) {
                return response($challenge, 200);
            }

            return response('Forbidden', 403);
        }

        try {
            $payload  = $request->all();
            $entry    = $payload['entry'][0] ?? null;
            $changes  = $entry['changes'][0] ?? null;
            $value    = $changes['value'] ?? null;
            $messages = $value['messages'] ?? [];

            if (empty($messages)) {
                return response()->json(['status' => 'ok']);
            }

            foreach ($messages as $message) {
                $phone     = $message['from'] ?? '';
                $messageId = $message['id'] ?? '';
                $text      = $message['text']['body'] ?? '';

                if (empty($phone) || empty($text)) continue;

                $response = $this->processor->process($phone, $text, $messageId);
                $this->sendReply($phone, $response['reply'] ?? '');
            }

        } catch (\Throwable $e) {
            Log::error('WhatsAppWebhookController: processing failed', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['status' => 'error logged'], 200);
        }

        return response()->json(['status' => 'ok']);
    }

    public function status(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $now = Carbon::now('UTC');

        return response()->json([
            'active_sessions'   => DB::table('whatsapp_sessions')
                ->where('expires_at', '>', $now)->whereNotNull('facility_id')->count(),
            'messages_last_24h' => DB::table('whatsapp_messages')
                ->where('created_at', '>=', $now->copy()->subHours(24))->count(),
            'inbound_last_24h'  => DB::table('whatsapp_messages')
                ->where('direction', 'INBOUND')
                ->where('created_at', '>=', $now->copy()->subHours(24))->count(),
            'failed_last_24h'   => DB::table('whatsapp_messages')
                ->where('processed_successfully', false)
                ->where('created_at', '>=', $now->copy()->subHours(24))->count(),
            'active_templates'  => DB::table('whatsapp_templates')
                ->where('is_active', true)->count(),
            'generated_at'      => $now->toISOString(),
        ]);
    }

    private function sendReply(string $phone, string $message): void
    {
        $accessToken   = env('WHATSAPP_ACCESS_TOKEN', '');
        $phoneNumberId = env('WHATSAPP_PHONE_NUMBER_ID', '');

        if (empty($accessToken) || empty($phoneNumberId)) {
            Log::info('WhatsAppWebhookController: credentials not configured — reply skipped', [
                'phone' => $phone,
            ]);
            return;
        }

        try {
            $client = new \GuzzleHttp\Client();
            $client->post("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'messaging_product' => 'whatsapp',
                    'to'                => $phone,
                    'type'              => 'text',
                    'text'              => ['body' => $message],
                ],
            ]);

            DB::table('whatsapp_messages')->insert([
                'direction'              => 'OUTBOUND',
                'message_type'           => 'TEXT',
                'content'                => $message,
                'processed_successfully' => true,
                'created_at'             => Carbon::now('UTC'),
            ]);

        } catch (\Throwable $e) {
            Log::error('WhatsAppWebhookController: failed to send reply', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

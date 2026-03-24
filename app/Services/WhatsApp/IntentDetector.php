<?php

namespace App\Services\WhatsApp;

class IntentDetector
{
    private array $patterns = [
        'ORDER'     => '/^(order|ord|agiza)/i',
        'STOCK'     => '/^(stock|availability|available|ipo|stok)/i',
        'CREDIT'    => '/^(credit|bal|balance|mkopo)/i',
        'REPAYMENT' => '/^(repay|payment|malipo|lipa)/i',
        'CONFIRM'   => '/^(confirm|done|yes|ndio|thibitisha)/i',
        'CANCEL'    => '/^(cancel|stop|acha|ondoa)/i',
        'HELP'      => '/^(help|menu|commands|msaada|\?)/i',
    ];

    public function detect(string $message): string
    {
        $normalised = trim(strtolower($message));

        foreach ($this->patterns as $intent => $pattern) {
            if (preg_match($pattern, $normalised)) {
                return $intent;
            }
        }

        return 'HELP'; // fallback
    }

    public function extractOrderLine(string $message): ?array
    {
        // Format: SKU QTY or SKU QTY PRICE
        // e.g. "AMX500 10" or "AMX500 10 150"
        $parts = preg_split('/\s+/', trim($message));

        if (count($parts) < 2) {
            return null;
        }

        $sku = strtoupper($parts[0]);
        $qty = (int) ($parts[1] ?? 0);
        $price = isset($parts[2]) ? (float) $parts[2] : null;

        if (empty($sku) || $qty <= 0) {
            return null;
        }

        return [
            'sku_code'  => $sku,
            'quantity'  => $qty,
            'price'     => $price,
        ];
    }
}

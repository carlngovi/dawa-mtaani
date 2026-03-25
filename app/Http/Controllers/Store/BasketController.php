<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\PatientBasket;
use App\Models\PatientBasketLine;
use App\Services\CurrencyConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BasketController extends Controller
{
    public function addItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_phone' => 'required|string|max:20',
            'facility_id'   => 'required|exists:facilities,id',
            'product_id'    => 'required|exists:products,id',
            'quantity'       => 'required|integer|min:1',
        ]);

        $basket = PatientBasket::firstOrCreate(
            [
                'patient_phone' => $validated['patient_phone'],
                'facility_id'   => $validated['facility_id'],
            ],
            ['session_token' => Str::random(60)]
        );

        PatientBasketLine::updateOrCreate(
            [
                'basket_id'  => $basket->id,
                'product_id' => $validated['product_id'],
            ],
            [
                'quantity' => $validated['quantity'],
                'added_at' => now(),
            ]
        );

        return response()->json([
            'status'       => 'success',
            'data'         => ['basket_token' => $basket->session_token],
            'message'      => '',
        ]);
    }

    public function removeItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_token' => 'required|string',
            'product_id'    => 'required|integer',
        ]);

        $basket = PatientBasket::where('session_token', $validated['session_token'])->first();

        if (! $basket) {
            return response()->json([
                'status'  => 'error',
                'data'    => [],
                'message' => 'Basket not found.',
            ], 404);
        }

        PatientBasketLine::where('basket_id', $basket->id)
            ->where('product_id', $validated['product_id'])
            ->delete();

        if ($basket->lines()->count() === 0) {
            $basket->delete();
        }

        return response()->json([
            'status'  => 'success',
            'data'    => [],
            'message' => '',
        ]);
    }

    public function getBasket(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_token' => 'required|string',
        ]);

        $basket = PatientBasket::where('session_token', $validated['session_token'])->first();

        if (! $basket) {
            return response()->json([
                'status'  => 'error',
                'data'    => [],
                'message' => 'Basket not found.',
            ], 404);
        }

        $lines = DB::table('patient_basket_lines as pbl')
            ->join('products as p', 'p.id', '=', 'pbl.product_id')
            ->join('wholesale_price_lists as wpl', function ($join) use ($basket) {
                $join->on('wpl.product_id', '=', 'p.id')
                    ->where('wpl.wholesale_facility_id', $basket->facility_id)
                    ->where('wpl.is_active', true);
            })
            ->where('pbl.basket_id', $basket->id)
            ->select([
                'p.ulid as product_ulid',
                'p.generic_name',
                'p.brand_name',
                'p.unit_size',
                'pbl.quantity',
                'wpl.unit_price',
            ])
            ->get()
            ->map(function ($row) {
                return [
                    'product_ulid' => $row->product_ulid,
                    'generic_name' => $row->generic_name,
                    'brand_name'   => $row->brand_name,
                    'unit_size'    => $row->unit_size,
                    'quantity'     => $row->quantity,
                    'unit_price'   => CurrencyConfig::format((float) $row->unit_price),
                    'line_total'   => CurrencyConfig::format((float) $row->unit_price * $row->quantity),
                ];
            });

        $subtotal = $lines->sum(fn ($l) => (float) filter_var($l['line_total'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));

        // Recalculate subtotal from raw values for accuracy
        $rawSubtotal = DB::table('patient_basket_lines as pbl')
            ->join('wholesale_price_lists as wpl', function ($join) use ($basket) {
                $join->on('wpl.product_id', '=', 'pbl.product_id')
                    ->where('wpl.wholesale_facility_id', $basket->facility_id)
                    ->where('wpl.is_active', true);
            })
            ->where('pbl.basket_id', $basket->id)
            ->selectRaw('SUM(wpl.unit_price * pbl.quantity) as subtotal')
            ->value('subtotal') ?? 0;

        return response()->json([
            'status'  => 'success',
            'data'    => [
                'session_token'  => $basket->session_token,
                'reserved_until' => $basket->reserved_until,
                'lines'          => $lines->all(),
                'subtotal'       => CurrencyConfig::format((float) $rawSubtotal),
            ],
            'message' => '',
        ]);
    }

    public function reserve(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_token' => 'required|string',
        ]);

        $basket = PatientBasket::where('session_token', $validated['session_token'])->first();

        if (! $basket) {
            return response()->json([
                'status'  => 'error',
                'data'    => [],
                'message' => 'Basket not found.',
            ], 404);
        }

        $basket->load('lines.product');
        $unavailableItems = [];

        DB::transaction(function () use ($basket, &$unavailableItems) {
            foreach ($basket->lines as $line) {
                $stock = DB::table('facility_stock_status')
                    ->where('wholesale_facility_id', $basket->facility_id)
                    ->where('product_id', $line->product_id)
                    ->lockForUpdate()
                    ->first();

                $availableQty = $stock->stock_quantity ?? 0;

                if ($availableQty < $line->quantity) {
                    $unavailableItems[] = [
                        'product_name'  => $line->product->generic_name,
                        'requested_qty' => $line->quantity,
                        'available_qty' => $availableQty,
                    ];
                }
            }
        });

        $reservationMinutes = (int) (DB::table('system_settings')
            ->where('key', 'reservation_minutes')
            ->value('value') ?: 15);

        $partialFulfilment = DB::table('system_settings')
            ->where('key', 'partial_fulfilment')
            ->value('value') ?? 'true';

        $basket->update(['reserved_until' => now()->addMinutes($reservationMinutes)]);

        if ($partialFulfilment !== 'true' && ! empty($unavailableItems)) {
            return response()->json([
                'status'  => 'error',
                'data'    => ['unavailable_items' => $unavailableItems],
                'message' => 'Some items are unavailable.',
            ], 422);
        }

        return response()->json([
            'status'  => 'success',
            'data'    => [
                'reserved_until'    => $basket->fresh()->reserved_until->toIso8601String(),
                'unavailable_items' => $unavailableItems,
            ],
            'message' => '',
        ]);
    }
}

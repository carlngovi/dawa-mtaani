{{-- Order Status Tracker --}}
@props(['status' => 'PAYMENT_PENDING', 'orderUlid' => ''])

@php
    $steps = [
        'PAYMENT_PENDING' => 'Payment Pending',
        'CONFIRMED'       => 'Confirmed',
        'PREPARING'       => 'Preparing',
        'READY'           => 'Ready',
    ];
    $stepKeys  = array_keys($steps);
    $currentIdx = array_search($status, $stepKeys);
    if ($currentIdx === false) {
        $currentIdx = -1;
    }
@endphp

<div class="rounded-xl border bg-gray-800 p-6 shadow-sm">
    <h3 class="text-lg font-semibold text-white mb-6">Order Status</h3>

    {{-- Progress bar --}}
    <div class="flex items-center justify-between">
        @foreach ($steps as $key => $label)
            @php
                $idx     = array_search($key, $stepKeys);
                $isActive  = $idx <= $currentIdx;
                $isCurrent = $idx === $currentIdx;
            @endphp

            <div class="flex flex-col items-center flex-1">
                {{-- Circle --}}
                <div @class([
                    'flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold transition',
                    'bg-yellow-400 text-gray-900' => $isActive,
                    'bg-gray-200 text-gray-400' => ! $isActive,
                    'ring-4 ring-yellow-200' => $isCurrent,
                ])>
                    {{ $idx + 1 }}
                </div>
                {{-- Label --}}
                <span @class([
                    'mt-2 text-xs font-medium text-center',
                    'text-yellow-600' => $isActive,
                    'text-gray-400'   => ! $isActive,
                ])>{{ $label }}</span>
            </div>

            @if (! $loop->last)
                <div @class([
                    'h-1 flex-1 -mt-6 mx-1 rounded',
                    'bg-yellow-400' => $idx < $currentIdx,
                    'bg-gray-200'   => $idx >= $currentIdx,
                ])></div>
            @endif
        @endforeach
    </div>

    {{-- QR Code when READY --}}
    @if ($status === 'READY')
        <div class="mt-8 flex flex-col items-center gap-3 rounded-lg border-2 border-dashed border-green-300 bg-green-900/20 p-6">
            <p class="text-sm font-medium text-green-300">Show this QR code at the pharmacy to collect your order</p>
            <div class="rounded-lg bg-gray-800 p-3 shadow">
                {!! QrCode::size(180)->generate(route('api.store.order.status', $orderUlid)) !!}
            </div>
            <p class="text-xs text-gray-400">{{ $orderUlid }}</p>
        </div>
    @endif
</div>

{{-- Supply Chain Badge — BINARY: show full badge or nothing --}}
@props(['verified' => false, 'chain' => null, 'facilityPpbLicence' => null, 'facilityPpbStatus' => null])

@if ($verified)
<div x-data="{ expanded: false }" class="inline-block">
    {{-- Badge --}}
    <button @click="expanded = !expanded"
            class="inline-flex items-center gap-1.5 rounded-full bg-green-900/20 px-3 py-1.5 text-sm font-medium text-green-400 ring-1 ring-green-200 hover:bg-green-900/30 transition">
        <svg class="h-4 w-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 1l2.928 1.382 3.09.476.952 3.008L18.856 8.5l-.954 3.01-.95 3.007-3.09.478L10 16.377l-2.862-1.382-3.09-.478-.95-3.008L2.144 8.5l.954-3.009.95-3.008 3.09-.476L10 1z" clip-rule="evenodd"/>
            <path fill="#fff" d="M13.707 7.293a1 1 0 00-1.414 0L9 10.586 7.707 9.293a1 1 0 10-1.414 1.414l2 2a1 1 0 001.414 0l4-4a1 1 0 000-1.414z"/>
        </svg>
        Verified Supply Chain
        <svg :class="expanded ? 'rotate-180' : ''" class="h-4 w-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Expanded timeline panel --}}
    <div x-show="expanded" x-collapse class="mt-3 rounded-xl border border-gray-700 bg-gray-800 p-4 shadow-sm">
        <ol class="relative border-l-2 border-gray-700 ml-3 space-y-6">
            @foreach ($chain as $event)
                <li class="ml-6">
                    {{-- Green checkmark --}}
                    <span class="absolute -left-3 flex h-6 w-6 items-center justify-center rounded-full bg-green-500 ring-4 ring-white">
                        <svg class="h-3.5 w-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L7 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>

                    <h4 class="text-sm font-semibold text-white">{{ $event['label'] }}</h4>

                    @if (! empty($event['actor']))
                        <p class="text-sm text-gray-400">{{ $event['actor'] }}</p>
                    @endif

                    @if (! empty($event['licence']))
                        <p class="text-xs text-gray-400">PPB: {{ $event['licence'] }}</p>
                    @endif

                    @if (! empty($event['order_reference']))
                        <p class="text-xs text-gray-400">Order: {{ $event['order_reference'] }}</p>
                    @endif

                    @if (! empty($event['timestamp']))
                        <p class="text-xs text-gray-400">
                            {{ \Carbon\Carbon::parse($event['timestamp'])->setTimezone('Africa/Nairobi')->format('d M Y, H:i') }}
                        </p>
                    @endif
                </li>
            @endforeach
        </ol>

        {{-- PPB licence info --}}
        @if ($facilityPpbLicence)
            <div class="mt-4 flex items-center gap-2 rounded-lg bg-gray-900/50 px-3 py-2 text-sm">
                <span class="text-gray-400">PPB Licence:</span>
                <span class="font-medium">{{ $facilityPpbLicence }}</span>
                <span @class([
                    'ml-auto rounded-full px-2 py-0.5 text-xs font-semibold',
                    'bg-green-900/30 text-green-400' => $facilityPpbStatus === 'VALID',
                    'bg-red-900/30 text-red-400'     => in_array($facilityPpbStatus, ['EXPIRED', 'SUSPENDED']),
                ])>{{ $facilityPpbStatus }}</span>
            </div>
        @endif
    </div>
</div>
@endif

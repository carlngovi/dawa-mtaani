@extends('layouts.app')
@section('title', $facility->facility_name . ' — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Back + header --}}
    <div>
        <a href="/rep/pharmacies"
           class="text-xs text-green-400 hover:underline">← Back to Pharmacy List</a>
        <div class="flex items-center gap-3 mt-3">
            <h1 class="text-2xl font-bold text-white">{{ $facility->facility_name }}</h1>
            @php
                $badge = match($facility->facility_status) {
                    'ACTIVE'         => 'bg-green-900/30 text-green-400',
                    'APPLIED'        => 'bg-gray-700 text-gray-400',
                    'PPB_VERIFIED'   => 'bg-blue-900/30 text-blue-400',
                    'ACCOUNT_LINKED' => 'bg-amber-900/30 text-amber-400',
                    'SUSPENDED'      => 'bg-red-900/30 text-red-400',
                    'PAUSED'         => 'bg-orange-900/30 text-orange-400',
                    'CHURNED'        => 'bg-gray-700 text-gray-400',
                    default          => 'bg-gray-700 text-gray-400',
                };
            @endphp
            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                {{ str_replace('_', ' ', $facility->facility_status) }}
            </span>
            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-700 text-gray-400">
                Read Only
            </span>
        </div>
    </div>

    {{-- Info grid --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700">
            <h3 class="text-sm font-semibold text-gray-300">Facility Details</h3>
        </div>
        <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-0 divide-y sm:divide-y-0 sm:divide-x divide-gray-700">
            @php
                $fields = [
                    'PPB Licence'      => $facility->ppb_licence_number,
                    'Facility Type'    => $facility->facility_type ?? '—',
                    'County'           => $facility->county,
                    'Sub-County'       => $facility->sub_county ?? '—',
                    'Ward'             => $facility->ward ?? '—',
                    'Network'          => $facility->network_membership === 'NETWORK' ? 'Network Member' : 'Off-Network',
                    'Status'           => str_replace('_', ' ', $facility->facility_status),
                    'Registered'       => \Carbon\Carbon::parse($facility->created_at)->timezone('Africa/Nairobi')->format('d M Y'),
                ];
            @endphp
            @foreach($fields as $label => $value)
            <div class="px-5 py-4">
                <dt class="text-xs text-gray-400 uppercase tracking-wider">{{ $label }}</dt>
                <dd class="mt-1 text-sm font-medium text-gray-200">{{ $value }}</dd>
            </div>
            @endforeach
        </dl>
    </div>

    {{-- Note --}}
    <p class="text-xs text-gray-400">
        Detailed operational data is available to Network Admin.
        This view shows activation status only — no financial, credit, or order data.
    </p>

</div>
@endsection

@extends('layouts.admin')
@section('title', $facility->facility_name . ' — Registration Review')
@section('content')
<div class="space-y-6" x-data="{ showRejectModal: false }">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-lg">
        {{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <a href="/admin/registrations" class="text-xs text-green-700 hover:underline mb-2 inline-block">← Back to Registrations</a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $facility->facility_name }}</h1>
            <div class="flex items-center gap-2 mt-2">
                @php
                    $stageBadge = match($facility->onboarding_status) {
                        'APPLIED'        => 'bg-gray-100 text-gray-600',
                        'PPB_VERIFIED'   => 'bg-blue-100 text-blue-700',
                        'ACCOUNT_LINKED' => 'bg-amber-100 text-amber-700',
                        'ACTIVE'         => 'bg-green-100 text-green-700',
                        default          => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $stageBadge }}">
                    {{ str_replace('_', ' ', $facility->onboarding_status) }}
                </span>
                <span class="text-xs text-gray-400">
                    Submitted {{ $facility->created_at->timezone('Africa/Nairobi')->format('d M Y') }}
                    ({{ $facility->created_at->diffForHumans() }})
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left column: details --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Facility Details --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Facility Details</h3>
                </div>
                <dl class="divide-y divide-gray-100">
                    <div class="px-5 py-3 flex justify-between">
                        <dt class="text-xs text-gray-500">Owner Name</dt>
                        <dd class="text-sm text-gray-800">{{ $facility->owner_name ?? '—' }}</dd>
                    </div>
                    @if($owner)
                    <div class="px-5 py-3 flex justify-between">
                        <dt class="text-xs text-gray-500">Owner Email</dt>
                        <dd class="text-sm text-gray-800">{{ $owner->email ?? '—' }}</dd>
                    </div>
                    @endif
                    <div class="px-5 py-3 flex justify-between">
                        <dt class="text-xs text-gray-500">Phone</dt>
                        <dd class="text-sm text-gray-800">{{ $facility->phone ?? '—' }}</dd>
                    </div>
                    <div class="px-5 py-3 flex justify-between">
                        <dt class="text-xs text-gray-500">PPB Licence Number</dt>
                        <dd class="text-sm font-mono text-gray-800">{{ $facility->ppb_licence_number ?? '—' }}</dd>
                    </div>
                    <div class="px-5 py-3 flex justify-between">
                        <dt class="text-xs text-gray-500">PPB Facility Type</dt>
                        <dd class="text-sm text-gray-800">{{ $facility->ppb_facility_type ?? '—' }}</dd>
                    </div>
                    <div class="px-5 py-3 flex justify-between">
                        <dt class="text-xs text-gray-500">County</dt>
                        <dd class="text-sm text-gray-800">{{ $facility->county ?? '—' }}</dd>
                    </div>
                    <div class="px-5 py-3 flex justify-between">
                        <dt class="text-xs text-gray-500">Sub-County / Ward</dt>
                        <dd class="text-sm text-gray-800">{{ $facility->sub_county ?? '—' }} / {{ $facility->ward ?? '—' }}</dd>
                    </div>
                    <div class="px-5 py-3 flex justify-between">
                        <dt class="text-xs text-gray-500">Physical Address</dt>
                        <dd class="text-sm text-gray-800">{{ $facility->physical_address ?? '—' }}</dd>
                    </div>
                    <div class="px-5 py-3 flex justify-between">
                        <dt class="text-xs text-gray-500">Network Membership</dt>
                        <dd>
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                {{ $facility->network_membership === 'NETWORK' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $facility->network_membership ?? '—' }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- PPB Verification --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700">PPB Verification</h3>
                    @php
                        $ppbBadge = match($facility->ppb_licence_status) {
                            'VALID'     => 'bg-green-100 text-green-700',
                            'EXPIRED'   => 'bg-red-100 text-red-700',
                            'SUSPENDED' => 'bg-orange-100 text-orange-700',
                            default     => 'bg-gray-100 text-gray-500',
                        };
                    @endphp
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $ppbBadge }}">
                        {{ $facility->ppb_licence_status ?? 'PENDING' }}
                    </span>
                </div>
                <div class="px-5 py-4 space-y-3">
                    @if($facility->ppb_verified_at)
                    <p class="text-sm text-gray-600">
                        Last verified: <strong>{{ $facility->ppb_verified_at->timezone('Africa/Nairobi')->format('d M Y, H:i') }}</strong>
                    </p>
                    @else
                    <p class="text-sm text-amber-600">PPB verification has not been completed yet.</p>
                    @endif

                    @if($canWrite)
                    <form method="POST" action="/admin/registrations/{{ $facility->ulid }}/verify-ppb">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1.5 border border-blue-200 text-blue-700 rounded-lg text-xs hover:bg-blue-50 transition-colors">
                            Re-run PPB Check
                        </button>
                    </form>
                    @endif

                    @if($ppbLogs->isNotEmpty())
                    <div class="mt-4">
                        <h4 class="text-xs font-medium text-gray-500 mb-2">Verification History</h4>
                        <div class="space-y-2">
                            @foreach($ppbLogs as $log)
                            <div class="text-xs border border-gray-100 rounded-lg p-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-500">{{ \Carbon\Carbon::parse($log->checked_at)->timezone('Africa/Nairobi')->format('d M Y, H:i') }}</span>
                                    <span class="font-medium {{ $log->licence_status_returned === 'VALID' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $log->licence_status_returned }}
                                    </span>
                                </div>
                                <p class="text-gray-400 mt-1">Triggered by: {{ $log->triggered_by }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($facility->ppb_raw_response)
                    <details class="mt-3">
                        <summary class="text-xs text-gray-400 cursor-pointer hover:text-gray-600">Raw PPB Response</summary>
                        <pre class="mt-2 text-xs bg-gray-50 p-3 rounded-lg overflow-x-auto text-gray-600">{{ json_encode($facility->ppb_raw_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </details>
                    @endif
                </div>
            </div>

            {{-- Account Linking --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Account Linking</h3>
                </div>
                <div class="px-5 py-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-500">Banking Account Number</span>
                        <span class="text-sm text-gray-800">{{ $facility->banking_account_number ?? 'Not provided' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-500">Account Validated</span>
                        @if($facility->banking_account_validated_at)
                            <span class="text-sm text-green-700 font-medium">
                                {{ $facility->banking_account_validated_at->timezone('Africa/Nairobi')->format('d M Y, H:i') }}
                            </span>
                        @else
                            <span class="text-sm text-amber-600">Pending I&M account setup</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Audit Trail --}}
            @if($auditLogs->isNotEmpty())
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">Audit Trail</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3 text-left">Date</th>
                                <th class="px-5 py-3 text-left">Action</th>
                                <th class="px-5 py-3 text-left">Actor</th>
                                <th class="px-5 py-3 text-left hidden md:table-cell">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($auditLogs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($log->created_at)->timezone('Africa/Nairobi')->format('d M Y, H:i') }}
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-800 font-medium">
                                    {{ str_replace('_', ' ', $log->action) }}
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-600">
                                    {{ $log->actor_name ?? 'System' }}
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-400 font-mono hidden md:table-cell">
                                    {{ $log->ip_address }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>

        {{-- Right column: Action panel --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-gray-200 p-5 sticky top-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-700">Actions</h3>

                @if(! $canWrite)
                <div class="bg-blue-50 border border-blue-200 text-blue-800 text-xs px-3 py-2 rounded-lg">
                    Read-only access. Only Tier 3+ can approve or reject.
                </div>
                @endif

                {{-- APPLIED stage --}}
                @if($facility->onboarding_status === 'APPLIED')
                <div class="bg-gray-50 border border-gray-200 text-gray-600 text-xs px-3 py-3 rounded-lg">
                    Waiting for PPB verification before approval is available.
                </div>
                @if($canWrite)
                <form method="POST" action="/admin/registrations/{{ $facility->ulid }}/verify-ppb">
                    @csrf
                    <button type="submit"
                            class="w-full px-4 py-2.5 border border-blue-200 text-blue-700 rounded-lg text-sm hover:bg-blue-50 transition-colors">
                        Re-run PPB Check
                    </button>
                </form>
                @endif
                @endif

                {{-- PPB_VERIFIED stage --}}
                @if($facility->onboarding_status === 'PPB_VERIFIED' && $canWrite)
                <form method="POST" action="/admin/registrations/{{ $facility->ulid }}/approve">
                    @csrf
                    <button type="submit"
                            class="w-full px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
                        Approve — Move to Account Linked
                    </button>
                </form>
                <button @click="showRejectModal = true"
                        class="w-full px-4 py-2.5 border border-red-200 text-red-600 rounded-lg text-sm hover:bg-red-50 transition-colors">
                    Reject Registration
                </button>
                @endif

                {{-- ACCOUNT_LINKED stage --}}
                @if($facility->onboarding_status === 'ACCOUNT_LINKED' && $canWrite)
                <form method="POST" action="/admin/registrations/{{ $facility->ulid }}/activate">
                    @csrf
                    <button type="submit"
                            class="w-full px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
                        Activate Facility
                    </button>
                </form>
                @if(! $facility->banking_account_validated_at)
                <p class="text-xs text-amber-600">I&M banking account not yet validated — activation will be blocked.</p>
                @endif
                @if($facility->ppb_licence_status !== 'VALID')
                <p class="text-xs text-red-600">PPB licence is {{ $facility->ppb_licence_status ?? 'unknown' }} — activation will be blocked.</p>
                @endif
                <button @click="showRejectModal = true"
                        class="w-full px-4 py-2.5 border border-red-200 text-red-600 rounded-lg text-sm hover:bg-red-50 transition-colors">
                    Reject Registration
                </button>
                @endif

                {{-- ACTIVE --}}
                @if($facility->onboarding_status === 'ACTIVE')
                <div class="bg-green-50 border border-green-200 text-green-800 text-xs px-3 py-3 rounded-lg">
                    This facility is already ACTIVE. No further actions required.
                </div>
                @endif

                {{-- Quick info --}}
                <div class="border-t border-gray-100 pt-4 space-y-2 text-xs text-gray-500">
                    <p>Onboarding: <strong class="text-gray-700">{{ $facility->onboarding_status }}</strong></p>
                    <p>Facility Status: <strong class="text-gray-700">{{ $facility->facility_status ?? 'N/A' }}</strong></p>
                    <p>PPB Licence: <strong class="text-gray-700">{{ $facility->ppb_licence_status ?? 'PENDING' }}</strong></p>
                </div>
            </div>
        </div>

    </div>

    {{-- Rejection Modal --}}
    <div x-show="showRejectModal"
         class="fixed inset-0 bg-gray-900/50 z-50 flex items-center justify-center"
         x-cloak>
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" @click.outside="showRejectModal = false">
            <h3 class="text-base font-semibold text-gray-900">Reject Registration</h3>
            <p class="text-sm text-gray-500 mt-1">
                This will mark <strong>{{ $facility->facility_name }}</strong> as rejected.
                An SMS will be sent to the owner.
            </p>
            <form method="POST" action="/admin/registrations/{{ $facility->ulid }}/reject" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Rejection Reason (min 20 characters)</label>
                    <textarea name="rejection_reason" rows="4" required minlength="20" maxlength="1000"
                              x-data="{ count: 0 }"
                              x-on:input="count = $event.target.value.length"
                              placeholder="Provide a detailed reason for rejecting this registration..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                    <p class="text-xs text-gray-400 mt-1" x-data="{ count: 0 }">
                        <span x-text="count"></span>/1000 characters
                    </p>
                </div>
                @error('rejection_reason')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
                <div class="flex gap-3">
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition-colors">
                        Confirm Rejection
                    </button>
                    <button type="button" @click="showRejectModal = false"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
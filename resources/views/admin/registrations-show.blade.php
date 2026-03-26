@extends('layouts.admin')
@section('title', $facility->facility_name . ' — Registration Review')
@section('content')
<div class="space-y-6" x-data="{ showRejectModal: false }">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="bg-green-900/20 border border-green-800 text-green-300 text-sm px-4 py-3 rounded-lg">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-900/20 border border-red-800 text-red-300 text-sm px-4 py-3 rounded-lg">
        {{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <a href="/admin/registrations" class="text-xs text-green-400 hover:underline mb-2 inline-block">← Back to Registrations</a>
            <h1 class="text-2xl font-bold text-white">{{ $facility->facility_name }}</h1>
            <div class="flex items-center gap-2 mt-2">
                @php
                    $stageBadge = match($facility->onboarding_status) {
                        'APPLIED'        => 'bg-gray-700 text-gray-400',
                        'PPB_VERIFIED'   => 'bg-blue-900/30 text-blue-400 border border-blue-800',
                        'ACCOUNT_LINKED' => 'bg-amber-900/30 text-amber-400 border border-amber-800',
                        'ACTIVE'         => 'bg-green-900/30 text-green-400 border border-green-800',
                        default          => 'bg-gray-700 text-gray-400',
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
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-300">Facility Details</h3>
                </div>
                <dl class="divide-y divide-gray-700">
                    <div class="px-5 py-3 flex justify-between">
                        <dt class="text-xs text-gray-400">Owner Name</dt>
                        <dd class="text-sm text-gray-200">{{ $facility->owner_name ?? '—' }}</dd>
                    </div>
                    @if($owner)
                    <div class="px-5 py-3 flex justify-between">
                        <dt class="text-xs text-gray-400">Owner Email</dt>
                        <dd class="text-sm text-gray-200">{{ $owner->email ?? '—' }}</dd>
                    </div>
                    @endif
                    <div class="px-5 py-3 flex justify-between">
                        <dt class="text-xs text-gray-400">Phone</dt>
                        <dd class="text-sm text-gray-200">{{ $facility->phone ?? '—' }}</dd>
                    </div>
                    <div class="px-5 py-3 flex justify-between">
                        <dt class="text-xs text-gray-400">PPB Licence Number</dt>
                        <dd class="text-sm font-mono text-gray-200">{{ $facility->ppb_licence_number ?? '—' }}</dd>
                    </div>
                    <div class="px-5 py-3 flex justify-between">
                        <dt class="text-xs text-gray-400">PPB Facility Type</dt>
                        <dd class="text-sm text-gray-200">{{ $facility->ppb_facility_type ?? '—' }}</dd>
                    </div>
                    <div class="px-5 py-3 flex justify-between">
                        <dt class="text-xs text-gray-400">County</dt>
                        <dd class="text-sm text-gray-200">{{ $facility->county ?? '—' }}</dd>
                    </div>
                    <div class="px-5 py-3 flex justify-between">
                        <dt class="text-xs text-gray-400">Sub-County / Ward</dt>
                        <dd class="text-sm text-gray-200">{{ $facility->sub_county ?? '—' }} / {{ $facility->ward ?? '—' }}</dd>
                    </div>
                    <div class="px-5 py-3 flex justify-between">
                        <dt class="text-xs text-gray-400">Physical Address</dt>
                        <dd class="text-sm text-gray-200">{{ $facility->physical_address ?? '—' }}</dd>
                    </div>
                    <div class="px-5 py-3 flex justify-between">
                        <dt class="text-xs text-gray-400">Network Membership</dt>
                        <dd>
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                {{ $facility->network_membership === 'NETWORK' ? 'bg-green-900/30 text-green-400 border border-green-800' : 'bg-gray-700 text-gray-400' }}">
                                {{ $facility->network_membership ?? '—' }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- PPB Verification --}}
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-700 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-300">PPB Verification</h3>
                    @php
                        $ppbBadge = match($facility->ppb_licence_status) {
                            'VALID'     => 'bg-green-900/30 text-green-400 border border-green-800',
                            'EXPIRED'   => 'bg-red-900/30 text-red-400 border border-red-800',
                            'SUSPENDED' => 'bg-orange-900/30 text-orange-400 border border-orange-800',
                            default     => 'bg-gray-700 text-gray-400',
                        };
                    @endphp
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $ppbBadge }}">
                        {{ $facility->ppb_licence_status ?? 'PENDING' }}
                    </span>
                </div>
                <div class="px-5 py-4 space-y-3">
                    @if($facility->ppb_verified_at)
                    <p class="text-sm text-gray-400">
                        Last verified: <strong>{{ $facility->ppb_verified_at->timezone('Africa/Nairobi')->format('d M Y, H:i') }}</strong>
                    </p>
                    @else
                    <p class="text-sm text-amber-400">PPB verification has not been completed yet.</p>
                    @endif

                    @if($canWrite)
                    <form method="POST" action="/admin/registrations/{{ $facility->ulid }}/verify-ppb">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1.5 border border-blue-800 text-blue-400 rounded-lg text-xs hover:bg-blue-900/20 transition-colors">
                            Re-run PPB Check
                        </button>
                    </form>
                    @endif

                    @if($ppbLogs->isNotEmpty())
                    <div class="mt-4">
                        <h4 class="text-xs font-medium text-gray-400 mb-2">Verification History</h4>
                        <div class="space-y-2">
                            @foreach($ppbLogs as $log)
                            <div class="text-xs border border-gray-700 rounded-lg p-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-400">{{ \Carbon\Carbon::parse($log->checked_at)->timezone('Africa/Nairobi')->format('d M Y, H:i') }}</span>
                                    <span class="font-medium {{ $log->licence_status_returned === 'VALID' ? 'text-green-400' : 'text-red-400' }}">
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
                        <summary class="text-xs text-gray-400 cursor-pointer hover:text-gray-400">Raw PPB Response</summary>
                        <pre class="mt-2 text-xs bg-gray-900/50 p-3 rounded-lg overflow-x-auto text-gray-400">{{ json_encode($facility->ppb_raw_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </details>
                    @endif
                </div>
            </div>

            {{-- Account Linking --}}
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-300">Account Linking</h3>
                </div>
                <div class="px-5 py-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-400">Banking Account Number</span>
                        <span class="text-sm text-gray-200">{{ $facility->banking_account_number ?? 'Not provided' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-xs text-gray-400">Account Validated</span>
                        @if($facility->banking_account_validated_at)
                            <span class="text-sm text-green-400 font-medium">
                                {{ $facility->banking_account_validated_at->timezone('Africa/Nairobi')->format('d M Y, H:i') }}
                            </span>
                        @else
                            <span class="text-sm text-amber-400">Pending I&M account setup</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Audit Trail --}}
            @if($auditLogs->isNotEmpty())
            <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-300">Audit Trail</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3 text-left">Date</th>
                                <th class="px-5 py-3 text-left">Action</th>
                                <th class="px-5 py-3 text-left">Actor</th>
                                <th class="px-5 py-3 text-left hidden md:table-cell">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach($auditLogs as $log)
                            <tr class="hover:bg-gray-900">
                                <td class="px-5 py-3 text-xs text-gray-400">
                                    {{ \Carbon\Carbon::parse($log->created_at)->timezone('Africa/Nairobi')->format('d M Y, H:i') }}
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-200 font-medium">
                                    {{ str_replace('_', ' ', $log->action) }}
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-400">
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
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-5 sticky top-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-300">Actions</h3>

                @if(! $canWrite)
                <div class="bg-blue-900/20 border border-blue-800 text-blue-300 text-xs px-3 py-2 rounded-lg">
                    Read-only access. Only Tier 3+ can approve or reject.
                </div>
                @endif

                {{-- APPLIED stage --}}
                @if($facility->onboarding_status === 'APPLIED')
                <div class="bg-gray-900/50 border border-gray-700 text-gray-400 text-xs px-3 py-3 rounded-lg">
                    Waiting for PPB verification before approval is available.
                </div>
                @if($canWrite)
                <form method="POST" action="/admin/registrations/{{ $facility->ulid }}/verify-ppb">
                    @csrf
                    <button type="submit"
                            class="w-full px-4 py-2.5 border border-blue-800 text-blue-400 rounded-lg text-sm hover:bg-blue-900/20 transition-colors">
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
                            class="w-full px-4 py-2.5 bg-yellow-400 text-gray-900 rounded-lg text-sm hover:bg-yellow-500 transition-colors">
                        Approve — Move to Account Linked
                    </button>
                </form>
                <button @click="showRejectModal = true"
                        class="w-full px-4 py-2.5 border border-red-800 text-red-400 rounded-lg text-sm hover:bg-red-900/20 transition-colors">
                    Reject Registration
                </button>
                @endif

                {{-- ACCOUNT_LINKED stage --}}
                @if($facility->onboarding_status === 'ACCOUNT_LINKED' && $canWrite)
                <form method="POST" action="/admin/registrations/{{ $facility->ulid }}/activate">
                    @csrf
                    <button type="submit"
                            class="w-full px-4 py-2.5 bg-yellow-400 text-gray-900 rounded-lg text-sm hover:bg-yellow-500 transition-colors">
                        Activate Facility
                    </button>
                </form>
                @if(! $facility->banking_account_validated_at)
                <p class="text-xs text-amber-400">I&M banking account not yet validated — activation will be blocked.</p>
                @endif
                @if($facility->ppb_licence_status !== 'VALID')
                <p class="text-xs text-red-400">PPB licence is {{ $facility->ppb_licence_status ?? 'unknown' }} — activation will be blocked.</p>
                @endif
                <button @click="showRejectModal = true"
                        class="w-full px-4 py-2.5 border border-red-800 text-red-400 rounded-lg text-sm hover:bg-red-900/20 transition-colors">
                    Reject Registration
                </button>
                @endif

                {{-- ACTIVE --}}
                @if($facility->onboarding_status === 'ACTIVE')
                <div class="bg-green-900/20 border border-green-800 text-green-300 text-xs px-3 py-3 rounded-lg">
                    This facility is already ACTIVE. No further actions required.
                </div>
                @endif

                {{-- Quick info --}}
                <div class="border-t border-gray-700 pt-4 space-y-2 text-xs text-gray-400">
                    <p>Onboarding: <strong class="text-gray-300">{{ $facility->onboarding_status }}</strong></p>
                    <p>Facility Status: <strong class="text-gray-300">{{ $facility->facility_status ?? 'N/A' }}</strong></p>
                    <p>PPB Licence: <strong class="text-gray-300">{{ $facility->ppb_licence_status ?? 'PENDING' }}</strong></p>
                </div>
            </div>
        </div>

    </div>

    {{-- Rejection Modal --}}
    <div x-show="showRejectModal"
         class="fixed inset-0 bg-gray-900/50 z-50 flex items-center justify-center"
         x-cloak>
        <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-md shadow-xl" @click.outside="showRejectModal = false">
            <h3 class="text-base font-semibold text-white">Reject Registration</h3>
            <p class="text-sm text-gray-400 mt-1">
                This will mark <strong>{{ $facility->facility_name }}</strong> as rejected.
                An SMS will be sent to the owner.
            </p>
            <form method="POST" action="/admin/registrations/{{ $facility->ulid }}/reject" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-300 mb-1">Rejection Reason (min 20 characters)</label>
                    <textarea name="rejection_reason" rows="4" required minlength="20" maxlength="1000"
                              x-data="{ count: 0 }"
                              x-on:input="count = $event.target.value.length"
                              placeholder="Provide a detailed reason for rejecting this registration..."
                              class="w-full px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
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
                            class="flex-1 px-4 py-2 border border-gray-600 rounded-lg text-sm text-gray-400 hover:bg-gray-900">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
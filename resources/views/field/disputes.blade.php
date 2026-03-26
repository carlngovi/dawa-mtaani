@extends('layouts.app')
@section('title', 'Escalated Disputes — Dawa Mtaani')
@section('content')
<div class="space-y-6" x-data="{ findingId: null, findingText: '' }">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Escalated Disputes — {{ $county }} County</h1>
        <p class="text-sm text-gray-500 mt-1">SGA's 24-hour response window has already expired on these disputes</p>
    </div>

    {{-- Warning --}}
    <div class="bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 rounded-lg">
        Your findings are <strong>advisory only</strong>. Binding resolution is made by Admin Tier 2
        after reviewing your submission.
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Order</th>
                        <th class="px-5 py-3 text-left">Retail Facility</th>
                        <th class="px-5 py-3 text-left">Reason</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Notes</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Raised</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($disputes as $dispute)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-mono text-xs text-gray-500">
                            {{ substr($dispute->order_ulid, -8) }}
                        </td>
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $dispute->facility_name }}</td>
                        <td class="px-5 py-3 text-xs text-gray-600">
                            {{ str_replace('_', ' ', $dispute->reason) }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">
                            {{ $dispute->notes ? \Illuminate\Support\Str::limit($dispute->notes, 60) : '—' }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">
                            {{ \Carbon\Carbon::parse($dispute->raised_at)->timezone('Africa/Nairobi')->format('d M, H:i') }}
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $badge = match($dispute->status) {
                                    'OPEN'         => 'bg-amber-100 text-amber-700',
                                    'UNDER_REVIEW' => 'bg-blue-100 text-blue-700',
                                    'RESOLVED'     => 'bg-green-100 text-green-700',
                                    default        => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                                {{ str_replace('_', ' ', $dispute->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <button @click="findingId = {{ $dispute->id }}; findingText = ''"
                                    class="px-3 py-1.5 bg-green-700 text-white rounded-lg text-xs hover:bg-green-800 transition-colors">
                                Submit Finding
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-gray-400 text-sm">
                            No disputes escalated to your county yet
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $disputes->links() }}</div>

    {{-- Finding modal --}}
    <div x-show="findingId !== null"
         class="fixed inset-0 bg-gray-900/50 z-50 flex items-center justify-center"
         x-cloak>
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
            <h3 class="text-base font-semibold text-gray-900">Field Agent Finding</h3>
            <p class="text-sm text-gray-500 mt-1">
                Your finding is advisory. Admin Tier 2 makes the binding resolution.
            </p>
            <form method="POST"
                  :action="'/api/v1/disputes/' + findingId + '/agent-finding'"
                  class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Your Investigation Findings</label>
                    <textarea name="agent_finding" x-model="findingText"
                              rows="4" required minlength="30"
                              placeholder="Describe your findings from investigating this dispute (minimum 30 characters)..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800">
                        Submit Finding
                    </button>
                    <button type="button" @click="findingId = null"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

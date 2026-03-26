@extends('layouts.app')
@section('title', 'T0 Approvals — Dawa Mtaani')
@section('content')
<div class="space-y-6" x-data="{ confirmId: null, confirmOp: '', confirmAction: '' }">

    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">T0 Write Approvals</h1>
            <p class="text-sm text-gray-500 mt-1">Technical Admin write operations require super_admin sign-off</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-700">
            Tier 1
        </span>
    </div>

    <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm px-4 py-3 rounded-lg">
        Review requests carefully. Approvals are logged to the security audit trail and cannot be undone.
    </div>

    @if($approvals->total() > 0)
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Operation Type</th>
                        <th class="px-5 py-3 text-left">Description</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Requested</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($approvals as $approval)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-800">
                            {{ str_replace('_', ' ', $approval->operation_type ?? '—') }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-600 max-w-xs">
                            {{ \Illuminate\Support\Str::limit($approval->description ?? '—', 80) }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">
                            @isset($approval->created_at)
                                {{ \Carbon\Carbon::parse($approval->created_at)->timezone('Africa/Nairobi')->format('d M Y, H:i') }}
                            @else — @endisset
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $badge = match($approval->status ?? '') {
                                    'PENDING'  => 'bg-amber-100 text-amber-700',
                                    'APPROVED' => 'bg-green-100 text-green-700',
                                    'REJECTED' => 'bg-red-100 text-red-700',
                                    default    => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                                {{ $approval->status ?? '—' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            @if(($approval->status ?? '') === 'PENDING')
                            <div class="flex gap-2">
                                <button @click="confirmId = {{ $approval->id }};
                                                confirmOp = '{{ addslashes($approval->operation_type ?? '') }}';
                                                confirmAction = 'approve'"
                                        class="px-3 py-1.5 bg-green-700 text-white rounded-lg text-xs hover:bg-green-800">
                                    Approve
                                </button>
                                <button @click="confirmId = {{ $approval->id }};
                                                confirmOp = '{{ addslashes($approval->operation_type ?? '') }}';
                                                confirmAction = 'reject'"
                                        class="px-3 py-1.5 border border-red-200 text-red-600 rounded-lg text-xs hover:bg-red-50">
                                    Reject
                                </button>
                            </div>
                            @else
                                <span class="text-xs text-gray-400">
                                    @isset($approval->reviewed_at)
                                        {{ \Carbon\Carbon::parse($approval->reviewed_at)->timezone('Africa/Nairobi')->format('d M Y') }}
                                    @else — @endisset
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div>{{ $approvals->links() }}</div>

    @else
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <p class="text-gray-400 text-sm">No pending write approval requests.</p>
    </div>
    @endif

    {{-- Confirm modal --}}
    <div x-show="confirmId !== null"
         class="fixed inset-0 bg-gray-900/50 z-50 flex items-center justify-center"
         x-cloak>
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl">
            <h3 class="text-base font-semibold text-gray-900"
                x-text="confirmAction === 'approve' ? 'Approve Write Operation' : 'Reject Write Operation'">
            </h3>
            <p class="text-sm text-gray-500 mt-2">
                Operation: <strong x-text="confirmOp"></strong>
            </p>
            <p class="text-xs text-amber-600 mt-2" x-show="confirmAction === 'approve'">
                This approval is logged and cannot be undone.
            </p>
            <div class="flex gap-3 mt-6">
                <form method="POST"
                      :action="'/super/t0-approvals/' + confirmId + '/' + confirmAction"
                      class="flex-1">
                    @csrf
                    <button type="submit"
                            :class="confirmAction === 'approve'
                                ? 'bg-green-700 hover:bg-green-800'
                                : 'bg-red-600 hover:bg-red-700'"
                            class="w-full px-4 py-2 text-white rounded-lg text-sm"
                            x-text="confirmAction === 'approve' ? 'Confirm Approve' : 'Confirm Reject'">
                    </button>
                </form>
                <button @click="confirmId = null"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                    Cancel
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

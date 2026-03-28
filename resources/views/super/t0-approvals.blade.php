@extends('layouts.app')
@section('title', 'T0 Approvals — Dawa Mtaani')
@section('content')
<div class="space-y-6" x-data="{ confirmId: null, confirmOp: '', confirmAction: '' }">

    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">T0 Write Approvals</h1>
            <p class="text-sm text-gray-400 mt-1">Technical Admin write operations require super_admin sign-off</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-yellow-900/30 text-yellow-400 border border-yellow-800">
            Tier 1
        </span>
    </div>

    <div class="bg-blue-900/20 border border-gray-700 text-blue-300 text-sm px-4 py-3 rounded-lg">
        Review requests carefully. Approvals are logged to the security audit trail and cannot be undone.
    </div>

    @if($approvals->total() > 0)
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead class="bg-gray-900 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Operation Type</th>
                        <th class="px-5 py-3 text-left">Description</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Requested</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($approvals as $approval)
                    <tr class="hover:bg-gray-700/50">
                        <td class="px-5 py-3 font-medium text-gray-200">
                            {{ str_replace('_', ' ', $approval->operation_type ?? '—') }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 max-w-xs">
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
                                    'PENDING'  => 'bg-amber-900/30 text-amber-400',
                                    'APPROVED' => 'bg-green-900/30 text-green-400',
                                    'REJECTED' => 'bg-red-900/30 text-red-400',
                                    default    => 'bg-gray-700 text-gray-400',
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
                                                confirmAction = 'confirm'"
                                        class="px-3 py-1.5 bg-yellow-400 text-white font-medium rounded-lg text-xs hover:bg-yellow-500">
                                    Approve
                                </button>
                                <button @click="confirmId = {{ $approval->id }};
                                                confirmOp = '{{ addslashes($approval->operation_type ?? '') }}';
                                                confirmAction = 'reject'"
                                        class="px-3 py-1.5 border border-gray-700 text-red-400 rounded-lg text-xs hover:bg-red-900/20">
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
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
        <p class="text-gray-400 text-sm">No pending write approval requests.</p>
    </div>
    @endif

    {{-- Confirm modal --}}
    <div x-show="confirmId !== null"
         class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center"
         x-cloak>
        <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-sm shadow-xl border border-gray-700">
            <h3 class="text-base font-semibold text-white"
                x-text="confirmAction === 'confirm' ? 'Approve Write Operation' : 'Reject Write Operation'">
            </h3>
            <p class="text-sm text-gray-400 mt-2">
                Operation: <strong x-text="confirmOp"></strong>
            </p>
            <p class="text-xs text-amber-400 mt-2" x-show="confirmAction === 'confirm'">
                This approval is logged and cannot be undone.
            </p>
            <div class="flex gap-3 mt-6">
                <form method="POST"
                      :action="'/super/t0-approvals/' + confirmId + '/' + confirmAction"
                      class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm"
                            x-text="confirmAction === 'confirm' ? 'Confirm Approve' : 'Confirm Reject'">
                    </button>
                </form>
                <button @click="confirmId = null"
                        class="flex-1 px-4 py-2 border border-gray-600 rounded-lg text-sm text-gray-400 hover:bg-gray-700/50">
                    Cancel
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

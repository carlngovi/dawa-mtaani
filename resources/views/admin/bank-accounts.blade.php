@extends('layouts.admin')
@section('title', 'Bank Accounts — Dawa Mtaani')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-white">Pharmacy Bank Accounts</h1>
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto"><table class="w-full text-sm min-w-[900px]">
            <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Facility</th>
                    <th class="px-5 py-3 text-left">County</th>
                    <th class="px-5 py-3 text-left">Account Number</th>
                    <th class="px-5 py-3 text-left">Validated</th>
                    <th class="px-5 py-3 text-left">Membership</th>
                    <th class="px-5 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($accounts as $account)
                <tr class="hover:bg-gray-900">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-200">{{ $account->facility_name }}</p>
                        <p class="text-xs text-gray-400">{{ $account->phone }}</p>
                    </td>
                    <td class="px-5 py-3 text-gray-400">{{ $account->county }}</td>
                    <td class="px-5 py-3 font-mono text-xs text-gray-400">{{ $account->banking_account_number }}</td>
                    <td class="px-5 py-3 text-xs">
                        @if($account->banking_account_validated_at)
                            <span class="text-green-400">✓ {{ \Carbon\Carbon::parse($account->banking_account_validated_at)->format('d M Y') }}</span>
                        @else
                            <span class="text-amber-500">Pending</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ $account->network_membership === 'NETWORK' ? 'bg-green-900/30 text-green-400 border border-gray-700' : 'bg-gray-700 text-gray-400' }}">
                            {{ $account->network_membership }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ $account->facility_status === 'ACTIVE' ? 'bg-green-900/30 text-green-400 border border-gray-700' : 'bg-red-900/30 text-red-400 border border-gray-700' }}">
                            {{ $account->facility_status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No bank accounts linked</td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="px-5 py-3 border-t border-gray-700">{{ $accounts->links() }}</div>
    </div>
</div>
@endsection

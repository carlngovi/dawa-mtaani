@extends('layouts.admin')
@section('title', 'Bank Accounts — Dawa Mtaani')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Pharmacy Bank Accounts</h1>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm min-w-[900px]">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Facility</th>
                    <th class="px-5 py-3 text-left">County</th>
                    <th class="px-5 py-3 text-left">Account Number</th>
                    <th class="px-5 py-3 text-left">Validated</th>
                    <th class="px-5 py-3 text-left">Membership</th>
                    <th class="px-5 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($accounts as $account)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-800">{{ $account->facility_name }}</p>
                        <p class="text-xs text-gray-400">{{ $account->phone }}</p>
                    </td>
                    <td class="px-5 py-3 text-gray-500">{{ $account->county }}</td>
                    <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ $account->banking_account_number }}</td>
                    <td class="px-5 py-3 text-xs">
                        @if($account->banking_account_validated_at)
                            <span class="text-green-600">✓ {{ \Carbon\Carbon::parse($account->banking_account_validated_at)->format('d M Y') }}</span>
                        @else
                            <span class="text-amber-500">Pending</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ $account->network_membership === 'NETWORK' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $account->network_membership }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ $account->facility_status === 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $account->facility_status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No bank accounts linked</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3 border-t border-gray-100">{{ $accounts->links() }}</div>
    </div>
</div>
@endsection

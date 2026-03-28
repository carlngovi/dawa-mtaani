@extends('layouts.app')
@section('title', 'Invitations — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">User Invitations</h1>
            <p class="text-sm text-gray-400 mt-1">Invite people to join the Dawa Mtaani network</p>
        </div>
    </div>

    @if(session('success'))
    <div x-data="{ show: true, copied: false }" x-show="show"
         class="bg-green-900/20 border border-gray-700 rounded-xl p-4">
        <div class="flex items-start justify-between gap-4">
            <p class="text-sm text-green-300 flex-1 break-all">{{ session('success') }}</p>
            <div class="flex gap-2 flex-shrink-0">
                @php preg_match('/https?:\/\/\S+/', session('success'), $m); $link = $m[0] ?? ''; @endphp
                @if($link)
                <button @click="navigator.clipboard.writeText('{{ $link }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="px-3 py-1 bg-yellow-400 text-white text-xs rounded-lg hover:bg-yellow-500">
                    <span x-show="!copied">Copy Link</span>
                    <span x-show="copied">Copied!</span>
                </button>
                @endif
                <button @click="show = false" class="text-green-400 hover:text-green-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-900/20 border border-gray-700 rounded-xl p-4">
        <p class="text-sm text-red-400">{{ $errors->first() }}</p>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Send Invite Form --}}
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-200 mb-4">Send New Invitation</h3>
            <form method="POST" action="/admin/invitations" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Jane Mwangi"
                           class="h-10 w-full rounded-lg border border-gray-600 bg-gray-800 px-3 text-sm text-white placeholder-gray-500 focus:border-yellow-400 focus:outline-none focus:ring-1 focus:ring-yellow-400"/>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="jane@pharmacy.co.ke"
                           class="h-10 w-full rounded-lg border border-gray-600 bg-gray-800 px-3 text-sm text-white placeholder-gray-500 focus:border-yellow-400 focus:outline-none focus:ring-1 focus:ring-yellow-400"/>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Role *</label>
                    <select name="intended_role" required
                            class="h-10 w-full rounded-lg border border-gray-600 bg-gray-800 px-3 text-sm text-white focus:border-yellow-400 focus:outline-none focus:ring-1 focus:ring-yellow-400">
                        @foreach($roles as $value => $label)
                        <option value="{{ $value }}" {{ old('intended_role') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Facility (optional)</label>
                    <select name="facility_id"
                            class="h-10 w-full rounded-lg border border-gray-600 bg-gray-800 px-3 text-sm text-white focus:border-yellow-400 focus:outline-none focus:ring-1 focus:ring-yellow-400">
                        <option value="">— No facility —</option>
                        @foreach($facilities as $f)
                        <option value="{{ $f->id }}" {{ old('facility_id') == $f->id ? 'selected' : '' }}>
                            {{ $f->facility_name }} ({{ $f->ppb_facility_type }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                        class="w-full py-2.5 bg-yellow-400 hover:bg-yellow-500 text-white text-sm font-medium rounded-lg transition-colors">
                    Send Invitation
                </button>
            </form>
        </div>

        {{-- Invitations Table --}}
        <div class="lg:col-span-2 bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-700">
                <h3 class="text-sm font-semibold text-gray-200">All Invitations</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[580px]">
                    <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Name / Email</th>
                            <th class="px-4 py-3 text-left">Role</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Expires</th>
                            <th class="px-4 py-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @forelse($invitations as $inv)
                        <tr class="hover:bg-gray-900">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-200">{{ $inv->name }}</p>
                                <p class="text-xs text-gray-400">{{ $inv->email }}</p>
                                @if($inv->facility_name)
                                <p class="text-xs text-gray-400">{{ $inv->facility_name }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-900/20 text-blue-400">
                                    {{ $inv->intended_role }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($inv->accepted_at)
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-green-900/30 text-green-400 border border-gray-700">Accepted</span>
                                @elseif(\Carbon\Carbon::parse($inv->expires_at)->isPast())
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-red-900/30 text-red-400 border border-gray-700">Expired</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-amber-900/30 text-amber-400">Pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400">
                                {{ \Carbon\Carbon::parse($inv->expires_at)->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                @if(!$inv->accepted_at)
                                <div class="flex gap-2 flex-wrap">
                                    <button onclick="navigator.clipboard.writeText('{{ url('/register/accept/' . $inv->token) }}')"
                                            class="text-xs px-2 py-1 border border-gray-700 rounded text-gray-400 hover:bg-gray-900">
                                        Copy Link
                                    </button>
                                    <form method="POST" action="/admin/invitations/{{ $inv->id }}"
                                          onsubmit="return confirm('Revoke this invitation?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs px-2 py-1 border border-gray-700 rounded text-red-400 hover:bg-red-900/20">
                                            Revoke
                                        </button>
                                    </form>
                                </div>
                                @else
                                <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">
                                No invitations yet. Send your first one using the form.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-gray-700">
                {{ $invitations->links() }}
            </div>
        </div>

    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Invitations — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">User Invitations</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Invite people to join the Dawa Mtaani network</p>
        </div>
    </div>

    @if(session('success'))
    <div x-data="{ show: true, copied: false }" x-show="show"
         class="bg-green-50 border border-green-200 rounded-xl p-4 dark:bg-green-900/20 dark:border-green-800">
        <div class="flex items-start justify-between gap-4">
            <p class="text-sm text-green-800 dark:text-green-300 flex-1 break-all">{{ session('success') }}</p>
            <div class="flex gap-2 flex-shrink-0">
                @php preg_match('/https?:\/\/\S+/', session('success'), $m); $link = $m[0] ?? ''; @endphp
                @if($link)
                <button @click="navigator.clipboard.writeText('{{ $link }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="px-3 py-1 bg-green-700 text-white text-xs rounded-lg hover:bg-green-800">
                    <span x-show="!copied">Copy Link</span>
                    <span x-show="copied">Copied!</span>
                </button>
                @endif
                <button @click="show = false" class="text-green-600 hover:text-green-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 dark:bg-red-900/20 dark:border-red-800">
        <p class="text-sm text-red-600 dark:text-red-400">{{ $errors->first() }}</p>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Send Invite Form --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-4">Send New Invitation</h3>
            <form method="POST" action="/admin/invitations" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Jane Mwangi"
                           class="h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-sm text-gray-800 dark:text-white placeholder:text-gray-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10"/>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="jane@pharmacy.co.ke"
                           class="h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-sm text-gray-800 dark:text-white placeholder:text-gray-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10"/>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Role *</label>
                    <select name="intended_role" required
                            class="h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-sm text-gray-800 dark:text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10">
                        @foreach($roles as $value => $label)
                        <option value="{{ $value }}" {{ old('intended_role') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Facility (optional)</label>
                    <select name="facility_id"
                            class="h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-sm text-gray-800 dark:text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10">
                        <option value="">— No facility —</option>
                        @foreach($facilities as $f)
                        <option value="{{ $f->id }}" {{ old('facility_id') == $f->id ? 'selected' : '' }}>
                            {{ $f->facility_name }} ({{ $f->ppb_facility_type }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                        class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Send Invitation
                </button>
            </form>
        </div>

        {{-- Invitations Table --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white">All Invitations</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[580px]">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-xs text-gray-500 dark:text-gray-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Name / Email</th>
                            <th class="px-4 py-3 text-left">Role</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Expires</th>
                            <th class="px-4 py-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($invitations as $inv)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800 dark:text-white">{{ $inv->name }}</p>
                                <p class="text-xs text-gray-400">{{ $inv->email }}</p>
                                @if($inv->facility_name)
                                <p class="text-xs text-gray-400">{{ $inv->facility_name }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                    {{ $inv->intended_role }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($inv->accepted_at)
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Accepted</span>
                                @elseif(\Carbon\Carbon::parse($inv->expires_at)->isPast())
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">Expired</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-400">
                                {{ \Carbon\Carbon::parse($inv->expires_at)->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                @if(!$inv->accepted_at)
                                <div class="flex gap-2 flex-wrap">
                                    <button onclick="navigator.clipboard.writeText('{{ url('/register/accept/' . $inv->token) }}')"
                                            class="text-xs px-2 py-1 border border-gray-200 dark:border-gray-700 rounded text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800">
                                        Copy Link
                                    </button>
                                    <form method="POST" action="/admin/invitations/{{ $inv->id }}"
                                          onsubmit="return confirm('Revoke this invitation?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs px-2 py-1 border border-red-200 rounded text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/20">
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
            <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-800">
                {{ $invitations->links() }}
            </div>
        </div>

    </div>
</div>
@endsection

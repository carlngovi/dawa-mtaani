@extends('layouts.app')
@section('title', 'Incident Log — Dawa Mtaani')
@section('content')
<div class="space-y-6" x-data="{ logOpen: false }">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div>
                <h1 class="text-2xl font-bold text-white">Incident Log</h1>
                <p class="text-sm text-gray-400 mt-1">Security events and platform incidents</p>
            </div>
            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-red-900/30 text-red-400 border border-gray-700">
                Tier 0
            </span>
        </div>
        <button @click="logOpen = true"
                class="px-4 py-2.5 bg-yellow-400 text-white rounded-lg text-sm hover:bg-yellow-500 transition-colors">
            + Log Incident
        </button>
    </div>

    @if($incidents->total() > 0)
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead class="bg-gray-900 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Event Type</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Description</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">User / IP</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Severity</th>
                        <th class="px-5 py-3 text-left">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($incidents as $incident)
                    <tr class="hover:bg-gray-700/50">
                        <td class="px-5 py-3 text-xs font-medium text-gray-300">
                            {{ str_replace('_', ' ', $incident->event_type ?? '—') }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">
                            {{ \Illuminate\Support\Str::limit($incident->description ?? '—', 60) }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">
                            {{ $incident->ip_address ?? '—' }}
                        </td>
                        <td class="px-5 py-3 hidden lg:table-cell">
                            @isset($incident->severity)
                            @php
                                $badge = match($incident->severity) {
                                    'CRITICAL' => 'bg-red-900/30 text-red-400',
                                    'HIGH'     => 'bg-orange-900/30 text-orange-400',
                                    'MEDIUM'   => 'bg-amber-900/30 text-amber-400',
                                    'LOW'      => 'bg-gray-700 text-gray-400',
                                    default    => 'bg-gray-700 text-gray-400',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                                {{ $incident->severity }}
                            </span>
                            @endisset
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400">
                            @isset($incident->created_at)
                                {{ \Carbon\Carbon::parse($incident->created_at)->timezone('Africa/Nairobi')->format('d M Y, H:i') }}
                            @else — @endisset
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div>{{ $incidents->links() }}</div>

    @else
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-12 text-center">
        <p class="text-gray-400 text-sm">No security events recorded yet.</p>
    </div>
    @endif

    {{-- Log incident modal --}}
    <div x-show="logOpen"
         class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center"
         x-cloak>
        <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-md shadow-xl border border-gray-700">
            <h3 class="text-base font-semibold text-white">Log New Incident</h3>
            <form method="POST" action="/api/v1/tech/incidents" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Severity</label>
                    <select name="severity" required
                            class="w-full px-3 py-2.5 border border-gray-600 bg-gray-900 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                        <option value="P1_CRITICAL">P1 — Critical</option>
                        <option value="P2_HIGH">P2 — High</option>
                        <option value="P3_MEDIUM" selected>P3 — Medium</option>
                        <option value="P4_LOW">P4 — Low</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Title</label>
                    <input type="text" name="title" required
                           class="w-full px-3 py-2.5 border border-gray-600 bg-gray-900 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Description</label>
                    <textarea name="description" rows="3" required
                              class="w-full px-3 py-2.5 border border-gray-600 bg-gray-900 text-white rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-yellow-400 focus:border-yellow-400"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-yellow-400 text-white rounded-lg text-sm hover:bg-yellow-500">
                        Log Incident
                    </button>
                    <button type="button" @click="logOpen = false"
                            class="flex-1 px-4 py-2 border border-gray-600 rounded-lg text-sm text-gray-400 hover:bg-gray-700/50">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

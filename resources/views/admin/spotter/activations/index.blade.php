@extends('layouts.admin')

@section('title', 'Spotter Activations — Dawa Mtaani')

@section('content')

<div class="space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Activation Codes</h1>
        <a href="{{ route('admin.spotter.activations.create') }}"
           class="px-4 py-2 bg-yellow-400/20 text-yellow-400 rounded-lg text-sm font-medium hover:bg-yellow-400/30 transition">
            Generate Code
        </a>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-700 text-gray-400 text-left">
                    <th class="px-4 py-3">Spotter</th>
                    <th class="px-4 py-3">Code</th>
                    <th class="px-4 py-3">Created by</th>
                    <th class="px-4 py-3">Expires</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="text-gray-300">
                @forelse($codes as $code)
                    <tr class="border-b border-gray-700/50 hover:bg-gray-700/30">
                        <td class="px-4 py-3">{{ $code->spotter->name ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono">{{ substr($code->code, 0, 4) }}****-****</td>
                        <td class="px-4 py-3">{{ $code->creator->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $code->expires_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3">
                            @if($code->consumed_at)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-400/20 text-gray-400">Used</span>
                            @elseif($code->expires_at->isPast())
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-400/20 text-red-400">Expired</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-400/20 text-green-400">Active</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No activation codes yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $codes->links() }}</div>

</div>

@endsection

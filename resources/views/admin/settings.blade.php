@extends('layouts.admin')
@section('title', 'System Settings — Dawa Mtaani')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">System Settings</h1>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm min-w-[640px]">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Key</th>
                    <th class="px-5 py-3 text-left">Value</th>
                    <th class="px-5 py-3 text-left">Updated</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($settings as $setting)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ $setting->key }}</td>
                    <td class="px-5 py-3 text-gray-800">{{ $setting->value }}</td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        {{ $setting->updated_at ? \Carbon\Carbon::parse($setting->updated_at)->format('d M Y') : '—' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="px-5 py-10 text-center text-gray-400">No settings</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

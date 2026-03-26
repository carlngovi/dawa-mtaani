@extends('layouts.admin')
@section('title', 'Monitoring — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <h1 class="text-2xl font-bold text-white">Monitoring & Observability</h1>

    {{-- Active Alerts --}}
    @if($activeAlerts->count() > 0)
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700">
            <h3 class="text-sm font-semibold text-gray-300">Active Alerts ({{ $activeAlerts->count() }})</h3>
        </div>
        <div class="divide-y divide-gray-700">
            @foreach($activeAlerts as $alert)
            <div class="px-5 py-3 flex items-center justify-between">
                <div>
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium mr-2
                        {{ $alert->severity === 'CRITICAL' ? 'bg-red-900/30 text-red-400 border border-red-800' : ($alert->severity === 'WARNING' ? 'bg-amber-900/30 text-amber-400 border border-amber-800' : 'bg-blue-900/30 text-blue-400 border border-blue-800') }}">
                        {{ $alert->severity }}
                    </span>
                    <span class="text-sm text-gray-200">{{ $alert->metric_name }}</span>
                    @if($alert->county)
                        <span class="text-xs text-gray-400 ml-2">{{ $alert->county }}</span>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400">Expected {{ number_format($alert->expected_value) }} · Got {{ number_format($alert->actual_value) }}</p>
                    <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($alert->created_at)->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Job Health --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700">
            <h3 class="text-sm font-semibold text-gray-300">Scheduled Job Health</h3>
        </div>
        <div class="overflow-x-auto"><table class="w-full text-sm min-w-[640px]">
            <thead class="bg-gray-900/50 text-xs text-gray-400 uppercase">
                <tr>
                    <th class="px-5 py-3 text-left">Job</th>
                    <th class="px-5 py-3 text-left">Last Run</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-right">Avg Duration</th>
                    <th class="px-5 py-3 text-right">Failures</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($jobs as $job)
                <tr class="hover:bg-gray-900 {{ $job->fail_count >= 3 ? 'bg-red-900/20' : '' }}">
                    <td class="px-5 py-3 font-medium text-gray-200">{{ $job->short_name }}</td>
                    <td class="px-5 py-3 text-gray-400 text-xs">
                        {{ $job->last_run_at ? \Carbon\Carbon::parse($job->last_run_at)->diffForHumans() : 'Never' }}
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ $job->last_status === 'COMPLETED' ? 'bg-green-900/30 text-green-400 border border-green-800' : ($job->last_status === 'FAILED' ? 'bg-red-900/30 text-red-400 border border-red-800' : 'bg-amber-900/30 text-amber-400 border border-amber-800') }}">
                            {{ $job->last_status ?? 'UNKNOWN' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right text-gray-400 text-xs">
                        {{ $job->avg_duration_ms ? number_format($job->avg_duration_ms).'ms' : '—' }}
                    </td>
                    <td class="px-5 py-3 text-right {{ $job->fail_count >= 3 ? 'text-red-400 font-bold' : 'text-gray-400' }}">
                        {{ $job->fail_count }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">No job records yet</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>

    {{-- SLO Compliance --}}
    @if($sloRecords->count() > 0)
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700">
            <h3 class="text-sm font-semibold text-gray-300">SLO Compliance</h3>
        </div>
        <div class="divide-y divide-gray-700">
            @foreach($sloRecords as $sliName => $records)
            <div class="px-5 py-3 flex items-center justify-between">
                <span class="text-sm text-gray-200">{{ str_replace('_', ' ', $sliName) }}</span>
                @php $latest = $records->first(); @endphp
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium {{ $latest->is_compliant ? 'text-green-400' : 'text-red-400' }}">
                        {{ number_format($latest->compliance_pct, 2) }}%
                    </span>
                    <span class="text-xs text-gray-400">target {{ $latest->slo_target_pct }}%</span>
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                        {{ $latest->is_compliant ? 'bg-green-900/30 text-green-400 border border-green-800' : 'bg-red-900/30 text-red-400 border border-red-800' }}">
                        {{ $latest->is_compliant ? 'COMPLIANT' : 'BREACHED' }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection

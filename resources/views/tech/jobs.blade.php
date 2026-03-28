@extends('layouts.app')
@section('title', 'Job Monitor — Dawa Mtaani')
@section('content')
<div class="space-y-6" x-data="{ payloadId: null, payloadText: '' }">

    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">Job Health Monitor</h1>
            <p class="text-sm text-gray-400 mt-1">Scheduled job heartbeats and failed jobs</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-red-900/30 text-red-400 border border-gray-700">
            Tier 0
        </span>
    </div>

    {{-- Queue depth --}}
    <div class="bg-gray-800 rounded-xl border border-gray-700 p-5">
        <p class="text-xs text-gray-400">Queue Depth</p>
        <p class="text-3xl font-bold text-{{ $pendingJobs > 200 ? 'red-400' : ($pendingJobs > 50 ? 'amber-400' : 'white') }} mt-1">
            {{ number_format($pendingJobs) }} pending
        </p>
    </div>

    {{-- Job health table --}}
    @if($jobHealth->isNotEmpty())
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700">
            <h3 class="text-sm font-semibold text-gray-300">Scheduled Job Health</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead class="bg-gray-900 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Job Name</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Last Run</th>
                        <th class="px-5 py-3 text-right hidden md:table-cell">Duration</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-right">Failures (7d)</th>
                        <th class="px-5 py-3 text-left">Health</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($jobHealth as $job)
                    @php
                        $failures   = $failureCounts->get($job->job_name, 0);
                        $health     = $failures >= 3 ? 'CRITICAL' : ($failures >= 1 ? 'WARNING' : 'OK');
                        $healthBadge = match($health) {
                            'CRITICAL' => 'bg-red-900/30 text-red-400',
                            'WARNING'  => 'bg-amber-900/30 text-amber-400',
                            default    => 'bg-green-900/30 text-green-400',
                        };
                        $statusBadge = match($job->status) {
                            'COMPLETED' => 'bg-green-900/30 text-green-400',
                            'FAILED'    => 'bg-red-900/30 text-red-400',
                            'STARTED'   => 'bg-blue-900/30 text-blue-400',
                            default     => 'bg-gray-700 text-gray-400',
                        };
                    @endphp
                    <tr class="hover:bg-gray-700/50">
                        <td class="px-5 py-3 font-mono text-xs text-gray-300">{{ $job->job_name }}</td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">
                            @isset($job->started_at)
                                {{ \Carbon\Carbon::parse($job->started_at)->timezone('Africa/Nairobi')->format('d M, H:i') }}
                            @else — @endisset
                        </td>
                        <td class="px-5 py-3 text-right text-xs text-gray-400 hidden md:table-cell">
                            {{ isset($job->duration_ms) ? number_format($job->duration_ms) . 'ms' : '—' }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $statusBadge }}">
                                {{ $job->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            @if($failures > 0)
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                {{ $failures >= 3 ? 'bg-red-900/30 text-red-400' : 'bg-amber-900/30 text-amber-400' }}">
                                {{ $failures }}
                            </span>
                            @else
                            <span class="text-xs text-gray-400">0</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $healthBadge }}">
                                {{ $health }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Failed jobs --}}
    @if($failedJobs->isNotEmpty())
    <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-700 bg-red-900/20">
            <h3 class="text-sm font-semibold text-red-400">Failed Jobs (last 20)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead class="bg-gray-900 text-xs text-gray-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Job Class</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Failed At</th>
                        <th class="px-5 py-3 text-left">Error</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($failedJobs as $failed)
                    <tr class="hover:bg-gray-700/50">
                        <td class="px-5 py-3 font-mono text-xs text-gray-300">
                            {{ \Illuminate\Support\Str::afterLast($failed->queue ?? '', '\\') ?: ($failed->uuid ?? '—') }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">
                            {{ \Carbon\Carbon::parse($failed->failed_at)->timezone('Africa/Nairobi')->format('d M, H:i') }}
                        </td>
                        <td class="px-5 py-3 text-xs text-red-400">
                            {{ \Illuminate\Support\Str::limit($failed->exception ?? '—', 80) }}
                        </td>
                        <td class="px-5 py-3 flex gap-2">
                            <button @click="payloadId = {{ $failed->id }};
                                            payloadText = {{ json_encode(\Illuminate\Support\Str::limit($failed->exception ?? '', 500)) }}"
                                    class="px-3 py-1.5 border border-gray-600 text-gray-400 rounded-lg text-xs hover:bg-gray-700/50">
                                Details
                            </button>
                            <form method="POST" action="/api/v1/tech/retry-job">
                                @csrf
                                <input type="hidden" name="id" value="{{ $failed->id }}">
                                <button type="submit"
                                        class="px-3 py-1.5 bg-amber-900/30 text-amber-400 border border-amber-800 rounded-lg text-xs hover:bg-amber-900/50">
                                    Retry
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Payload modal --}}
    <div x-show="payloadId !== null"
         class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center"
         x-cloak>
        <div class="bg-gray-800 rounded-2xl p-6 w-full max-w-lg shadow-xl border border-gray-700">
            <h3 class="text-base font-semibold text-white">Exception Detail</h3>
            <pre class="mt-3 text-xs font-mono bg-gray-900 border border-gray-700 rounded-lg p-4 overflow-auto max-h-64 text-red-400"
                 x-text="payloadText"></pre>
            <button @click="payloadId = null"
                    class="mt-4 w-full px-4 py-2 bg-gray-800 border border-gray-600 text-white rounded-lg text-sm text-gray-400 hover:bg-gray-700/50">
                Close
            </button>
        </div>
    </div>

</div>
@endsection

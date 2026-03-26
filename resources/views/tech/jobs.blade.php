@extends('layouts.app')
@section('title', 'Job Monitor — Dawa Mtaani')
@section('content')
<div class="space-y-6" x-data="{ payloadId: null, payloadText: '' }">

    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Job Health Monitor</h1>
            <p class="text-sm text-gray-500 mt-1">Scheduled job heartbeats and failed jobs</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">
            Tier 0
        </span>
    </div>

    {{-- Queue depth --}}
    <div class="bg-white rounded-xl border border-{{ $pendingJobs > 200 ? 'red' : ($pendingJobs > 50 ? 'amber' : 'gray') }}-200 p-5">
        <p class="text-xs text-gray-400">Queue Depth</p>
        <p class="text-3xl font-bold text-{{ $pendingJobs > 200 ? 'red-600' : ($pendingJobs > 50 ? 'amber-600' : 'gray-900') }} mt-1">
            {{ number_format($pendingJobs) }} pending
        </p>
    </div>

    {{-- Job health table --}}
    @if($jobHealth->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Scheduled Job Health</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Job Name</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Last Run</th>
                        <th class="px-5 py-3 text-right hidden md:table-cell">Duration</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-right">Failures (7d)</th>
                        <th class="px-5 py-3 text-left">Health</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($jobHealth as $job)
                    @php
                        $failures   = $failureCounts->get($job->job_name, 0);
                        $health     = $failures >= 3 ? 'CRITICAL' : ($failures >= 1 ? 'WARNING' : 'OK');
                        $healthBadge = match($health) {
                            'CRITICAL' => 'bg-red-100 text-red-700',
                            'WARNING'  => 'bg-amber-100 text-amber-700',
                            default    => 'bg-green-100 text-green-700',
                        };
                        $statusBadge = match($job->status) {
                            'COMPLETED' => 'bg-green-100 text-green-700',
                            'FAILED'    => 'bg-red-100 text-red-700',
                            'STARTED'   => 'bg-blue-100 text-blue-700',
                            default     => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-mono text-xs text-gray-700">{{ $job->job_name }}</td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">
                            @isset($job->started_at)
                                {{ \Carbon\Carbon::parse($job->started_at)->timezone('Africa/Nairobi')->format('d M, H:i') }}
                            @else — @endisset
                        </td>
                        <td class="px-5 py-3 text-right text-xs text-gray-500 hidden md:table-cell">
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
                                {{ $failures >= 3 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
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
    <div class="bg-white rounded-xl border border-red-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-red-100 bg-red-50">
            <h3 class="text-sm font-semibold text-red-700">Failed Jobs (last 20)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Job Class</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Failed At</th>
                        <th class="px-5 py-3 text-left">Error</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($failedJobs as $failed)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-mono text-xs text-gray-700">
                            {{ \Illuminate\Support\Str::afterLast($failed->queue ?? '', '\\') ?: ($failed->uuid ?? '—') }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">
                            {{ \Carbon\Carbon::parse($failed->failed_at)->timezone('Africa/Nairobi')->format('d M, H:i') }}
                        </td>
                        <td class="px-5 py-3 text-xs text-red-600">
                            {{ \Illuminate\Support\Str::limit($failed->exception ?? '—', 80) }}
                        </td>
                        <td class="px-5 py-3 flex gap-2">
                            <button @click="payloadId = {{ $failed->id }};
                                            payloadText = {{ json_encode(\Illuminate\Support\Str::limit($failed->exception ?? '', 500)) }}"
                                    class="px-3 py-1.5 border border-gray-200 text-gray-600 rounded-lg text-xs hover:bg-gray-50">
                                Details
                            </button>
                            <form method="POST" action="/api/v1/tech/retry-job">
                                @csrf
                                <input type="hidden" name="id" value="{{ $failed->id }}">
                                <button type="submit"
                                        class="px-3 py-1.5 bg-amber-100 text-amber-700 rounded-lg text-xs hover:bg-amber-200">
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
         class="fixed inset-0 bg-gray-900/50 z-50 flex items-center justify-center"
         x-cloak>
        <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl">
            <h3 class="text-base font-semibold text-gray-900">Exception Detail</h3>
            <pre class="mt-3 text-xs font-mono bg-gray-50 rounded-lg p-4 overflow-auto max-h-64 text-red-700"
                 x-text="payloadText"></pre>
            <button @click="payloadId = null"
                    class="mt-4 w-full px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                Close
            </button>
        </div>
    </div>

</div>
@endsection

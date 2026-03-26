@extends('layouts.app')
@section('title', 'System Diagnostics — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    <div class="flex items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">System Diagnostics</h1>
            <p class="text-sm text-gray-500 mt-1">Integration health and read-only query console</p>
        </div>
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">
            Tier 0
        </span>
    </div>

    {{-- Integration health --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($integrations as $name => $status)
        @php
            $cardClass = match($status) {
                'OK'    => 'border-green-200 bg-green-50',
                'WARN'  => 'border-amber-200 bg-amber-50',
                'ERROR' => 'border-red-200 bg-red-50',
                default => 'border-gray-200',
            };
            $badgeClass = match($status) {
                'OK'    => 'bg-green-100 text-green-700',
                'WARN'  => 'bg-amber-100 text-amber-700',
                'ERROR' => 'bg-red-100 text-red-700',
                default => 'bg-gray-100 text-gray-600',
            };
        @endphp
        <div class="rounded-xl border p-4 {{ $cardClass }}">
            <p class="text-xs font-medium text-gray-700">{{ $name }}</p>
            <span class="inline-flex mt-2 px-2 py-0.5 rounded text-xs font-medium {{ $badgeClass }}">
                {{ $status }}
            </span>
        </div>
        @endforeach
    </div>

    {{-- System stats --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-{{ $queueDepth > 200 ? 'red' : ($queueDepth > 50 ? 'amber' : 'gray') }}-200 p-5">
            <p class="text-xs text-gray-400">Queue Depth</p>
            <p class="text-3xl font-bold text-{{ $queueDepth > 200 ? 'red-600' : ($queueDepth > 50 ? 'amber-600' : 'gray-900') }} mt-1">
                {{ number_format($queueDepth) }}
            </p>
        </div>
        <div class="bg-white rounded-xl border border-{{ $failedJobs > 0 ? 'red' : 'gray' }}-200 p-5">
            <p class="text-xs text-gray-400">Failed Jobs</p>
            <p class="text-3xl font-bold text-{{ $failedJobs > 0 ? 'red-600' : 'gray-900' }} mt-1">
                {{ number_format($failedJobs) }}
            </p>
            @if($failedJobs > 0)
            <a href="/tech/jobs" class="text-xs text-red-600 hover:underline mt-1 block">
                View in Job Monitor →
            </a>
            @endif
        </div>
    </div>

    {{-- Query console --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-700">Read-Only Query Console</h3>
        </div>
        <div class="p-5 space-y-4">

            <div class="bg-amber-50 border border-amber-200 text-amber-800 text-xs px-3 py-2 rounded-lg">
                Only SELECT queries permitted. Write operations require T0 approval at
                <a href="/tech/write" class="underline font-medium">/tech/write</a>.
            </div>

            <form method="POST" action="/tech/query">
                @csrf
                <div class="space-y-3">
                    <textarea name="sql" rows="4"
                              placeholder="SELECT * FROM facilities WHERE facility_status = 'ACTIVE' LIMIT 10"
                              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-green-500">{{ $querySQL ?? '' }}</textarea>
                    <button type="submit"
                            class="px-4 py-2 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
                        Run Query
                    </button>
                </div>
            </form>

            @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
            @endif

            @if($queryResults !== null)
            <div>
                <p class="text-xs text-gray-400 mb-2">
                    {{ count($queryResults) }} row(s) — query: <code class="font-mono">{{ $querySQL }}</code>
                </p>
                @if(count($queryResults) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-xs border border-gray-200">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                @foreach(array_keys((array) $queryResults[0]) as $col)
                                <th class="px-3 py-2 text-left border-b border-gray-200 font-medium">{{ $col }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($queryResults as $row)
                            <tr class="hover:bg-gray-50">
                                @foreach((array) $row as $val)
                                <td class="px-3 py-2 font-mono text-gray-600 whitespace-nowrap">
                                    {{ is_null($val) ? 'NULL' : (strlen((string)$val) > 60 ? substr($val, 0, 60) . '…' : $val) }}
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-xs text-gray-400">Query returned 0 rows.</p>
                @endif
            </div>
            @endif

        </div>
    </div>

</div>
@endsection

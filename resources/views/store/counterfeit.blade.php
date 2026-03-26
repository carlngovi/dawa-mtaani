@extends('layouts.app')
@section('title', 'Report Counterfeit — Dawa Mtaani')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Report Counterfeit</h1>
        <p class="text-sm text-gray-500 mt-1">Help protect public health by reporting suspected counterfeit medicines</p>
    </div>

    {{-- Info --}}
    <div class="bg-amber-50 border border-amber-200 text-amber-800 text-sm px-4 py-3 rounded-lg">
        Reports are reviewed by the Network Admin team. Confirmed counterfeits are escalated to the
        Pharmacy and Poisons Board (PPB) for investigation.
    </div>

    {{-- Report form --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Submit a Report</h3>

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="/store/report/counterfeit" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Pharmacy where purchased</label>
                <select name="facility_id" required
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Select pharmacy...</option>
                    @php
                        $facilities = \Illuminate\Support\Facades\DB::table('facilities')
                            ->where('facility_status', 'ACTIVE')
                            ->whereNull('deleted_at')
                            ->orderBy('facility_name')
                            ->select(['id', 'facility_name', 'county'])
                            ->limit(200)
                            ->get();
                    @endphp
                    @foreach($facilities as $f)
                        <option value="{{ $f->id }}" {{ old('facility_id') == $f->id ? 'selected' : '' }}>
                            {{ $f->facility_name }} — {{ $f->county }}
                        </option>
                    @endforeach
                </select>
                @error('facility_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Medicine</label>
                <select name="product_id" required
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Select medicine...</option>
                    @php
                        $products = \Illuminate\Support\Facades\DB::table('products')
                            ->where('is_active', true)
                            ->orderBy('generic_name')
                            ->select(['id', 'generic_name', 'brand_name'])
                            ->limit(500)
                            ->get();
                    @endphp
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->generic_name }}{{ $p->brand_name ? ' — ' . $p->brand_name : '' }}
                        </option>
                    @endforeach
                </select>
                @error('product_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Describe the issue (min 10 characters)</label>
                <textarea name="report_notes" rows="4" required minlength="10" maxlength="1000"
                          placeholder="Describe what makes you suspect this medicine is counterfeit — packaging differences, side effects, appearance..."
                          class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">{{ old('report_notes') }}</textarea>
                @error('report_notes') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit"
                    class="px-4 py-2.5 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800 transition-colors">
                Submit Report
            </button>
        </form>
    </div>

    {{-- Previous reports --}}
    @if($reports->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Your Reports</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Medicine</th>
                        <th class="px-5 py-3 text-left hidden md:table-cell">Pharmacy</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left hidden lg:table-cell">Submitted</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($reports as $report)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-800">{{ $report->generic_name ?? '—' }}</p>
                            @if($report->brand_name)
                                <p class="text-xs text-gray-400">{{ $report->brand_name }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-gray-500 hidden md:table-cell">
                            {{ $report->facility_name ?? '—' }}
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $badge = match($report->status) {
                                    'SUBMITTED'    => 'bg-amber-100 text-amber-700',
                                    'INVESTIGATING'=> 'bg-blue-100 text-blue-700',
                                    'CONFIRMED'    => 'bg-red-100 text-red-700',
                                    'DISMISSED'    => 'bg-gray-100 text-gray-500',
                                    default        => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                                {{ $report->status }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                            {{ \Carbon\Carbon::parse($report->created_at)->timezone('Africa/Nairobi')->format('d M Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div>{{ $reports->links() }}</div>
    @endif

</div>
@endsection
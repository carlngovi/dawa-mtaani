@extends('layouts.app')
@section('title', 'Quality Reports — Dawa Mtaani')
@section('content')
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900 dark:text-white">Quality Reports</h1><p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Report suspected counterfeit or quality issues</p></div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-4">Submit a Quality Report</h3>
            <form method="POST" action="/api/v1/quality-flags" class="space-y-4"
                  x-data="{}" @submit.prevent="
                    fetch('/api/v1/quality-flags', {
                        method: 'POST',
                        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'},
                        body: JSON.stringify({product_id:parseInt($el.querySelector('[name=product_id]').value),flag_type:$el.querySelector('[name=flag_type]').value,notes:$el.querySelector('[name=notes]').value})
                    }).then(r=>r.json()).then(d=>{alert(d.message||'Submitted');location.reload()}).catch(()=>alert('Error submitting'))
                  ">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Product *</label>
                    <select name="product_id" required class="h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-sm text-gray-800 dark:text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10">
                        <option value="">— Select —</option>
                        @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->generic_name }} ({{ $p->sku_code }})</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Issue Type *</label>
                    <select name="flag_type" required class="h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 text-sm text-gray-800 dark:text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10">
                        <option value="">— Select —</option>
                        <option value="SUSPECTED_COUNTERFEIT">Suspected Counterfeit</option>
                        <option value="PACKAGING_ANOMALY">Packaging Anomaly</option>
                        <option value="LABELLING_CONCERN">Labelling Concern</option>
                        <option value="QUALITY_DEGRADATION">Quality Degradation</option>
                        <option value="OTHER">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Description</label>
                    <textarea name="notes" rows="3" placeholder="Describe the issue..." class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-transparent px-3 py-2 text-sm text-gray-800 dark:text-white placeholder:text-gray-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10 resize-none"></textarea>
                </div>
                <button type="submit" class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">Submit Report</button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800"><h3 class="text-sm font-semibold text-gray-800 dark:text-white">My Submitted Reports</h3></div>
            <div class="overflow-x-auto"><table class="w-full text-sm min-w-[560px]">
                <thead class="bg-gray-50 dark:bg-gray-800 text-xs text-gray-500 uppercase"><tr><th class="px-4 py-3 text-left">Product</th><th class="px-4 py-3 text-left">Type</th><th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Date</th></tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($myFlags as $flag)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3"><p class="font-medium text-gray-800 dark:text-white">{{ $flag->generic_name ?? 'Unknown' }}</p></td>
                        <td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">{{ str_replace('_', ' ', $flag->flag_type) }}</span></td>
                        <td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ match($flag->status) { 'OPEN'=>'bg-amber-100 text-amber-700','UNDER_REVIEW'=>'bg-blue-100 text-blue-700','CONFIRMED'=>'bg-red-100 text-red-700',default=>'bg-gray-100 text-gray-500' } }}">{{ str_replace('_', ' ', $flag->status) }}</span></td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ \Carbon\Carbon::parse($flag->created_at)->format('d M Y') }}</td>
                    </tr>
                    @empty<tr><td colspan="4" class="px-4 py-10 text-center text-gray-400 text-sm">No reports yet</td></tr>@endforelse
                </tbody>
            </table></div>
            <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-800">{{ $myFlags->links() }}</div>
        </div>
    </div>
</div>
@endsection

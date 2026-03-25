@extends('layouts.retail')
@section('title', 'Quality Reports — Dawa Mtaani')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Quality Reports</h1>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Report a Quality Concern</h3>
        <form x-data="{}" @submit.prevent="
            fetch('/api/v1/quality-flags', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                body: JSON.stringify({
                    product_id: parseInt(document.getElementById('qf_product').value),
                    flag_type: document.getElementById('qf_type').value,
                    notes: document.getElementById('qf_notes').value,
                })
            }).then(r => r.json()).then(d => alert(d.message))
        " class="space-y-3">
            <input type="number" id="qf_product" placeholder="Product ID"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <select id="qf_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="SUSPECTED_COUNTERFEIT">Suspected Counterfeit</option>
                <option value="PACKAGING_ANOMALY">Packaging Anomaly</option>
                <option value="LABELLING_CONCERN">Labelling Concern</option>
                <option value="QUALITY_DEGRADATION">Quality Degradation</option>
                <option value="OTHER">Other</option>
            </select>
            <textarea id="qf_notes" rows="3" placeholder="Additional notes..."
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
            <button type="submit" class="px-5 py-2 bg-green-700 text-white text-sm rounded-lg hover:bg-green-800">
                Submit Report
            </button>
        </form>
    </div>
</div>
@endsection

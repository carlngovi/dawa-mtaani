@extends('layouts.admin')
@section('title', 'Categories — Dawa Mtaani')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Product Categories</h1>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($categories as $cat)
        <a href="/admin/products?category={{ urlencode($cat->therapeutic_category) }}"
           class="bg-white rounded-xl border border-gray-200 p-5 hover:border-green-300 transition-colors">
            <p class="text-sm font-semibold text-gray-800">{{ $cat->therapeutic_category }}</p>
            <p class="text-2xl font-bold text-green-700 mt-2">{{ $cat->product_count }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $cat->active_count }} active</p>
        </a>
        @endforeach
    </div>
</div>
@endsection

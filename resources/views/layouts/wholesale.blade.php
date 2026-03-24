@extends('layouts.app')

@section('nav')
    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Portal</p>
    <a href="/wholesale/orders" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('wholesale/orders*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Order Queue
    </a>
    <a href="/wholesale/price-lists" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('wholesale/price-lists*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Price Lists
    </a>
    <a href="/wholesale/stock" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('wholesale/stock*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Stock Management
    </a>
    <a href="/wholesale/performance" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('wholesale/performance*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Performance
    </a>
@endsection

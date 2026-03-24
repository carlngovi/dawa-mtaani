@extends('layouts.app')

@section('nav')
    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">My Pharmacy</p>
    <a href="/retail/dashboard" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('retail/dashboard') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Dashboard
    </a>
    <a href="/retail/catalogue" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('retail/catalogue*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Order Medicines
    </a>
    <a href="/retail/orders" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('retail/orders*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        My Orders
    </a>
    <a href="/retail/favourites" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('retail/favourites*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Favourites
    </a>

    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-6 mb-2">POS</p>
    <a href="/retail/pos" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('retail/pos*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Point of Sale
    </a>
    <a href="/retail/stock" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('retail/stock*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Stock Intelligence
    </a>

    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-6 mb-2">Credit</p>
    <a href="/retail/credit" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('retail/credit*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Credit Dashboard
    </a>
    <a href="/retail/lpo" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('retail/lpo*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        LPO Requests
    </a>

    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-6 mb-2">Reports</p>
    <a href="/retail/quality-flags" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('retail/quality-flags*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Quality Reports
    </a>
@endsection

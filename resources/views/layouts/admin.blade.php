@extends('layouts.app')

@section('nav')
    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Overview</p>
    <a href="/admin/dashboard" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('admin/dashboard') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Dashboard
    </a>
    <a href="/admin/facilities" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('admin/facilities*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Facilities
    </a>
    <a href="/admin/orders" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('admin/orders*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Orders
    </a>

    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-6 mb-2">Operations</p>
    <a href="/admin/flags" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('admin/flags*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Facility Flags
    </a>
    <a href="/admin/disputes" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('admin/disputes*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Disputes
    </a>
    <a href="/admin/quality-flags" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('admin/quality-flags*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Quality Flags
    </a>
    <a href="/admin/ppb-registry" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('admin/ppb-registry*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        PPB Registry
    </a>

    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-6 mb-2">Credit & Finance</p>
    <a href="/admin/credit" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('admin/credit*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Credit Config
    </a>
    <a href="/admin/reports" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('admin/reports*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Reports
    </a>

    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-6 mb-2">System</p>
    <a href="/admin/monitoring" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('admin/monitoring*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Monitoring
    </a>
    <a href="/admin/security" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('admin/security*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Security Events
    </a>
    <a href="/admin/audit-log" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('admin/audit-log*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Audit Log
    </a>
    <a href="/admin/recruiter" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('admin/recruiter*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Recruiter Firms
    </a>
    <a href="/admin/dpa" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg
        {{ request()->is('admin/dpa*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
        Data & DPA
    </a>
@endsection

@extends('layouts.app')

@section('title', $portalTitle . ' — Dawa Mtaani')

@section('content')
<div class="page-header">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white dark:text-white tracking-tight">
            {{ $portalTitle }}
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ $portalSubtitle ?? 'This module is being built by Datanav.' }}
        </p>
    </div>
    <span class="badge-yellow px-3 py-1 rounded-full text-xs font-semibold">
        In Development
    </span>
</div>

<div class="section-card p-8 text-center space-y-4">
    <div class="h-16 w-16 rounded-2xl bg-yellow-400/10 flex items-center justify-center mx-auto">
        <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
        </svg>
    </div>
    <div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white dark:text-white">
            {{ $portalTitle }} is under construction
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-md mx-auto">
            Business logic for this portal is being wired by Datanav.
            The routes, middleware, role enforcement, and session security
            are all active — only the data layer is pending.
        </p>
    </div>
    <div class="pt-2 space-y-1">
        <p class="text-xs text-gray-400">Logged in as:
            <span class="font-mono text-yellow-500">{{ auth()->user()->email }}</span>
        </p>
        <p class="text-xs text-gray-400">Role:
            <span class="font-mono text-yellow-500">{{ auth()->user()->getRoleNames()->first() }}</span>
        </p>
        <p class="text-xs text-gray-400">Portal:
            <span class="font-mono text-yellow-500">{{ request()->path() }}</span>
        </p>
    </div>
</div>
@endsection

@extends('layouts.retail')
@section('title', 'Favourites — Dawa Mtaani')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Favourites</h1>
    <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
        <p class="text-gray-500 text-sm">Your starred products will appear here.</p>
        <a href="/retail/catalogue" class="mt-3 inline-block text-sm text-green-700 hover:underline">Browse catalogue →</a>
    </div>
</div>
@endsection

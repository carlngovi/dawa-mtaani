@extends('layouts.admin')

@section('title', 'Generate Activation Code — Dawa Mtaani')

@section('content')

<div class="space-y-6 max-w-lg">

    <h1 class="text-2xl font-bold text-white">Generate Activation Code</h1>

    @if(session('generated_code'))
        <div class="bg-gray-800 border-2 border-yellow-400 rounded-lg p-6">
            <p class="text-sm text-gray-400 mb-2">Activation Code</p>
            <div class="flex items-center gap-3">
                <span id="activation-code" class="text-2xl font-mono font-bold text-yellow-400 tracking-widest">{{ session('generated_code') }}</span>
                <button onclick="navigator.clipboard.writeText(document.getElementById('activation-code').textContent)"
                        class="px-3 py-1 bg-gray-700 text-gray-300 rounded text-xs hover:bg-gray-600 transition">
                    Copy
                </button>
            </div>
            <p class="text-sm text-gray-400 mt-3">Expires: {{ session('expires_at') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.spotter.activations.store') }}" class="bg-gray-800 border border-gray-700 rounded-lg p-6 space-y-4">
        @csrf

        <div>
            <label for="spotter_user_id" class="block text-sm text-gray-400 mb-1">Spotter</label>
            <select name="spotter_user_id" id="spotter_user_id"
                    class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-yellow-400 focus:border-yellow-400">
                <option value="">Select a field agent…</option>
                @foreach($spotters as $s)
                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->id }})</option>
                @endforeach
            </select>
            @error('spotter_user_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="expiry_hours" class="block text-sm text-gray-400 mb-1">Expiry (hours)</label>
            <input type="number" name="expiry_hours" id="expiry_hours" value="72" min="1" max="168"
                   class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-yellow-400 focus:border-yellow-400">
            @error('expiry_hours') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit"
                class="w-full px-4 py-2 bg-yellow-400/20 text-yellow-400 rounded-lg text-sm font-medium hover:bg-yellow-400/30 transition">
            Generate Code
        </button>
    </form>

</div>

@endsection

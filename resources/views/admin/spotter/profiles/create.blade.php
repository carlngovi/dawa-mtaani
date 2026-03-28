@extends('layouts.admin')

@section('title', 'Create Spotter Profile — Dawa Mtaani')

@section('content')

<div class="max-w-2xl space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-white">Create Spotter Profile</h1>
        <p class="text-sm text-gray-400 mt-1">Assign county, ward, and sales rep to a field agent</p>
    </div>

    @if($errors->any())
        <div class="bg-red-400/10 border border-gray-700/30 text-red-400 rounded-lg px-4 py-3 text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.spotter.profiles.store') }}" method="POST" class="bg-gray-800 border border-gray-700 rounded-lg p-6 space-y-5">
        @csrf

        <div>
            <label class="text-gray-400 text-xs mb-1 block">Spotter <span class="text-red-400">*</span></label>
            <select name="user_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:border-yellow-400 focus:outline-none">
                <option value="">Select a field agent...</option>
                @foreach($spottersWithoutProfile as $s)
                    <option value="{{ $s->id }}" {{ (old('user_id', request('user_id')) == $s->id) ? 'selected' : '' }}>
                        {{ $s->name }} ({{ $s->email }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-gray-400 text-xs mb-1 block">County <span class="text-red-400">*</span></label>
                <input type="text" name="county" value="{{ old('county') }}" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:border-yellow-400 focus:outline-none" placeholder="e.g. Nairobi">
            </div>
            <div>
                <label class="text-gray-400 text-xs mb-1 block">Ward <span class="text-red-400">*</span></label>
                <input type="text" name="ward" value="{{ old('ward') }}" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:border-yellow-400 focus:outline-none" placeholder="e.g. Westlands">
            </div>
        </div>

        <div>
            <label class="text-gray-400 text-xs mb-1 block">Sales Rep</label>
            <select name="sales_rep_user_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:border-yellow-400 focus:outline-none">
                <option value="">None</option>
                @foreach($salesReps as $rep)
                    <option value="{{ $rep->id }}" {{ old('sales_rep_user_id') == $rep->id ? 'selected' : '' }}>
                        {{ $rep->name }} ({{ $rep->email }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-gray-400 text-xs mb-1 block">Notes</label>
            <textarea name="notes" rows="3" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:border-yellow-400 focus:outline-none resize-none" placeholder="Optional notes...">{{ old('notes') }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-yellow-400 text-white font-bold px-6 py-2.5 rounded-xl text-sm hover:bg-yellow-300 transition">Save Profile</button>
            <a href="{{ route('admin.spotter.profiles.index') }}" class="border border-gray-600 text-gray-300 px-6 py-2.5 rounded-xl text-sm hover:bg-gray-700 transition">Cancel</a>
        </div>
    </form>

</div>

@endsection

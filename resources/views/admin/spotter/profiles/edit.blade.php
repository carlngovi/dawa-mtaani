@extends('layouts.admin')

@section('title', 'Edit Spotter Profile — Dawa Mtaani')

@section('content')

<div class="max-w-2xl space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-white">Edit Spotter Profile</h1>
        <p class="text-sm text-gray-400 mt-1">{{ $profile->user->name }} — {{ $profile->user->email }}</p>
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

    <form action="{{ route('admin.spotter.profiles.update', $profile) }}" method="POST" class="bg-gray-800 border border-gray-700 rounded-lg p-6 space-y-5">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-gray-400 text-xs mb-1 block">County <span class="text-red-400">*</span></label>
                <input type="text" name="county" value="{{ old('county', $profile->county) }}" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:border-yellow-400 focus:outline-none">
            </div>
            <div>
                <label class="text-gray-400 text-xs mb-1 block">Ward <span class="text-red-400">*</span></label>
                <input type="text" name="ward" value="{{ old('ward', $profile->ward) }}" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:border-yellow-400 focus:outline-none">
            </div>
        </div>

        <div>
            <label class="text-gray-400 text-xs mb-1 block">Sales Rep</label>
            <select name="sales_rep_user_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:border-yellow-400 focus:outline-none">
                <option value="">None</option>
                @foreach($salesReps as $rep)
                    <option value="{{ $rep->id }}" {{ old('sales_rep_user_id', $profile->sales_rep_user_id) == $rep->id ? 'selected' : '' }}>
                        {{ $rep->name }} ({{ $rep->email }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-gray-400 text-xs mb-1 block">Active</label>
            <div class="flex gap-3">
                <label class="flex items-center gap-2 text-gray-300 text-sm cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $profile->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-600 bg-gray-900 text-yellow-400 focus:ring-yellow-400">
                    Profile is active
                </label>
            </div>
        </div>

        <div>
            <label class="text-gray-400 text-xs mb-1 block">Notes</label>
            <textarea name="notes" rows="3" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:border-yellow-400 focus:outline-none resize-none">{{ old('notes', $profile->notes) }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-yellow-400 text-white font-bold px-6 py-2.5 rounded-xl text-sm hover:bg-yellow-300 transition">Update Profile</button>
            <a href="{{ route('admin.spotter.profiles.index') }}" class="border border-gray-600 text-gray-300 px-6 py-2.5 rounded-xl text-sm hover:bg-gray-700 transition">Cancel</a>
        </div>
    </form>

</div>

@endsection

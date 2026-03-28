<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SpotterProfile;
use App\Models\User;
use Illuminate\Http\Request;

class SpotterProfileController extends Controller
{
    public function index()
    {
        $spotters = User::role('network_field_agent')
            ->with('spotterProfile')
            ->orderBy('name')
            ->get();

        return view('admin.spotter.profiles.index', compact('spotters'));
    }

    public function create()
    {
        $spottersWithoutProfile = User::role('network_field_agent')
            ->whereDoesntHave('spotterProfile')
            ->orderBy('name')
            ->get();

        $salesReps = User::role('sales_rep')->orderBy('name')->get();

        return view('admin.spotter.profiles.create', compact('spottersWithoutProfile', 'salesReps'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:spotter_profiles,user_id',
            'county' => 'required|string|max:100',
            'ward' => 'required|string|max:100',
            'sales_rep_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        SpotterProfile::create($validated);

        return redirect()->route('admin.spotter.profiles.index')
            ->with('success', 'Spotter profile created successfully.');
    }

    public function edit(SpotterProfile $profile)
    {
        $salesReps = User::role('sales_rep')->orderBy('name')->get();

        return view('admin.spotter.profiles.edit', compact('profile', 'salesReps'));
    }

    public function update(Request $request, SpotterProfile $profile)
    {
        $validated = $request->validate([
            'county' => 'required|string|max:100',
            'ward' => 'required|string|max:100',
            'sales_rep_user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $profile->update($validated);

        return redirect()->route('admin.spotter.profiles.index')
            ->with('success', 'Spotter profile updated successfully.');
    }
}

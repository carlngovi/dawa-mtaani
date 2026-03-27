<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SpotterActivationCode;
use App\Models\User;
use App\Services\SpotterActivationService;
use Illuminate\Http\Request;

class SpotterActivationController extends Controller
{
    public function index()
    {
        $codes = SpotterActivationCode::with('spotter', 'creator')
            ->latest()
            ->paginate(25);

        return view('admin.spotter.activations.index', compact('codes'));
    }

    public function create()
    {
        $spotters = User::role('network_field_agent')->get(['id', 'name']);

        return view('admin.spotter.activations.create', compact('spotters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'spotter_user_id' => 'required|exists:users,id',
            'expiry_hours' => 'nullable|integer|min:1|max:168',
        ]);

        $code = app(SpotterActivationService::class)->generateCode(
            auth()->user(),
            User::find($request->spotter_user_id),
            $request->expiry_hours ?? 72,
        );

        $display = implode('-', str_split($code->code, 4));

        return redirect()->back()->with([
            'generated_code' => $display,
            'expires_at' => $code->expires_at->format('d M Y H:i'),
        ]);
    }
}

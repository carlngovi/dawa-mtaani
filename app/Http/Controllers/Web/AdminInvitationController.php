<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RoleAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * AdminInvitationController
 *
 * Manages the invitation-only registration system.
 * Only network_admin / admin / super_admin can create invitations.
 * Invitees register via a signed token URL — no public registration.
 */
class AdminInvitationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['network_admin', 'admin', 'super_admin'])) {
            abort(403);
        }

        $invitations = DB::table('invitations')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.invitations', compact('invitations'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['network_admin', 'admin', 'super_admin'])) {
            abort(403);
        }

        $validated = $request->validate([
            'email'      => 'required|email|unique:users,email',
            'role'       => 'required|string|in:retail_facility,wholesale_facility,logistics_facility,group_owner,network_field_agent,sales_rep,admin_support,assistant_admin,shared_accountant,admin',
            'facility_id' => 'nullable|exists:facilities,id',
        ]);

        $token = Str::random(64);

        DB::table('invitations')->insert([
            'email'       => $validated['email'],
            'role'        => $validated['role'],
            'facility_id' => $validated['facility_id'] ?? null,
            'token'       => $token,
            'invited_by'  => $user->id,
            'created_at'  => now(),
            'expires_at'  => now()->addDays(7),
        ]);

        // In production: dispatch a mail job with the acceptance URL.
        // URL format: /register/accept/{token}
        // For now: flash the link so admin can copy it manually.
        $acceptUrl = url('/register/accept/' . $token);

        return back()->with('success', "Invitation created. Acceptance link: {$acceptUrl}");
    }

    public function destroy(int $id)
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['network_admin', 'admin', 'super_admin'])) {
            abort(403);
        }

        DB::table('invitations')->where('id', $id)->delete();

        return back()->with('success', 'Invitation deleted.');
    }

    public function showAccept(string $token)
    {
        $invitation = DB::table('invitations')
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->whereNull('accepted_at')
            ->first();

        if (! $invitation) {
            return redirect('/login')->with('status', 'This invitation is invalid or has expired.');
        }

        return view('auth.register', compact('invitation', 'token'));
    }

    public function acceptStore(Request $request, string $token)
    {
        $invitation = DB::table('invitations')
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->whereNull('accepted_at')
            ->first();

        if (! $invitation) {
            return redirect('/login')->with('status', 'This invitation is invalid or has expired.');
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $userModel = User::create([
            'name'        => $validated['name'],
            'email'       => $invitation->email,
            'password'    => Hash::make($validated['password']),
            'facility_id' => $invitation->facility_id,
        ]);

        $userModel->syncRoles([$invitation->role]);

        DB::table('invitations')
            ->where('token', $token)
            ->update(['accepted_at' => now()]);

        auth()->login($userModel);

        return redirect('/dashboard');
    }
}

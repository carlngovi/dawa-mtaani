<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerRegistrationController extends Controller
{
    public function create()
    {
        return view('auth.register-customer');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'password'   => ['required', 'min:8', 'confirmed'],
            'terms'      => ['accepted'],
        ]);

        $fullName = $validated['first_name'] . ' ' . $validated['last_name'];

        $user = DB::transaction(function () use ($validated, $fullName) {
            $user = User::create([
                'name'              => $fullName,
                'email'             => $validated['email'],
                'password'          => Hash::make($validated['password']),
                'email_verified_at' => now(),
            ]);

            $user->assignRole('customer');

            return $user;
        });

        Auth::login($user, remember: true);

        return redirect('/store');
    }
}

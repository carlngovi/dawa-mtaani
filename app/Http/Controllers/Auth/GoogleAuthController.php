<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Google login failed. Please try again.']);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            return redirect('/login')->withErrors([
                'email' => 'No account found for ' . $googleUser->getEmail() . '. Please contact your administrator.',
            ]);
        }

        Auth::login($user, true);

        $user->update(['last_login_at' => now('UTC')]);

        // Redirect based on role
        if ($user->hasRole(['network_admin', 'network_field_agent'])) {
            return redirect('/admin/dashboard');
        }
        if ($user->hasRole('wholesale_facility')) {
            return redirect('/wholesale/orders');
        }
        return redirect('/retail/dashboard');
    }
}

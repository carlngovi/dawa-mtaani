<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\RoleRedirectController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
            return redirect()->route('login')->with('status', 'Google sign-in failed. Please try again.');
        }

        // Lookup by google_id first, then fall back to email
        $user = User::where('google_id', $googleUser->getId())->first()
             ?? User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            return redirect()->route('login')->with(
                'status',
                'No account found for this Google address. Contact your administrator.'
            );
        }

        // Link Google profile if not yet set
        if (! $user->google_id) {
            $user->update([
                'google_id'        => $googleUser->getId(),
                'google_avatar'    => $googleUser->getAvatar(),
                'google_linked_at' => now(),
            ]);
        }

        Auth::login($user, remember: true);

        return RoleRedirectController::redirectForUser($user);
    }

    public function facilityRedirect()
    {
        session(['google_intent' => 'facility_registration']);

        return Socialite::driver('google')
            ->redirectUrl(route('auth.google.facility.callback'))
            ->redirect();
    }

    public function facilityCallback()
    {
        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl(route('auth.google.facility.callback'))
                ->user();
        } catch (\Exception $e) {
            return redirect()->route('register.facility')
                ->with('status', 'Google sign-in failed. Please try again.');
        }

        if (User::where('email', $googleUser->getEmail())->exists()) {
            return redirect()->route('register.facility')->withErrors([
                'email' => 'An account with this Google address already exists. Please sign in.',
            ]);
        }

        session(['google_prefill' => [
            'name'      => $googleUser->getName(),
            'email'     => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'avatar'    => $googleUser->getAvatar(),
        ]]);

        return redirect()->route('register.facility');
    }

    public function patientRedirect()
    {
        session(['google_intent' => 'patient_registration']);

        return Socialite::driver('google')
            ->redirectUrl(route('auth.google.patient.callback'))
            ->redirect();
    }

    public function patientCallback()
    {
        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl(route('auth.google.patient.callback'))
                ->user();
        } catch (\Exception $e) {
            return redirect()->route('register.patient')
                ->with('status', 'Google sign-up failed. Please try again.');
        }

        $existingUser = User::where('email', $googleUser->getEmail())->first();

        if ($existingUser) {
            if ($existingUser->hasRole('patient')) {
                if (! $existingUser->google_id) {
                    $existingUser->update([
                        'google_id'        => $googleUser->getId(),
                        'google_avatar'    => $googleUser->getAvatar(),
                        'google_linked_at' => now(),
                    ]);
                }

                Auth::login($existingUser, remember: true);

                return redirect('/store');
            }

            return redirect()->route('login')->withErrors([
                'email' => 'An account with this email already exists. Please sign in.',
            ]);
        }

        $user = \Illuminate\Support\Facades\DB::transaction(function () use ($googleUser) {
            $user = User::create([
                'name'              => $googleUser->getName(),
                'email'             => $googleUser->getEmail(),
                'google_id'         => $googleUser->getId(),
                'google_avatar'     => $googleUser->getAvatar(),
                'google_linked_at'  => now(),
                'email_verified_at' => now(),
                'password'          => null,
            ]);

            $user->assignRole('patient');

            return $user;
        });

        Auth::login($user, remember: true);

        return redirect('/store');
    }
}

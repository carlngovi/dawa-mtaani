<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\RoleRedirectController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        $redirectUrl = config('services.google.redirect');
        Log::info('Google Login Redirect', ['redirect_url' => $redirectUrl]);
        
        return Socialite::driver('google')
            ->redirectUrl($redirectUrl)
            ->redirect();
    }

    public function callback()
    {
        try {
            $redirectUrl = config('services.google.redirect');
            Log::info('Google Login Callback Started', [
                'redirect_url' => $redirectUrl,
                'request_url' => request()->fullUrl(),
                'all_params' => request()->all()
            ]);
            
            $googleUser = Socialite::driver('google')
                ->redirectUrl($redirectUrl)
                ->user();
                
            Log::info('Google User Retrieved', ['email' => $googleUser->getEmail(), 'id' => $googleUser->getId()]);
                
        } catch (\Exception $e) {
            Log::error('Google OAuth Callback Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('login')->with('status', 'Google sign-in failed. Please try again. Error: ' . $e->getMessage());
        }

        // Lookup by google_id first, then fall back to email
        $user = User::where('google_id', $googleUser->getId())->first()
             ?? User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            Log::warning('No user found for Google account', ['email' => $googleUser->getEmail()]);
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
            Log::info('Linked Google account to existing user', ['user_id' => $user->id, 'email' => $user->email]);
        }

        Auth::login($user, remember: true);
        
        $redirectTo = RoleRedirectController::redirectForUser($user);
        Log::info('User logged in, redirecting to', ['url' => $redirectTo->getTargetUrl()]);

        return $redirectTo;
    }

    public function facilityRedirect()
    {
        session(['google_intent' => 'facility_registration']);

        $redirectUrl = config('app.url') . '/auth/google/facility/callback';
        Log::info('Facility Google Redirect', ['redirect_url' => $redirectUrl]);

        return Socialite::driver('google')
            ->redirectUrl($redirectUrl)
            ->redirect();
    }

    public function facilityCallback()
    {
        try {
            $redirectUrl = config('app.url') . '/auth/google/facility/callback';
            
            Log::info('Facility Google Callback Started', [
                'redirect_url' => $redirectUrl,
                'request_url' => request()->fullUrl(),
                'all_params' => request()->all()
            ]);
            
            $googleUser = Socialite::driver('google')
                ->redirectUrl($redirectUrl)
                ->user();
                
            Log::info('Facility Google User Retrieved', ['email' => $googleUser->getEmail()]);
                
        } catch (\Exception $e) {
            Log::error('Google Facility OAuth Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('register.facility')
                ->with('status', 'Google sign-in failed. Please try again. Error: ' . $e->getMessage());
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

        $redirectUrl = config('app.url') . '/auth/google/patient/callback';
        Log::info('Patient Google Redirect', ['redirect_url' => $redirectUrl]);

        return Socialite::driver('google')
            ->redirectUrl($redirectUrl)
            ->redirect();
    }

    public function patientCallback()
    {
        try {
            $redirectUrl = config('app.url') . '/auth/google/patient/callback';
            
            Log::info('Patient Google Callback Started', [
                'redirect_url' => $redirectUrl,
                'request_url' => request()->fullUrl(),
                'all_params' => request()->all()
            ]);
            
            $googleUser = Socialite::driver('google')
                ->redirectUrl($redirectUrl)
                ->user();
                
            Log::info('Patient Google User Retrieved', ['email' => $googleUser->getEmail()]);
                
        } catch (\Exception $e) {
            Log::error('Google Patient OAuth Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('register.patient')
                ->with('status', 'Google sign-up failed. Please try again. Error: ' . $e->getMessage());
        }

        $existingUser = User::where('email', $googleUser->getEmail())->first();

        if ($existingUser) {
            Log::info('Existing user found', [
                'user_id' => $existingUser->id, 
                'email' => $existingUser->email,
                'role' => $existingUser->roles->pluck('name')->toArray(),
                'has_patient_role' => $existingUser->hasRole('patient')
            ]);
            
            if ($existingUser->hasRole('patient')) {
                if (! $existingUser->google_id) {
                    $existingUser->update([
                        'google_id'        => $googleUser->getId(),
                        'google_avatar'    => $googleUser->getAvatar(),
                        'google_linked_at' => now(),
                    ]);
                    Log::info('Linked Google account to existing patient', ['user_id' => $existingUser->id]);
                }

                Auth::login($existingUser, remember: true);
                
                $redirectUrl = url('/store');
                Log::info('Existing patient logged in, redirecting to', ['url' => $redirectUrl]);
                
                return redirect($redirectUrl);
            }

            Log::warning('Existing user found but not a patient', [
                'user_id' => $existingUser->id,
                'role' => $existingUser->roles->pluck('name')->toArray()
            ]);
            
            return redirect()->route('login')->withErrors([
                'email' => 'An account with this email already exists. Please sign in.',
            ]);
        }

        Log::info('Creating new patient user');
        
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
            
            Log::info('New patient created', [
                'user_id' => $user->id, 
                'email' => $user->email,
                'name' => $user->name
            ]);

            return $user;
        });

        Auth::login($user, remember: true);
        
        // TEMPORARY: Redirect to test page instead of store to debug
        // Comment this line and uncomment the next line to test
        $redirectUrl = url('/store');
        // $redirectUrl = url('/google-test-success');
        
        Log::info('New patient logged in, redirecting to', ['url' => $redirectUrl]);

        return redirect($redirectUrl);
    }
}
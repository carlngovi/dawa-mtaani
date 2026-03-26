<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\PpbReverificationJob;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class FacilityRegistrationController extends Controller
{
    public function create()
    {
        $counties = DB::table('kenya_counties')->orderBy('name')->get();

        $prefill = session('google_prefill');

        return view('auth.register-facility', compact('counties', 'prefill'));
    }

    public function store(Request $request)
    {
        $googlePrefill = session('google_prefill');
        $isGoogle = ! empty($googlePrefill);

        $rules = [
            'name'                => ['required', 'string', 'max:255'],
            'ppb_licence_number'  => ['required', 'string', 'unique:facilities,ppb_licence_number'],
            'facility_name'      => ['required', 'string', 'max:255'],
            'phone'              => ['required', 'string', 'regex:/^\+254[17]\d{8}$/', 'unique:facilities,phone'],
            'email'              => ['required', 'email', 'unique:users,email'],
            'county'             => ['required', 'exists:kenya_counties,code'],
            'terms'              => ['accepted'],
        ];

        if (! $isGoogle) {
            $rules['password'] = ['required', 'min:8', 'confirmed'];
        }

        $validated = $request->validate($rules);

        $countyName = DB::table('kenya_counties')
            ->where('code', $validated['county'])
            ->value('name');

        try {
            $result = DB::transaction(function () use ($validated, $isGoogle, $googlePrefill, $countyName, $request) {
                $userData = [
                    'name'  => $validated['name'],
                    'email' => $validated['email'],
                ];

                if ($isGoogle) {
                    $userData['password'] = Hash::make(Str::random(32));
                    $userData['google_id'] = $googlePrefill['google_id'];
                    $userData['google_avatar'] = $googlePrefill['avatar'] ?? null;
                    $userData['google_linked_at'] = now();
                } else {
                    $userData['password'] = Hash::make($validated['password']);
                }

                $user = User::create($userData);
                $user->assignRole('retail_facility');

                $facility = Facility::create([
                    'ulid'               => Str::ulid(),
                    'owner_name'         => $validated['name'],
                    'ppb_licence_number' => $validated['ppb_licence_number'],
                    'facility_name'      => $validated['facility_name'],
                    'phone'              => $validated['phone'],
                    'email'              => $validated['email'],
                    'county'             => $countyName,
                    'network_membership' => 'NETWORK',
                    'onboarding_status'  => 'APPLIED',
                    'facility_status'    => 'APPLIED',
                    'created_by'         => $user->id,
                ]);

                $user->facility_id = $facility->id;
                $user->save();

                PpbReverificationJob::dispatch($facility);

                DB::table('audit_logs')->insert([
                    'facility_id' => $facility->id,
                    'user_id'     => $user->id,
                    'action'      => 'FACILITY_SELF_REGISTERED',
                    'ip_address'  => $request->ip(),
                    'created_at'  => now(),
                ]);

                return $facility;
            });

            session()->forget('google_prefill');

            return redirect()->route('register.facility.pending')
                ->with('pending_facility_name', $result->facility_name);

        } catch (\Exception $e) {
            return back()->withInput()->withErrors([
                'general' => 'Registration failed. Please try again.',
            ]);
        }
    }

    public function pending()
    {
        $facilityName = session('pending_facility_name', 'your pharmacy');

        return view('auth.register-facility-pending', compact('facilityName'));
    }
}

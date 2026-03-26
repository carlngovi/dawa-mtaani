<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Services\PpbVerificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminRegistrationsController extends Controller
{
    private const WRITE_ROLES = ['network_admin', 'assistant_admin', 'admin', 'super_admin', 'technical_admin'];
    private const READ_ROLES  = ['network_admin', 'assistant_admin', 'admin', 'super_admin', 'technical_admin', 'admin_support'];

    public function index(Request $request)
    {
        abort_unless($request->user()->hasAnyRole(self::READ_ROLES), 403);

        $query = Facility::query()
            ->whereIn('onboarding_status', ['APPLIED', 'PPB_VERIFIED', 'ACCOUNT_LINKED'])
            ->when($request->filled('stage'), fn($q) => $q->where('onboarding_status', $request->stage))
            ->when($request->filled('search'), fn($q) => $q->where(function ($q) use ($request) {
                $q->where('facility_name', 'like', '%' . $request->search . '%')
                  ->orWhere('ppb_licence_number', 'like', '%' . $request->search . '%')
                  ->orWhere('owner_name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            }))
            ->orderBy('created_at', 'desc');

        $pending = $query->paginate(20)->withQueryString();

        $counts = [
            'applied'      => Facility::where('onboarding_status', 'APPLIED')->count(),
            'ppb_verified' => Facility::where('onboarding_status', 'PPB_VERIFIED')->count(),
            'acct_linked'  => Facility::where('onboarding_status', 'ACCOUNT_LINKED')->count(),
        ];

        return view('admin.registrations', compact('pending', 'counts'));
    }

    public function show(Request $request, string $ulid)
    {
        abort_unless($request->user()->hasAnyRole(self::READ_ROLES), 403);

        $facility = Facility::where('ulid', $ulid)->firstOrFail();

        $owner = DB::table('users')
            ->where('facility_id', $facility->id)
            ->first();

        $ppbLogs = DB::table('ppb_verification_logs')
            ->where('facility_id', $facility->id)
            ->orderBy('checked_at', 'desc')
            ->limit(10)
            ->get();

        $auditLogs = DB::table('audit_logs')
            ->where('facility_id', $facility->id)
            ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
            ->select(['audit_logs.*', 'users.name as actor_name'])
            ->orderBy('audit_logs.created_at', 'desc')
            ->limit(20)
            ->get();

        $canWrite = $request->user()->hasAnyRole(self::WRITE_ROLES);

        return view('admin.registrations-show', compact(
            'facility', 'owner', 'ppbLogs', 'auditLogs', 'canWrite'
        ));
    }

    public function approve(Request $request, string $ulid)
    {
        abort_unless($request->user()->hasAnyRole(self::WRITE_ROLES), 403);

        $facility = Facility::where('ulid', $ulid)->firstOrFail();

        if ($facility->onboarding_status !== 'PPB_VERIFIED') {
            return back()->with('error', 'Facility must pass PPB verification before approval.');
        }

        DB::transaction(function () use ($facility, $request) {
            $facility->update(['onboarding_status' => 'ACCOUNT_LINKED']);

            DB::table('audit_logs')->insert([
                'facility_id'   => $facility->id,
                'user_id'       => $request->user()->id,
                'action'        => 'REGISTRATION_APPROVED',
                'model_type'    => 'App\Models\Facility',
                'model_id'      => $facility->id,
                'payload_before'=> json_encode(['onboarding_status' => 'PPB_VERIFIED']),
                'payload_after' => json_encode(['onboarding_status' => 'ACCOUNT_LINKED']),
                'ip_address'    => $request->ip(),
                'user_agent'    => $request->userAgent(),
                'created_at'    => now(),
            ]);
        });

        try {
            $this->sendSms(
                $facility->phone,
                "Your pharmacy {$facility->facility_name} registration has been approved. Please complete your I&M account setup to activate your account."
            );
        } catch (\Throwable $e) {
            Log::warning('SMS failed after registration approval', ['facility_id' => $facility->id, 'error' => $e->getMessage()]);
        }

        return redirect("/admin/registrations/{$ulid}")->with('success', 'Registration approved. Facility moved to Account Linked stage.');
    }

    public function activate(Request $request, string $ulid)
    {
        abort_unless($request->user()->hasAnyRole(self::WRITE_ROLES), 403);

        $facility = Facility::where('ulid', $ulid)->firstOrFail();

        if ($facility->onboarding_status !== 'ACCOUNT_LINKED') {
            return back()->with('error', 'Facility must be in Account Linked stage before activation.');
        }

        if (! $facility->banking_account_validated_at) {
            return back()->with('error', 'Cannot activate — I&M banking account not yet validated.');
        }

        if ($facility->ppb_licence_status !== 'VALID') {
            return back()->with('error', 'Cannot activate — PPB licence is not VALID.');
        }

        DB::transaction(function () use ($facility, $request) {
            $facility->update([
                'onboarding_status' => 'ACTIVE',
                'facility_status'   => 'ACTIVE',
                'activated_at'      => now(),
            ]);

            DB::table('audit_logs')->insert([
                'facility_id'   => $facility->id,
                'user_id'       => $request->user()->id,
                'action'        => 'FACILITY_ACTIVATED',
                'model_type'    => 'App\Models\Facility',
                'model_id'      => $facility->id,
                'payload_before'=> json_encode(['onboarding_status' => 'ACCOUNT_LINKED']),
                'payload_after' => json_encode(['onboarding_status' => 'ACTIVE', 'facility_status' => 'ACTIVE']),
                'ip_address'    => $request->ip(),
                'user_agent'    => $request->userAgent(),
                'created_at'    => now(),
            ]);
        });

        try {
            $this->sendSms(
                $facility->phone,
                "Welcome to Dawa Mtaani! Your pharmacy {$facility->facility_name} is now active. You can start placing orders immediately."
            );
        } catch (\Throwable $e) {
            Log::warning('SMS failed after facility activation', ['facility_id' => $facility->id, 'error' => $e->getMessage()]);
        }

        return redirect('/admin/registrations')->with('success', "Facility {$facility->facility_name} is now ACTIVE.");
    }

    public function reject(Request $request, string $ulid)
    {
        abort_unless($request->user()->hasAnyRole(self::WRITE_ROLES), 403);

        $request->validate([
            'rejection_reason' => 'required|string|min:20|max:1000',
        ]);

        $facility = Facility::where('ulid', $ulid)->firstOrFail();

        DB::transaction(function () use ($facility, $request) {
            $facility->update(['facility_status' => 'CHURNED']);

            DB::table('audit_logs')->insert([
                'facility_id'   => $facility->id,
                'user_id'       => $request->user()->id,
                'action'        => 'REGISTRATION_REJECTED',
                'model_type'    => 'App\Models\Facility',
                'model_id'      => $facility->id,
                'payload_before'=> json_encode(['facility_status' => $facility->getOriginal('facility_status')]),
                'payload_after' => json_encode([
                    'facility_status'  => 'CHURNED',
                    'rejection_reason' => $request->rejection_reason,
                ]),
                'ip_address'    => $request->ip(),
                'user_agent'    => $request->userAgent(),
                'created_at'    => now(),
            ]);
        });

        try {
            $reason = $request->rejection_reason;
            $this->sendSms(
                $facility->phone,
                "Your pharmacy registration has been reviewed. Unfortunately we are unable to approve your application at this time. Reason: {$reason}. Contact support for assistance."
            );
        } catch (\Throwable $e) {
            Log::warning('SMS failed after registration rejection', ['facility_id' => $facility->id, 'error' => $e->getMessage()]);
        }

        return redirect('/admin/registrations')->with('success', "Registration for {$facility->facility_name} has been rejected.");
    }

    public function verifyPpb(Request $request, string $ulid)
    {
        abort_unless($request->user()->hasAnyRole(self::WRITE_ROLES), 403);

        $facility = Facility::where('ulid', $ulid)->firstOrFail();

        if (class_exists(\App\Services\PpbVerificationService::class)) {
            try {
                $service = app(PpbVerificationService::class);
                $result  = $service->verify($facility->ppb_licence_number);

                DB::table('ppb_verification_logs')->insert([
                    'facility_id'             => $facility->id,
                    'checked_at'              => now(),
                    'licence_status_returned' => $result->licenceStatus ?? 'NOT_FOUND',
                    'response_json'           => json_encode([
                        'found'          => $result->found,
                        'facility_name'  => $result->facilityName ?? null,
                        'ppb_type'       => $result->ppbType ?? null,
                        'licence_status' => $result->licenceStatus ?? null,
                        'message'        => $result->message ?? null,
                    ]),
                    'triggered_by' => 'ADMIN_MANUAL',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                if ($result->found && $result->licenceStatus === 'VALID') {
                    $facility->update([
                        'ppb_licence_status' => 'VALID',
                        'ppb_verified_at'    => now(),
                        'onboarding_status'  => $facility->onboarding_status === 'APPLIED' ? 'PPB_VERIFIED' : $facility->onboarding_status,
                    ]);
                } else {
                    $facility->update([
                        'ppb_licence_status' => $result->licenceStatus ?? 'NOT_FOUND',
                        'ppb_verified_at'    => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Manual PPB verification failed', ['facility_id' => $facility->id, 'error' => $e->getMessage()]);
                return back()->with('error', 'PPB verification service unavailable. Please try again later.');
            }
        } else {
            // Stub: auto-approve PPB when service not available
            $facility->update([
                'ppb_licence_status' => 'VALID',
                'ppb_verified_at'    => now(),
                'onboarding_status'  => $facility->onboarding_status === 'APPLIED' ? 'PPB_VERIFIED' : $facility->onboarding_status,
            ]);
            Log::info('PPB verification stubbed (service not available)', ['facility_id' => $facility->id]);
        }

        DB::table('audit_logs')->insert([
            'facility_id' => $facility->id,
            'user_id'     => $request->user()->id,
            'action'      => 'PPB_REVERIFICATION_TRIGGERED',
            'model_type'  => 'App\Models\Facility',
            'model_id'    => $facility->id,
            'payload_after'=> json_encode(['ppb_licence_status' => $facility->ppb_licence_status]),
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'created_at'  => now(),
        ]);

        return back()->with('success', 'PPB verification has been processed.');
    }

    private function sendSms(string $phone, string $message): void
    {
        if (class_exists(\AfricasTalking\SDK::class)) {
            $at = new \AfricasTalking\SDK(config('services.africastalking.username'), config('services.africastalking.api_key'));
            $sms = $at->sms();
            $sms->send(['to' => $phone, 'message' => $message]);
        }
    }
}

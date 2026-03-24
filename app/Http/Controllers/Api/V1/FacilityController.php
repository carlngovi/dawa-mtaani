<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\PpbReverificationJob;
use App\Services\PpbVerificationService;
use App\Services\RoleAssignmentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FacilityController extends Controller
{
    public function __construct(
        private readonly PpbVerificationService $ppbService,
        private readonly RoleAssignmentService $roleService,
    ) {}

    // -------------------------------------------------------
    // POST /api/v1/facilities/register
    // -------------------------------------------------------
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'owner_name'            => 'required|string|max:255',
            'ppb_licence_number'    => 'required|string|max:100|unique:facilities,ppb_licence_number',
            'facility_name'         => 'required|string|max:255',
            'phone'                 => ['required', 'string', 'regex:/^(\+254|07|01)\d{8,9}$/'],
            'email'                 => 'nullable|email|max:255',
            'county'                => 'required|string|max:100',
            'sub_county'            => 'required|string|max:100',
            'ward'                  => 'required|string|max:100',
            'physical_address'      => 'required|string',
            'banking_account_number' => 'nullable|string|max:100',
        ]);

        // Phone uniqueness check across active facilities
        $phoneExists = DB::table('facilities')
            ->where('phone', $validated['phone'])
            ->where('facility_status', '!=', 'CHURNED')
            ->exists();

        if ($phoneExists) {
            return response()->json([
                'message' => 'This phone number is already registered to another facility. ' .
                             'Contact the network administrator if this is a new outlet.',
            ], 422);
        }

        // PPB Verification — must succeed before facility is created
        $ppbResult = $this->ppbService->verify($validated['ppb_licence_number']);

        if (! $ppbResult->found) {
            return response()->json([
                'message' => $ppbResult->message ?? 'PPB licence not found. Please check the licence number.',
            ], 422);
        }

        if ($ppbResult->licenceStatus === 'SUSPENDED') {
            return response()->json([
                'message' => 'This PPB licence is currently suspended. Contact PPB to resolve.',
            ], 422);
        }

        // Create facility record
        $ulid = (string) Str::ulid();
        $now = Carbon::now('UTC');

        $facilityId = DB::table('facilities')->insertGetId([
            'ulid'                => $ulid,
            'owner_name'          => $validated['owner_name'],
            'ppb_licence_number'  => $validated['ppb_licence_number'],
            'ppb_facility_type'   => $ppbResult->ppbType,
            'ppb_licence_status'  => $ppbResult->licenceStatus,
            'ppb_verified_at'     => $now,
            'ppb_raw_response'    => json_encode([
                'facility_name'     => $ppbResult->facilityName,
                'ppb_type'          => $ppbResult->ppbType,
                'licence_status'    => $ppbResult->licenceStatus,
                'registered_address' => $ppbResult->registeredAddress,
            ]),
            'facility_name'       => $ppbResult->facilityName ?? $validated['facility_name'],
            'phone'               => $validated['phone'],
            'email'               => $validated['email'] ?? null,
            'county'              => $validated['county'],
            'sub_county'          => $validated['sub_county'],
            'ward'                => $validated['ward'],
            'physical_address'    => $validated['physical_address'],
            'banking_account_number' => $validated['banking_account_number'] ?? null,
            'network_membership'  => 'NETWORK', // Default — admin changes to OFF_NETWORK
            'onboarding_status'   => 'PPB_VERIFIED',
            'facility_status'     => 'ACTIVE',
            'created_by'          => $request->user()->id,
            'created_at'          => $now,
            'updated_at'          => $now,
        ]);

        // Log PPB verification
        DB::table('ppb_verification_logs')->insert([
            'facility_id'             => $facilityId,
            'checked_at'              => $now,
            'licence_status_returned' => $ppbResult->licenceStatus,
            'response_json'           => json_encode([
                'found'           => $ppbResult->found,
                'facility_name'   => $ppbResult->facilityName,
                'ppb_type'        => $ppbResult->ppbType,
                'licence_status'  => $ppbResult->licenceStatus,
            ]),
            'triggered_by'            => 'ONBOARDING',
            'created_at'              => $now,
            'updated_at'              => $now,
        ]);

        // Update authenticated user's facility_id
        DB::table('users')
            ->where('id', $request->user()->id)
            ->update(['facility_id' => $facilityId]);

        // Assign RBAC role based on PPB type
        $this->roleService->assignForFacilityType(
            $request->user(),
            $ppbResult->ppbType
        );

        return response()->json([
            'message'           => 'Facility registered successfully.',
            'ulid'              => $ulid,
            'onboarding_status' => 'PPB_VERIFIED',
            'ppb_facility_type' => $ppbResult->ppbType,
            'facility_name'     => $ppbResult->facilityName ?? $validated['facility_name'],
        ], 201);
    }

    // -------------------------------------------------------
    // GET /api/v1/facilities/{ulid}
    // -------------------------------------------------------
    public function show(Request $request, string $ulid): JsonResponse
    {
        $facility = DB::table('facilities')
            ->where('ulid', $ulid)
            ->whereNull('deleted_at')
            ->first();

        if (! $facility) {
            return response()->json(['message' => 'Facility not found.'], 404);
        }

        return response()->json(['facility' => $facility]);
    }

    // -------------------------------------------------------
    // GET /api/v1/facilities
    // -------------------------------------------------------
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Only network admin and field agents can list all facilities
        if (! $user->hasRole(['network_admin', 'network_field_agent'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $query = DB::table('facilities')
            ->whereNull('deleted_at');

        if ($request->filled('type')) {
            $query->where('ppb_facility_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('facility_status', $request->status);
        }

        if ($request->filled('county')) {
            $query->where('county', $request->county);
        }

        if ($request->filled('membership')) {
            $query->where('network_membership', $request->membership);
        }

        if ($request->filled('gps_pending')) {
            $query->whereNull('latitude');
        }

        $facilities = $query->paginate(30);

        return response()->json($facilities);
    }

    // -------------------------------------------------------
    // PATCH /api/v1/facilities/{ulid}/link-account
    // -------------------------------------------------------
    public function linkAccount(Request $request, string $ulid): JsonResponse
    {
        $validated = $request->validate([
            'banking_account_number' => 'required|string|max:100',
        ]);

        $updated = DB::table('facilities')
            ->where('ulid', $ulid)
            ->whereNull('deleted_at')
            ->update([
                'banking_account_number'       => $validated['banking_account_number'],
                'banking_account_validated_at' => Carbon::now('UTC'),
                'onboarding_status'            => 'ACCOUNT_LINKED',
                'updated_by'                   => $request->user()->id,
                'updated_at'                   => Carbon::now('UTC'),
            ]);

        if (! $updated) {
            return response()->json(['message' => 'Facility not found.'], 404);
        }

        return response()->json(['message' => 'Banking account linked.', 'onboarding_status' => 'ACCOUNT_LINKED']);
    }

    // -------------------------------------------------------
    // PATCH /api/v1/facilities/{ulid}/status
    // -------------------------------------------------------
    public function updateStatus(Request $request, string $ulid): JsonResponse
    {
        if (! $request->user()->hasRole('network_admin')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'facility_status' => 'required|in:ACTIVE,SUSPENDED,PAUSED,CHURNED',
            'reason'          => 'required|string|max:500',
        ]);

        $updated = DB::table('facilities')
            ->where('ulid', $ulid)
            ->whereNull('deleted_at')
            ->update([
                'facility_status' => $validated['facility_status'],
                'updated_by'      => $request->user()->id,
                'updated_at'      => Carbon::now('UTC'),
            ]);

        if (! $updated) {
            return response()->json(['message' => 'Facility not found.'], 404);
        }

        return response()->json([
            'message'         => 'Facility status updated.',
            'facility_status' => $validated['facility_status'],
        ]);
    }

    // -------------------------------------------------------
    // POST /api/v1/facilities/{ulid}/verify-ppb
    // -------------------------------------------------------
    public function verifyPpb(Request $request, string $ulid): JsonResponse
    {
        if (! $request->user()->hasRole(['network_admin', 'network_field_agent'])) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $facility = DB::table('facilities')
            ->where('ulid', $ulid)
            ->whereNull('deleted_at')
            ->first();

        if (! $facility) {
            return response()->json(['message' => 'Facility not found.'], 404);
        }

        $result = $this->ppbService->verify($facility->ppb_licence_number);

        DB::table('facilities')
            ->where('ulid', $ulid)
            ->update([
                'ppb_licence_status' => $result->licenceStatus ?? $facility->ppb_licence_status,
                'ppb_verified_at'    => Carbon::now('UTC'),
                'updated_by'         => $request->user()->id,
                'updated_at'         => Carbon::now('UTC'),
            ]);

        DB::table('ppb_verification_logs')->insert([
            'facility_id'             => $facility->id,
            'checked_at'              => Carbon::now('UTC'),
            'licence_status_returned' => $result->licenceStatus ?? 'NOT_FOUND',
            'response_json'           => json_encode([
                'found'          => $result->found,
                'ppb_type'       => $result->ppbType,
                'licence_status' => $result->licenceStatus,
            ]),
            'triggered_by'            => 'MANUAL',
            'created_at'              => Carbon::now('UTC'),
            'updated_at'              => Carbon::now('UTC'),
        ]);

        return response()->json([
            'message'        => 'PPB verification complete.',
            'found'          => $result->found,
            'licence_status' => $result->licenceStatus,
        ]);
    }
}

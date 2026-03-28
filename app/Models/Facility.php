<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facility extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'ulid', 'owner_name', 'ppb_licence_number', 'ppb_facility_type',
        'ppb_licence_status', 'ppb_verified_at', 'ppb_raw_response',
        'facility_name', 'phone', 'email', 'county', 'sub_county', 'ward',
        'physical_address', 'banking_account_number', 'banking_account_validated_at',
        'network_membership', 'onboarding_status', 'facility_status', 'activated_at',
        'latitude', 'longitude', 'gps_accuracy_meters', 'gps_captured_at',
        'gps_captured_by', 'gps_capture_method',
        'phone_uniqueness_override', 'phone_override_reason', 'phone_override_by',
        'village_town', 'kenya_ward_id', 'kenya_constituency_id', 'kenya_county_id',
        'is_anonymised', 'anonymised_at', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'ppb_raw_response'              => 'array',
        'ppb_verified_at'               => 'datetime',
        'banking_account_validated_at'  => 'datetime',
        'activated_at'                  => 'datetime',
        'gps_captured_at'               => 'datetime',
        'anonymised_at'                 => 'datetime',
        'phone_uniqueness_override'     => 'boolean',
        'is_anonymised'                 => 'boolean',
        'latitude'                      => 'decimal:7',
        'longitude'                     => 'decimal:7',
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function groupMembership()
    {
        return $this->hasOne(PharmacyGroupMember::class);
    }

    public function group()
    {
        return $this->hasOneThrough(PharmacyGroup::class, PharmacyGroupMember::class, 'facility_id', 'id', 'id', 'group_id');
    }

    public function authorisedPlacers()
    {
        return $this->hasMany(FacilityAuthorisedPlacer::class);
    }

    public function pricingAgreement()
    {
        return $this->hasOne(FacilityPricingAgreement::class);
    }

    public function ppbVerificationLogs()
    {
        return $this->hasMany(PpbVerificationLog::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('facility_status', 'ACTIVE');
    }

    public function scopeNetwork($query)
    {
        return $query->where('network_membership', 'NETWORK');
    }

    public function scopeOffNetwork($query)
    {
        return $query->where('network_membership', 'OFF_NETWORK');
    }

    public function scopeRetail($query)
    {
        return $query->whereIn('ppb_facility_type', ['RETAIL', 'HOSPITAL']);
    }

    public function scopeWholesale($query)
    {
        return $query->whereIn('ppb_facility_type', ['WHOLESALE', 'MANUFACTURER']);
    }

    public function scopeGpsPending($query)
    {
        return $query->whereNull('latitude');
    }

    // Helpers
    public function isRetail(): bool
    {
        return in_array($this->ppb_facility_type, ['RETAIL', 'HOSPITAL']);
    }

    public function isWholesale(): bool
    {
        return in_array($this->ppb_facility_type, ['WHOLESALE', 'MANUFACTURER']);
    }

    public function isNetworkMember(): bool
    {
        return $this->network_membership === 'NETWORK';
    }

    public function hasGps(): bool
    {
        return ! is_null($this->latitude) && ! is_null($this->longitude);
    }
}

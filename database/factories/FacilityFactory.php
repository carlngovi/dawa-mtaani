<?php

namespace Database\Factories;

use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FacilityFactory extends Factory
{
    protected $model = Facility::class;

    public function definition(): array
    {
        return [
            'ulid'                => strtolower(Str::ulid()),
            'owner_name'          => fake()->name(),
            'ppb_licence_number'  => 'PPB-' . fake()->unique()->numerify('######'),
            'ppb_facility_type'   => 'WHOLESALE',
            'ppb_licence_status'  => 'VALID',
            'facility_name'       => fake()->company(),
            'phone'               => fake()->numerify('+2547########'),
            'county'              => 'Nairobi',
            'sub_county'          => 'Westlands',
            'ward'                => 'Parklands',
            'physical_address'    => fake()->address(),
            'network_membership'  => 'NETWORK',
            'onboarding_status'   => 'ACTIVE',
            'facility_status'     => 'ACTIVE',
            'branding_mode'       => 'OWN_BRAND',
            'latitude'            => -1.2921,
            'longitude'           => 36.8219,
            'created_by'          => 1,
        ];
    }

    public function offNetwork(): static
    {
        return $this->state(['network_membership' => 'OFF_NETWORK']);
    }

    public function dawaMtaaniBranding(): static
    {
        return $this->state(['branding_mode' => 'DAWA_MTAANI']);
    }
}

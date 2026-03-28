<?php

namespace Database\Seeders;

use App\Models\SpotterProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class SpotterProfileSeeder extends Seeder
{
    public function run(): void
    {
        $spotter = User::where('email', 'field.agent@test.com')->first();
        $salesRep = User::where('email', 'sales.rep@test.com')->first();

        if ($spotter) {
            SpotterProfile::updateOrCreate(
                ['user_id' => $spotter->id],
                [
                    'county' => 'Nairobi',
                    'ward' => 'Westlands',
                    'sales_rep_user_id' => $salesRep?->id,
                    'is_active' => true,
                ]
            );

            echo "Spotter profile created for {$spotter->name}\n";
        } else {
            echo "Spotter user field.agent@test.com not found\n";
        }
    }
}

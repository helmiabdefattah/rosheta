<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Database\Seeder;

class PharmacySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing areas or create some if none exist
        $areas = Area::all();
        if ($areas->isEmpty()) {
            $areas = Area::factory()->count(10)->create();
        }

        // Get existing users or create some if none exist
        $users = User::all();
        if ($users->isEmpty()) {
            $users = User::factory()->count(10)->create();
        }

        // Create pharmacies using existing areas and users
        Pharmacy::factory()->count(30)->create([
            'area_id' => fn() => $areas->random()->id,
            'user_id' => fn() => $users->random()->id,
        ]);
    }
}

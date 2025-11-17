<?php

namespace Database\Seeders;

use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PharmacyUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pharmacies = Pharmacy::all();

        foreach ($pharmacies as $pharmacy) {
            // Skip if a user already exists for this pharmacy_id
            $existing = User::where('pharmacy_id', $pharmacy->id)->first();
            if ($existing) {
                continue;
            }

            // Build a deterministic unique email per pharmacy
            $email = 'pharmacy' . $pharmacy->id . '@example.com';

            // Create the user assigned to this pharmacy
            User::create([
                'name' => $pharmacy->name ? ($pharmacy->name . ' User') : ('Pharmacy ' . $pharmacy->id . ' User'),
                'email' => $email,
                'password' => Hash::make('password'), // default password
                'pharmacy_id' => $pharmacy->id,
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);
        }
    }
}



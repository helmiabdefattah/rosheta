<?php

namespace Database\Seeders;

use App\Models\Laboratory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LaboratorySeeder extends Seeder
{
    public function run(): void
    {
        if (Laboratory::count() >= 5) {
            return;
        }

        $names = [
            'Alpha Lab',
            'Beta Diagnostics',
            'Gamma Labs',
            'Delta Medical',
            'Omega Diagnostics',
        ];

        foreach ($names as $name) {
            Laboratory::firstOrCreate(
                ['name' => $name],
                [
                    'phone' => '010' . random_int(10000000, 99999999),
                    'email' => Str::slug($name) . '@example.com',
                    'address' => 'Cairo, Egypt',
                    'is_active' => true,
                ]
            );
        }
    }
}





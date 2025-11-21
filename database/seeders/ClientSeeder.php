<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create clients with password "password"
        Client::factory()->count(50)->create([
            'password' => Hash::make('password'),
        ]);
    }
}

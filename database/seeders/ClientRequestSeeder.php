<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\ClientRequest;
use App\Models\ClientRequestLine;
use App\Models\Medicine;
use Illuminate\Database\Seeder;

class ClientRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing clients or create some if none exist
        $clients = Client::all();
        if ($clients->isEmpty()) {
            $clients = Client::factory()->count(20)->create();
        }

        // Get existing medicines or create some if none exist
        $medicines = Medicine::all();
        if ($medicines->isEmpty()) {
            $medicines = Medicine::factory()->count(50)->create();
        }

        // Create client addresses for existing clients
        foreach ($clients as $client) {
            ClientAddress::factory()->count(rand(1, 3))->create([
                'client_id' => $client->id,
            ]);
        }

        // Get all client addresses
        $clientAddresses = ClientAddress::all();

        // Create client requests
        $clientRequests = ClientRequest::factory()->count(40)->create([
            'client_id' => fn() => $clients->random()->id,
            'client_address_id' => fn() => $clientAddresses->random()->id,
        ]);

        // Create client request lines for each request
        foreach ($clientRequests as $request) {
            $lineCount = rand(1, 5); // 1-5 medicines per request
            ClientRequestLine::factory()->count($lineCount)->create([
                'client_request_id' => $request->id,
                'medicine_id' => fn() => $medicines->random()->id,
            ]);
        }
    }
}

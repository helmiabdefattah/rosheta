<?php

namespace Database\Seeders;

use App\Models\ClientRequest;
use App\Models\Medicine;
use App\Models\Offer;
use App\Models\OfferLine;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing data
        $clientRequests = ClientRequest::all();
        if ($clientRequests->isEmpty()) {
            $this->call(ClientRequestSeeder::class);
            $clientRequests = ClientRequest::all();
        }

        $pharmacies = Pharmacy::all();
        if ($pharmacies->isEmpty()) {
            $this->call(PharmacySeeder::class);
            $pharmacies = Pharmacy::all();
        }

        $users = User::all();
        if ($users->isEmpty()) {
            $users = User::factory()->count(10)->create();
        }

        $medicines = Medicine::all();
        if ($medicines->isEmpty()) {
            $medicines = Medicine::factory()->count(50)->create();
        }

        // Create offers for some client requests
        $offers = Offer::factory()->count(30)->create([
            'client_request_id' => fn() => $clientRequests->random()->id,
            'pharmacy_id' => fn() => $pharmacies->random()->id,
            'user_id' => fn() => $users->random()->id,
        ]);

        // Create offer lines for each offer
        foreach ($offers as $offer) {
            // Get the medicines from the related client request
            $requestLines = $offer->request->lines;
            
            if ($requestLines->isNotEmpty()) {
                // Create offer lines based on request lines
                foreach ($requestLines as $requestLine) {
                    OfferLine::factory()->create([
                        'offer_id' => $offer->id,
                        'medicine_id' => $requestLine->medicine_id,
                        'quantity' => $requestLine->quantity,
                        'unit' => $requestLine->unit,
                        'price' => rand(10, 500),
                    ]);
                }
            } else {
                // If no request lines, create random offer lines
                $lineCount = rand(1, 4);
                OfferLine::factory()->count($lineCount)->create([
                    'offer_id' => $offer->id,
                    'medicine_id' => fn() => $medicines->random()->id,
                ]);
            }

            // Update total price based on offer lines
            $totalPrice = $offer->lines->sum(function ($line) {
                return $line->quantity * ($line->price ?? 0);
            });
            $offer->update(['total_price' => $totalPrice]);
        }
    }
}

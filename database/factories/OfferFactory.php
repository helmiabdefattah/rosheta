<?php

namespace Database\Factories;

use App\Models\ClientRequest;
use App\Models\Offer;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Offer>
 */
class OfferFactory extends Factory
{
    protected $model = Offer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_request_id' => ClientRequest::factory(),
            'pharmacy_id' => Pharmacy::factory(),
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['draft', 'submitted', 'accepted', 'cancelled']),
            'total_price' => $this->faker->randomFloat(2, 50, 5000),
        ];
    }
}

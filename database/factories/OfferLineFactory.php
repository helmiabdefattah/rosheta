<?php

namespace Database\Factories;

use App\Models\Medicine;
use App\Models\Offer;
use App\Models\OfferLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OfferLine>
 */
class OfferLineFactory extends Factory
{
    protected $model = OfferLine::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'offer_id' => Offer::factory(),
            'medicine_id' => Medicine::factory(),
            'quantity' => $this->faker->numberBetween(1, 10),
            'unit' => $this->faker->randomElement(['box', 'strips']),
            'price' => $this->faker->randomFloat(2, 10, 500),
        ];
    }
}

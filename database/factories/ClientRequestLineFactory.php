<?php

namespace Database\Factories;

use App\Models\ClientRequest;
use App\Models\ClientRequestLine;
use App\Models\Medicine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientRequestLine>
 */
class ClientRequestLineFactory extends Factory
{
    protected $model = ClientRequestLine::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_request_id' => ClientRequest::factory(),
            'medicine_id' => Medicine::factory(),
            'quantity' => $this->faker->numberBetween(1, 10),
            'unit' => $this->faker->randomElement(['box', 'strips']),
        ];
    }
}

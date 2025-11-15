<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\ClientRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientRequest>
 */
class ClientRequestFactory extends Factory
{
    protected $model = ClientRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'client_address_id' => ClientAddress::factory(),
            'pregnant' => $this->faker->boolean(10), // 10% chance
            'diabetic' => $this->faker->boolean(15), // 15% chance
            'heart_patient' => $this->faker->boolean(10), // 10% chance
            'high_blood_pressure' => $this->faker->boolean(20), // 20% chance
            'note' => $this->faker->optional()->paragraph(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'images' => $this->faker->optional()->randomElements([
                'image1.jpg',
                'image2.jpg',
                'image3.jpg',
            ], $this->faker->numberBetween(0, 3)),
        ];
    }
}

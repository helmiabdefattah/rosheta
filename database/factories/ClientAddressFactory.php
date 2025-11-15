<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\City;
use App\Models\Client;
use App\Models\ClientAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientAddress>
 */
class ClientAddressFactory extends Factory
{
    protected $model = ClientAddress::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'address' => $this->faker->address(),
            'location' => [
                'lat' => $this->faker->latitude(30.0, 31.5),
                'lng' => $this->faker->longitude(29.0, 33.0),
            ],
            'city_id' => City::factory(),
            'area_id' => Area::factory(),
        ];
    }
}

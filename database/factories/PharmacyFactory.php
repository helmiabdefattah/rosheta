<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pharmacy>
 */
class PharmacyFactory extends Factory
{
    protected $model = Pharmacy::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'area_id' => Area::factory(),
            'name' => $this->faker->company() . ' Pharmacy',
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->address(),
            'location' => $this->faker->address(),
            'lat' => $this->faker->latitude(30.0, 31.5), // Egypt latitude range
            'lng' => $this->faker->longitude(29.0, 33.0), // Egypt longitude range
            'license_number' => $this->faker->unique()->numerify('PH-#######'),
            'pharmacist_name' => $this->faker->name(),
            'pharmacist_license' => $this->faker->unique()->numerify('LIC-#######'),
            'opening_time' => $this->faker->time('08:00'),
            'closing_time' => $this->faker->time('22:00'),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}

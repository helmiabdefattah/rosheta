<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Governorate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\City>
 */
class CityFactory extends Factory
{
    protected $model = City::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Use existing governorate if available, otherwise create one
        $governorate = Governorate::inRandomOrder()->first() ?? Governorate::factory()->create();

        return [
            'governorate_id' => $governorate->id,
            'name' => $this->faker->city(),
            'name_ar' => $this->faker->words(2, true),
            'is_active' => $this->faker->boolean(90),
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }
}

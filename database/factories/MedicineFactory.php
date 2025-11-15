<?php

namespace Database\Factories;

use App\Models\Medicine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Medicine>
 */
class MedicineFactory extends Factory
{
    protected $model = Medicine::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'api_id' => $this->faker->unique()->numerify('API-#####'),
            'name' => $this->faker->words(3, true) . ' ' . $this->faker->numberBetween(10, 500) . 'mg',
            'arabic' => $this->faker->words(2, true),
            'shortage' => '0',
            'date_updated' => $this->faker->numerify('##########'),
            'imported' => $this->faker->randomElement(['imported', 'local']),
            'percentage' => '',
            'pharmacology' => $this->faker->optional()->sentence(),
            'route' => $this->faker->randomElement(['oral.solid', 'oral.liquid', 'injection', 'topical']),
            'in_eye' => $this->faker->boolean(10),
            'units' => $this->faker->numberBetween(1, 5),
            'small_unit' => '0',
            'sold_times' => $this->faker->numberBetween(0, 1000),
            'dosage_form' => $this->faker->randomElement(['tablet', 'capsule', 'syrup', 'injection', 'cream']),
            'barcode' => $this->faker->optional()->ean13(),
            'barcode2' => '',
            'qrcode' => '',
            'anyupdatedate' => $this->faker->numerify('##########'),
            'new_added_date' => '',
            'updtime' => '0',
            'fame' => $this->faker->boolean(20),
            'cosmo' => $this->faker->boolean(5),
            'dose' => '',
            'repeated' => $this->faker->boolean(10),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'oldprice' => $this->faker->optional()->randomFloat(2, 5, 400),
            'newprice' => '',
            'active_ingredient' => $this->faker->words(2, true),
            'similars_id' => '',
            'img' => $this->faker->optional()->imageUrl(),
            'description' => $this->faker->optional()->sentence(),
            'uses' => $this->faker->optional()->sentence(),
            'company' => $this->faker->company(),
            'reported' => $this->faker->boolean(5),
            'reporter_name' => $this->faker->optional()->name(),
            'visits' => $this->faker->numberBetween(0, 10000),
            'sim_visits' => $this->faker->numberBetween(0, 5000),
            'ind_visits' => $this->faker->numberBetween(0, 2000),
            'composition_visits' => $this->faker->numberBetween(0, 1000),
            'order_visits' => $this->faker->numberBetween(0, 3000),
            'app_visits' => $this->faker->numberBetween(0, 500),
            'shares' => $this->faker->numberBetween(0, 100),
            'last_visited' => $this->faker->optional()->numerify('##########'),
            'lastupdatingadmin' => $this->faker->optional()->email(),
            'awhat_visits' => $this->faker->numberBetween(0, 200),
            'report_msg' => '',
            'found_pharmacies_ids' => '',
            'availability' => $this->faker->numberBetween(0, 100),
            'noimgid' => '0',
            'raw_data' => [],
            'search_term' => '',
            'batch_number' => $this->faker->numberBetween(1, 100),
        ];
    }
}

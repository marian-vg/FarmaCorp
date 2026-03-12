<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Medicine;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Medicine>
 */
class MedicineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'presentation_name' => $this->faker->words(3, true),
            'price' => $this->faker->randomFloat(2, 5, 5000),
            'level' => $this->faker->randomElement(['Baja', 'Media', 'Alta']),
            'leaflet' => $this->faker->paragraph(),
            'expiration_date' => $this->faker->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
            'is_psychotropic' => $this->faker->boolean(15),
            'group_id' => Group::factory(),
        ];
    }
}

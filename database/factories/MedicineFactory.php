<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Medicine>
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
            'product_id' => \App\Models\Product::factory(),
            'level' => $this->faker->randomElement(['Baja', 'Media', 'Alta']),
            'leaflet' => $this->faker->paragraph(),
            'expiration_date' => $this->faker->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
            'is_psychotropic' => $this->faker->boolean(15), 
            'group_id' => \App\Models\Group::factory(),
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Paracetamol', 'Ibuprofeno', 'Amoxicilina', 'Aspirina', 'Omeprazol', 'Loratadina', 'Enalapril', 'Metformina']) . ' ' . fake()->randomElement(['500mg', '600mg', '1g', '10mg', '20mg']) . ' x ' . fake()->randomElement(['10', '20', '30']) . ' comp.',
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 100, 8500), // Precios entre $100 y $8500
            'status' => true, // Por defecto activos
        ];
    }

    /**
     * Estado para crear productos inactivos (para probar el "Archivo")
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }
}
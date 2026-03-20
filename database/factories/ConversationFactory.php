<?php

namespace Database\Factories;

use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conversation>
 */
class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'is_group' => false,
            'name' => null,
        ];
    }

    /**
     * Indicate that the conversation is a group.
     */
    public function group(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_group' => true,
            'name' => $this->faker->words(3, true),
        ]);
    }
}

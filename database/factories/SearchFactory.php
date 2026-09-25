<?php

namespace Database\Factories;

use App\Models\Search;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Search>
 */
class SearchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'city' => fake()->city(),
            'country' => fake()->country(),
            'temperature' => fake()->randomFloat(1, -10, 45),
            'condition' => fake()->randomElement(['Clear sky', 'Partialy Clouded', 'Rain', 'Snow', 'Thunderstrom']),
        ];
    }

    public function rainy(): static
    {
        return $this->state(fn (array $attributes): array => [
            'condition' => 'Rain',
        ]);
    }
}

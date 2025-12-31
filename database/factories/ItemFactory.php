<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;

class ItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'category_id' => Category::factory(),
            'quantity' => fake()->numberBetween(10, 100),
            'price' => fake()->randomFloat(2, 10, 100),
        ];
    }
}

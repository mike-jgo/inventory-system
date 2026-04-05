<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'           => User::factory(),
            'customer_name'     => fake()->name(),
            'type'              => fake()->randomElement(['dine_in', 'takeout', 'online']),
            'status'            => fake()->randomElement(['pending', 'completed', 'cancelled']),
            'total_amount'      => fake()->randomFloat(2, 10, 500),
            'payment_method'    => 'cash',
            'amount_paid'       => fake()->randomFloat(2, 10, 500),
            'change_due'        => 0,
            'payment_reference' => null,
            'wastage'           => false,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    public function definition()
    {
        return [
            'code' => $this->faker->unique()->regexify('[A-Z0-9]{10}'),
            'type' => $this->faker->randomElement(['percentage', 'fixed']),
            'value' => $this->faker->numberBetween(5, 50),
            'start_date' => $this->faker->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'end_date' => $this->faker->dateTimeBetween('+1 month', '+2 months')->format('Y-m-d'),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\FeeType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FeeType>
 */
class FeeTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FeeType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Trash Fee',
                'Security Fee',
                'Community Association Fee',
                'Water Fee',
                'Parking Fee'
            ]),
            'amount' => $this->faker->randomFloat(2, 10000, 500000),
            'is_active' => $this->faker->boolean(90),
        ];
    }
}

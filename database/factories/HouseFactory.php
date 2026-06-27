<?php

namespace Database\Factories;

use App\Models\House;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\House>
 */
class HouseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = House::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'house_number' => strtoupper($this->faker->unique()->bothify('??-##')),
            'address' => $this->faker->address(),
            'occupancy_status' => 'unoccupied',
            // 'occupancy_status' => $this->faker->randomElement(['occupied', 'unoccupied']),
        ];
    }
}

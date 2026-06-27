<?php

namespace Database\Factories;

use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resident>
 */
class ResidentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Resident::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name(),
            'id_card_photo' => 'residents/id_cards/' . $this->faker->uuid() . '.jpg',
            'resident_status' => $this->faker->randomElement(['contract', 'permanent']),
            'phone_number' => substr($this->faker->numerify('08##########'), 0, 13),
            'is_married' => $this->faker->boolean(),
        ];
    }
}

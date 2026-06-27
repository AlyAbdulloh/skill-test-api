<?php

namespace Database\Factories;

use App\Models\House;
use App\Models\HouseResident;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HouseResident>
 */
class HouseResidentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = HouseResident::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->date();
        return [
            'house_id' => House::factory(),
            'resident_id' => Resident::factory(),
            'start_date' => $startDate,
            'end_date' => function (array $attributes) use ($startDate) {
                $resident = Resident::find($attributes['resident_id']);
                if ($resident && $resident->resident_status === 'contract') {
                    return $this->faker->dateTimeBetween($startDate, '+2 years')->format('Y-m-d');
                }
                return null;
            },
            'is_active' => $this->faker->boolean(80),
        ];
    }
}

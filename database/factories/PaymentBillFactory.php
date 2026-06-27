<?php

namespace Database\Factories;

use App\Models\FeeType;
use App\Models\HouseResident;
use App\Models\PaymentBill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentBill>
 */
class PaymentBillFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PaymentBill::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['paid', 'unpaid']);
        return [
            'house_resident_id' => HouseResident::factory(),
            'fee_type_id' => FeeType::factory(),
            'billing_month' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-01'),
            'amount' => $this->faker->randomFloat(2, 10000, 100000),
            'status' => $status,
            'paid_at' => $status === 'paid' ? $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d') : null,
            'payment_group_id' => null,
        ];
    }
}

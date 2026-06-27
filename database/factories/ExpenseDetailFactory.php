<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\ExpenseDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExpenseDetail>
 */
class ExpenseDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ExpenseDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'expense_id' => Expense::factory(),
            'title' => $this->faker->words(3, true),
            'amount' => $this->faker->randomFloat(2, 5000, 100000),
        ];
    }
}

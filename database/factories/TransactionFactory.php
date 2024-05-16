<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Transaction;
use Faker\Generator as Faker;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition()
    {
        $startDate = '2023-05-01';
        $endDate = '2024-05-01';
        return [
            'title' => $this->faker->sentence(3),
            'payment_method' => $this->faker->randomElement(['Paystack', 'Monnify']),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'status' => $this->faker->randomElement(['Completed', 'Pending', 'Failed']),
            'created_at' => $this->faker->dateTimeBetween($startDate, $endDate),
            'updated_at' => $this->faker->dateTimeBetween($startDate, $endDate),
        ];
    }
}

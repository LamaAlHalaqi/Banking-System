<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'amount' => $this->faker->randomFloat(2, 1, 10000),
            'type' => $this->faker->randomElement([
                Transaction::TYPE_DEPOSIT,
                Transaction::TYPE_WITHDRAWAL,
                Transaction::TYPE_TRANSFER,
                Transaction::TYPE_PAYMENT
            ]),
            'status' => Transaction::STATUS_COMPLETED,
            'description' => $this->faker->sentence(),
            'reference' => $this->faker->uuid(),
            'initiated_by' => User::factory(),
        ];
    }

    /**
     * Configure the model factory to generate deposit transactions.
     */
    public function deposit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Transaction::TYPE_DEPOSIT,
            'amount' => $this->faker->randomFloat(2, 1, 10000),
        ]);
    }

    /**
     * Configure the model factory to generate withdrawal transactions.
     */
    public function withdrawal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Transaction::TYPE_WITHDRAWAL,
            'amount' => $this->faker->randomFloat(2, 1, 10000),
        ]);
    }

    /**
     * Configure the model factory to generate transfer transactions.
     */
    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Transaction::TYPE_TRANSFER,
            'destination_account_id' => Account::factory(),
            'amount' => $this->faker->randomFloat(2, 1, 10000),
        ]);
    }

    /**
     * Configure the model factory to generate payment transactions.
     */
    public function payment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Transaction::TYPE_PAYMENT,
            'amount' => $this->faker->randomFloat(2, 1, 10000),
        ]);
    }

    /**
     * Configure the model factory to generate pending transactions.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Transaction::STATUS_PENDING,
        ]);
    }

    /**
     * Configure the model factory to generate approved transactions.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Transaction::STATUS_APPROVED,
            'approved_by' => User::factory(),
        ]);
    }

    /**
     * Configure the model factory to generate rejected transactions.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Transaction::STATUS_REJECTED,
            'approved_by' => User::factory(),
        ]);
    }
}
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Account;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'account_number' => $this->generateUniqueAccountNumber(),
            'account_type' => $this->faker->randomElement([
                Account::TYPE_SAVINGS,
                Account::TYPE_CHECKING,
                Account::TYPE_LOAN,
                Account::TYPE_INVESTMENT
            ]),
            'balance' => $this->faker->randomFloat(2, 0, 100000),
            'interest_rate' => $this->faker->randomFloat(4, 0, 0.1),
            'state' => Account::STATE_ACTIVE,
            'overdraft_limit' => $this->faker->randomFloat(2, 0, 5000),
        ];
    }

    /**
     * Generate a unique account number.
     *
     * @return string
     */
    private function generateUniqueAccountNumber(): string
    {
        do {
            $accountNumber = 'ACC' . $this->faker->unique()->numerify('########');
        } while (Account::where('account_number', $accountNumber)->exists());
        
        return $accountNumber;
    }

    /**
     * Configure the model factory to generate savings accounts.
     */
    public function savings(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => Account::TYPE_SAVINGS,
        ]);
    }

    /**
     * Configure the model factory to generate checking accounts.
     */
    public function checking(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => Account::TYPE_CHECKING,
        ]);
    }

    /**
     * Configure the model factory to generate loan accounts.
     */
    public function loan(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => Account::TYPE_LOAN,
            'balance' => $this->faker->randomFloat(2, -100000, 0), // Negative balance for loans
        ]);
    }

    /**
     * Configure the model factory to generate investment accounts.
     */
    public function investment(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => Account::TYPE_INVESTMENT,
        ]);
    }

    /**
     * Configure the model factory to generate frozen accounts.
     */
    public function frozen(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => Account::STATE_FROZEN,
        ]);
    }

    /**
     * Configure the model factory to generate suspended accounts.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => Account::STATE_SUSPENDED,
        ]);
    }

    /**
     * Configure the model factory to generate closed accounts.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => Account::STATE_CLOSED,
        ]);
    }
}
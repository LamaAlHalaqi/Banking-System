<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => User::ROLE_CUSTOMER,
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'date_of_birth' => fake()->date(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Configure the model factory to generate admin users.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_ADMIN,
        ]);
    }

    /**
     * Configure the model factory to generate manager users.
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_MANAGER,
        ]);
    }

    /**
     * Configure the model factory to generate teller users.
     */
    public function teller(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_TELLER,
        ]);
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SupportTicket;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SupportTicket>
 */
class SupportTicketFactory extends Factory
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
            'subject' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'priority' => $this->faker->randomElement([
                SupportTicket::PRIORITY_LOW,
                SupportTicket::PRIORITY_MEDIUM,
                SupportTicket::PRIORITY_HIGH
            ]),
            'status' => SupportTicket::STATUS_OPEN,
        ];
    }

    /**
     * Configure the model factory to generate low priority tickets.
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => SupportTicket::PRIORITY_LOW,
        ]);
    }

    /**
     * Configure the model factory to generate medium priority tickets.
     */
    public function mediumPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => SupportTicket::PRIORITY_MEDIUM,
        ]);
    }

    /**
     * Configure the model factory to generate high priority tickets.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => SupportTicket::PRIORITY_HIGH,
        ]);
    }

    /**
     * Configure the model factory to generate in-progress tickets.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SupportTicket::STATUS_IN_PROGRESS,
        ]);
    }

    /**
     * Configure the model factory to generate resolved tickets.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SupportTicket::STATUS_RESOLVED,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Configure the model factory to generate closed tickets.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SupportTicket::STATUS_CLOSED,
            'resolved_at' => now(),
        ]);
    }
}

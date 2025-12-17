<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Notification;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
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
            'channel' => $this->faker->randomElement([
                Notification::CHANNEL_EMAIL,
                Notification::CHANNEL_SMS,
                Notification::CHANNEL_IN_APP
            ]),
            'subject' => $this->faker->sentence(),
            'message' => $this->faker->paragraph(),
            'status' => Notification::STATUS_PENDING,
        ];
    }

    /**
     * Configure the model factory to generate email notifications.
     */
    public function email(): static
    {
        return $this->state(fn (array $attributes) => [
            'channel' => Notification::CHANNEL_EMAIL,
        ]);
    }

    /**
     * Configure the model factory to generate SMS notifications.
     */
    public function sms(): static
    {
        return $this->state(fn (array $attributes) => [
            'channel' => Notification::CHANNEL_SMS,
        ]);
    }

    /**
     * Configure the model factory to generate in-app notifications.
     */
    public function inApp(): static
    {
        return $this->state(fn (array $attributes) => [
            'channel' => Notification::CHANNEL_IN_APP,
        ]);
    }

    /**
     * Configure the model factory to generate sent notifications.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Notification::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    /**
     * Configure the model factory to generate failed notifications.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Notification::STATUS_FAILED,
        ]);
    }
}

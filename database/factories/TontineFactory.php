<?php

namespace Database\Factories;

use App\Frequency;
use App\Models\Tontine;
use App\Models\User;
use App\TontineStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tontine>
 */
class TontineFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => 'Tontine '.fake()->randomElement(['des collègues', 'du quartier', 'des commerçantes', 'de la famille']),
            'description' => null,
            'contribution_amount' => fake()->randomElement([5000, 10000, 25000, 50000]),
            'frequency' => Frequency::Monthly,
            'starts_on' => now()->addDays(3)->toDateString(),
            'late_penalty' => 1000,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => ['status' => TontineStatus::Active]);
    }
}

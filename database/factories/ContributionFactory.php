<?php

namespace Database\Factories;

use App\Models\Contribution;
use App\Models\Member;
use App\Models\Round;
use App\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contribution>
 */
class ContributionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'round_id' => Round::factory(),
            'member_id' => fn (array $attributes) => Member::factory()->create(['tontine_id' => Round::find($attributes['round_id'])->tontine_id])->id,
            'amount' => 10000,
            'penalty' => 0,
            'method' => fake()->randomElement(PaymentMethod::cases()),
            'reference' => null,
            'paid_on' => now()->toDateString(),
        ];
    }
}

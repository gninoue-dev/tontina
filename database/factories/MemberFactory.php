<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Tontine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tontine_id' => Tontine::factory(),
            'name' => fake()->firstName().' '.fake()->lastName(),
            'phone' => fake()->unique()->numerify(fake()->randomElement(['07', '05', '01']).'########'),
            'payout_position' => null,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Round;
use App\Models\Tontine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Round>
 */
class RoundFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tontine_id' => Tontine::factory()->active(),
            'number' => 1,
            'due_on' => now()->toDateString(),
            'beneficiary_id' => fn (array $attributes) => Member::factory()->create(['tontine_id' => $attributes['tontine_id']])->id,
            'paid_out_at' => null,
        ];
    }
}

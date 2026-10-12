<?php

namespace Tests\Feature;

use App\Actions\StartTontine;
use App\Models\Member;
use App\Models\Tontine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TontineAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_another_organizer_cannot_see_or_touch_my_tontine(): void
    {
        $tontine = Tontine::factory()->create();
        $members = Member::factory()->for($tontine)->count(2)->create();
        app(StartTontine::class)->handle($tontine);
        $intruder = User::factory()->create();

        $this->actingAs($intruder);
        $this->get(route('tontines.show', $tontine))->assertForbidden();
        $this->get(route('rounds.show', [$tontine, 1]))->assertForbidden();
        $this->post(route('contributions.store', [$tontine, 1]), [
            'member_id' => $members[0]->id, 'method' => 'cash', 'paid_on' => now()->toDateString(),
        ])->assertForbidden();
        $this->post(route('rounds.payout', [$tontine, 1]))->assertForbidden();
        $this->post(route('members.store', $tontine), ['name' => 'X', 'phone' => '0707000000'])->assertForbidden();
        $this->post(route('tontines.draw', $tontine))->assertForbidden();
        $this->delete(route('tontines.destroy', $tontine))->assertForbidden();

        $this->assertDatabaseCount('contributions', 0);
    }

    public function test_nested_resources_must_belong_to_the_tontine_in_the_url(): void
    {
        $user = User::factory()->create();
        $mine = Tontine::factory()->for($user, 'organizer')->create();
        $other = Tontine::factory()->for($user, 'organizer')->create();
        $foreignMember = Member::factory()->for($other)->create();

        $this->actingAs($user)->delete(route('members.destroy', [$mine, $foreignMember]))->assertNotFound();
        $this->assertModelExists($foreignMember);
    }
}

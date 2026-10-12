<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Tontine;
use App\Models\User;
use App\TontineStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TontineSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_organizer_creates_a_tontine(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/tontines', [
            'name' => 'Tontine du quartier',
            'contribution_amount' => 10000,
            'frequency' => 'weekly',
            'starts_on' => now()->addDay()->toDateString(),
            'late_penalty' => '',
        ]);

        $tontine = Tontine::sole();
        $response->assertRedirect(route('tontines.show', $tontine));
        $this->assertTrue($tontine->organizer->is($user));
        $this->assertSame(TontineStatus::Draft, $tontine->status);
        $this->assertSame(0, $tontine->late_penalty);
        $this->assertSame(32, strlen($tontine->public_token));
    }

    public function test_tontine_validation(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/tontines', ['name' => '', 'contribution_amount' => 100, 'frequency' => 'daily', 'starts_on' => now()->subDay()->toDateString()])
            ->assertSessionHasErrors(['name', 'contribution_amount', 'frequency', 'starts_on']);
        $this->assertDatabaseCount('tontines', 0);
    }

    public function test_member_phone_is_normalized_from_common_ivorian_formats(): void
    {
        $tontine = Tontine::factory()->create();

        foreach (['07 07 12 34 56', '+225 05.05.23.45.67', '002250101345678'] as $input) {
            $this->actingAs($tontine->organizer)
                ->post(route('members.store', $tontine), ['name' => 'Membre', 'phone' => $input])
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(['0707123456', '0505234567', '0101345678'], $tontine->members()->pluck('phone')->all());
    }

    public function test_invalid_or_duplicate_phone_is_refused(): void
    {
        $tontine = Tontine::factory()->create();
        Member::factory()->for($tontine)->create(['phone' => '0707123456']);

        $this->actingAs($tontine->organizer)->post(route('members.store', $tontine), ['name' => 'A', 'phone' => '12345'])
            ->assertSessionHasErrors(['phone' => 'Le numéro doit avoir 10 chiffres, par exemple 07 07 12 34 56.']);
        $this->actingAs($tontine->organizer)->post(route('members.store', $tontine), ['name' => 'B', 'phone' => '07 07 12 34 56'])
            ->assertSessionHasErrors(['phone' => 'Ce numéro est déjà inscrit dans cette tontine.']);
    }

    public function test_the_same_phone_can_join_two_different_tontines(): void
    {
        $user = User::factory()->create();
        [$first, $second] = Tontine::factory()->for($user, 'organizer')->count(2)->create();

        foreach ([$first, $second] as $tontine) {
            $this->actingAs($user)->post(route('members.store', $tontine), ['name' => 'Awa', 'phone' => '0707123456'])
                ->assertSessionHasNoErrors();
        }
        $this->assertDatabaseCount('members', 2);
    }

    public function test_draw_assigns_every_position_once(): void
    {
        $tontine = Tontine::factory()->create();
        Member::factory()->for($tontine)->count(5)->create();

        $this->actingAs($tontine->organizer)->post(route('tontines.draw', $tontine))->assertRedirect();

        $this->assertEqualsCanonicalizing([1, 2, 3, 4, 5], $tontine->members()->pluck('payout_position')->all());
    }

    public function test_starting_plans_one_round_per_member_in_payout_order(): void
    {
        $tontine = Tontine::factory()->create(['frequency' => 'monthly', 'starts_on' => '2026-01-31']);
        $members = Member::factory()->for($tontine)->count(3)->sequence(
            ['payout_position' => 2], ['payout_position' => 1], ['payout_position' => 3],
        )->create();

        $this->actingAs($tontine->organizer)->post(route('tontines.start', $tontine))
            ->assertSessionHas('success');

        $rounds = $tontine->rounds()->get();
        $this->assertSame(TontineStatus::Active, $tontine->fresh()->status);
        $this->assertSame([1, 2, 3], $rounds->pluck('number')->all());
        $this->assertSame([$members[1]->id, $members[0]->id, $members[2]->id], $rounds->pluck('beneficiary_id')->all());
        // fin de mois : pas de débordement au 3 mars
        $this->assertSame(['2026-01-31', '2026-02-28', '2026-03-31'], $rounds->map(fn ($r) => $r->due_on->toDateString())->all());
    }

    public function test_a_tontine_needs_two_members_to_start(): void
    {
        $tontine = Tontine::factory()->create();
        Member::factory()->for($tontine)->create();

        $this->actingAs($tontine->organizer)->post(route('tontines.start', $tontine))
            ->assertSessionHas('error', 'Il faut au moins 2 membres pour démarrer une tontine.');
        $this->assertTrue($tontine->fresh()->isDraft());
        $this->assertDatabaseCount('rounds', 0);
    }

    public function test_members_and_order_are_locked_once_started(): void
    {
        $tontine = Tontine::factory()->create();
        $members = Member::factory()->for($tontine)->count(2)->create();
        $this->actingAs($tontine->organizer)->post(route('tontines.start', $tontine));
        $positions = $tontine->members()->pluck('payout_position', 'id')->all();

        $this->post(route('members.store', $tontine), ['name' => 'Tard', 'phone' => '0707000000'])->assertSessionHas('error');
        $this->delete(route('members.destroy', [$tontine, $members[0]]))->assertSessionHas('error');
        $this->post(route('tontines.draw', $tontine))->assertSessionHas('error');
        $this->post(route('tontines.start', $tontine))->assertSessionHas('error', 'Cette tontine a déjà démarré.');

        $this->assertDatabaseCount('members', 2);
        $this->assertSame($positions, $tontine->members()->pluck('payout_position', 'id')->all());
        $this->assertDatabaseCount('rounds', 2);
    }

    public function test_only_draft_tontines_can_be_deleted(): void
    {
        $draft = Tontine::factory()->create();
        $active = Tontine::factory()->active()->for($draft->organizer, 'organizer')->create();

        $this->actingAs($draft->organizer)->delete(route('tontines.destroy', $active))->assertForbidden();
        $this->delete(route('tontines.destroy', $draft))->assertRedirect(route('tontines.index'));

        $this->assertModelMissing($draft);
        $this->assertModelExists($active);
    }

    public function test_index_lists_only_my_tontines(): void
    {
        $mine = Tontine::factory()->create(['name' => 'Ma tontine']);
        Tontine::factory()->create(['name' => 'Tontine voisine']);

        $this->actingAs($mine->organizer)->get('/tontines')->assertOk()->assertSee('Ma tontine')->assertDontSee('Tontine voisine');
    }
}

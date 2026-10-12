<?php

namespace Tests\Feature;

use App\Actions\StartTontine;
use App\Models\Contribution;
use App\Models\Member;
use App\Models\Tontine;
use App\TontineStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RoundPaymentTest extends TestCase
{
    use RefreshDatabase;

    private Tontine $tontine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-03-10');
        $this->tontine = Tontine::factory()->create([
            'contribution_amount' => 25000, 'late_penalty' => 2000, 'frequency' => 'monthly', 'starts_on' => '2026-03-05',
        ]);
        Member::factory()->for($this->tontine)->count(3)->sequence(
            ['name' => 'Awa', 'phone' => '0707123456'], ['name' => 'Yao', 'phone' => '0505234567'], ['name' => 'Koffi', 'phone' => '0101345678'],
        )->create();
        app(StartTontine::class)->handle($this->tontine);
        $this->actingAs($this->tontine->organizer);
    }

    private function pay(Member $member, string $paidOn, string $method = 'wave', ?string $reference = 'WV123'): TestResponse
    {
        return $this->post(route('contributions.store', [$this->tontine, 1]), [
            'member_id' => $member->id, 'method' => $method, 'reference' => $reference, 'paid_on' => $paidOn,
        ]);
    }

    public function test_round_page_shows_who_paid_and_whatsapp_reminders_for_the_others(): void
    {
        [$awa, $yao] = $this->tontine->members()->get();
        $this->pay($awa, '2026-03-04');

        $response = $this->get(route('rounds.show', [$this->tontine, 1]))->assertOk();

        $response->assertSee('1/3 cotisations');
        $response->assertSee('https://wa.me/2250505234567?text=', false);
        $response->assertSee(rawurlencode('Bonjour Yao'), false);
        $response->assertDontSee('https://wa.me/2250707123456', false);
    }

    public function test_on_time_payment_has_no_penalty_and_keeps_mobile_money_reference(): void
    {
        $awa = $this->tontine->members()->first();

        $this->pay($awa, '2026-03-05', 'orange_money', 'PP2603.0512')->assertSessionHas('success', 'Cotisation de Awa enregistrée.');

        $contribution = Contribution::sole();
        $this->assertSame(25000, $contribution->amount);
        $this->assertSame(0, $contribution->penalty);
        $this->assertSame('PP2603.0512', $contribution->reference);
    }

    public function test_late_payment_gets_the_penalty(): void
    {
        $awa = $this->tontine->members()->first();

        $this->pay($awa, '2026-03-08')->assertSessionHas('success', 'Cotisation de Awa enregistrée (en retard : pénalité appliquée).');

        $this->assertSame(2000, Contribution::sole()->penalty);
    }

    public function test_cash_payment_drops_the_reference(): void
    {
        $this->pay($this->tontine->members()->first(), '2026-03-05', 'cash', 'ignored');

        $this->assertNull(Contribution::sole()->reference);
    }

    public function test_a_member_cannot_pay_twice_for_the_same_round(): void
    {
        $awa = $this->tontine->members()->first();
        $this->pay($awa, '2026-03-05');

        $this->pay($awa, '2026-03-06')->assertSessionHas('error', 'Awa a déjà cotisé pour ce tour.');
        $this->assertDatabaseCount('contributions', 1);
    }

    public function test_payment_validation(): void
    {
        $outsider = Member::factory()->create();

        $this->post(route('contributions.store', [$this->tontine, 1]), [
            'member_id' => $outsider->id, 'method' => 'bitcoin', 'paid_on' => '2026-03-20',
        ])->assertSessionHasErrors(['member_id', 'method', 'paid_on']);
        $this->assertDatabaseCount('contributions', 0);
    }

    public function test_pot_is_handed_over_only_when_everyone_paid(): void
    {
        [$awa, $yao, $koffi] = $this->tontine->members()->get();
        $this->pay($awa, '2026-03-05');
        $this->pay($yao, '2026-03-05');

        $this->post(route('rounds.payout', [$this->tontine, 1]))
            ->assertSessionHas('error', 'Tous les membres doivent avoir cotisé avant de remettre la cagnotte.');

        $this->pay($koffi, '2026-03-06');
        $this->post(route('rounds.payout', [$this->tontine, 1]))->assertRedirect(route('tontines.show', $this->tontine));

        $round = $this->tontine->rounds()->first();
        $this->assertNotNull($round->paid_out_at);
        $this->assertSame(TontineStatus::Active, $this->tontine->fresh()->status);
    }

    public function test_paid_out_round_is_frozen(): void
    {
        $members = $this->tontine->members()->get();
        foreach ($members as $member) {
            $this->pay($member, '2026-03-05');
        }
        $this->post(route('rounds.payout', [$this->tontine, 1]));

        $this->delete(route('contributions.destroy', [$this->tontine, 1, Contribution::first()]))->assertSessionHas('error');
        $this->post(route('rounds.payout', [$this->tontine, 1]))->assertSessionHas('error', 'Cette cagnotte a déjà été remise.');
        $this->assertDatabaseCount('contributions', 3);
    }

    public function test_a_mistaken_payment_can_be_cancelled_before_payout(): void
    {
        $this->pay($this->tontine->members()->first(), '2026-03-05');

        $this->delete(route('contributions.destroy', [$this->tontine, 1, Contribution::sole()]))->assertSessionHas('success');
        $this->assertDatabaseCount('contributions', 0);
    }

    public function test_tontine_completes_after_the_last_payout_and_dashboard_totals_add_up(): void
    {
        $members = $this->tontine->members()->get();
        foreach ($this->tontine->rounds()->get() as $round) {
            foreach ($members as $member) {
                $this->post(route('contributions.store', [$this->tontine, $round->number]), [
                    'member_id' => $member->id, 'method' => 'cash', 'paid_on' => '2026-03-09',
                ]);
            }
            $this->post(route('rounds.payout', [$this->tontine, $round->number]));
        }

        $this->assertSame(TontineStatus::Completed, $this->tontine->fresh()->status);
        // tour 1 (échéance 05/03) payé le 09/03 : 3 pénalités ; tours 2 et 3 payés en avance
        $this->get(route('tontines.show', $this->tontine))->assertOk()
            ->assertSee('Tontine terminée')
            ->assertSee("225\u{202F}000\u{00A0}FCFA")
            ->assertSee("6\u{202F}000\u{00A0}FCFA");
    }
}

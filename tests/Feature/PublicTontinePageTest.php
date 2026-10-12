<?php

namespace Tests\Feature;

use App\Actions\RecordContribution;
use App\Actions\StartTontine;
use App\Models\Member;
use App\Models\Tontine;
use App\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTontinePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_members_see_order_and_payments_without_phone_numbers(): void
    {
        $tontine = Tontine::factory()->create(['name' => 'Tontine des collègues', 'starts_on' => now()->toDateString()]);
        [$awa, $yao] = Member::factory()->for($tontine)->count(2)->sequence(
            ['name' => 'Awa Traoré', 'phone' => '0707123456'], ['name' => 'Yao Kouassi', 'phone' => '0505234567'],
        )->create();
        app(StartTontine::class)->handle($tontine);
        app(RecordContribution::class)->handle($tontine->rounds()->first(), $awa, PaymentMethod::Wave, now()->toImmutable());

        $response = $this->get(route('public.show', $tontine->public_token))->assertOk();

        $response->assertSee('Tontine des collègues')
            ->assertSee('Awa Traoré reçoit la cagnotte')
            ->assertSee('✓ Awa Traoré')
            ->assertSee('1/2 cotisations')
            ->assertSee('07 •• •• •• 56')
            ->assertDontSee('0707123456')
            ->assertDontSee('0505234567');
    }

    public function test_unknown_token_is_not_found(): void
    {
        $this->get('/t/nimporte-quoi')->assertNotFound();
    }
}

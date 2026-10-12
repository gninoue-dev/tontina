<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_explains_the_problem(): void
    {
        $this->get('/')->assertOk()->assertSee('cahier')->assertSee('Créer ma tontine');
    }

    public function test_an_organizer_can_register_and_lands_on_their_tontines(): void
    {
        $response = $this->post('/inscription', [
            'name' => 'Aminata Koné',
            'email' => 'aminata@example.ci',
            'password' => 'motdepasse123',
            'password_confirmation' => 'motdepasse123',
        ]);

        $response->assertRedirect(route('tontines.index'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'aminata@example.ci']);
    }

    public function test_registration_errors_are_in_french(): void
    {
        $this->from('/inscription')->post('/inscription', ['name' => '', 'email' => 'pas-un-email', 'password' => 'court'])
            ->assertSessionHasErrors([
                'name' => 'Le champ nom est obligatoire.',
                'email' => 'Le champ e-mail doit être une adresse e-mail valide.',
            ]);
        $this->assertGuest();
    }

    public function test_login_with_wrong_password_is_refused(): void
    {
        $user = User::factory()->create();

        $this->post('/connexion', ['email' => $user->email, 'password' => 'faux'])
            ->assertSessionHasErrors(['email' => 'E-mail ou mot de passe incorrect.']);
        $this->assertGuest();
    }

    public function test_login_and_logout(): void
    {
        $user = User::factory()->create();

        $this->post('/connexion', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('tontines.index'));
        $this->assertAuthenticatedAs($user);

        $this->post('/deconnexion')->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->create();
        foreach (range(1, 6) as $attempt) {
            $this->post('/connexion', ['email' => $user->email, 'password' => 'faux']);
        }

        $this->post('/connexion', ['email' => $user->email, 'password' => 'password'])->assertTooManyRequests();
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/tontines')->assertRedirect(route('login'));
    }
}

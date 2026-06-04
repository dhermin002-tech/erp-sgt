<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    // ── Accès ────────────────────────────────────────────────────────────────

    public function test_page_login_accessible(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_invite_redirige_vers_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    // ── Login valide ─────────────────────────────────────────────────────────

    public function test_login_valide_redirige_vers_dashboard(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret')]);

        $this->post('/login', ['username' => $user->username, 'password' => 'secret'])
             ->assertRedirect('/dashboard');
    }

    // ── Login invalide ────────────────────────────────────────────────────────

    public function test_mauvais_mot_de_passe_retourne_erreur(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret')]);

        $this->post('/login', ['username' => $user->username, 'password' => 'mauvais'])
             ->assertSessionHasErrors('username');
    }

    public function test_username_inexistant_retourne_erreur(): void
    {
        $this->post('/login', ['username' => 'inexistant', 'password' => 'secret'])
             ->assertSessionHasErrors('username');
    }

    public function test_champs_vides_retournent_erreurs_validation(): void
    {
        $this->post('/login', [])
             ->assertSessionHasErrors(['username', 'password']);
    }

    // ── RBAC accès par rôle ──────────────────────────────────────────────────

    public function test_manager_accede_au_dashboard(): void
    {
        $manager = User::factory()->manager()->create();
        $this->actingAs($manager)->get('/dashboard')->assertStatus(200);
    }

    public function test_technicien_accede_au_dashboard(): void
    {
        $tech = User::factory()->technicien()->create();
        $this->actingAs($tech)->get('/dashboard')->assertStatus(200);
    }

    public function test_agent_accede_au_dashboard(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $this->actingAs($agent)->get('/dashboard')->assertStatus(200);
    }

    public function test_stagiaire_accede_au_dashboard(): void
    {
        $stagiaire = User::factory()->stagiaire()->create();
        $this->actingAs($stagiaire)->get('/dashboard')->assertStatus(200);
    }

    // ── Logout ───────────────────────────────────────────────────────────────

    public function test_logout_deconnecte_et_redirige(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
             ->post('/logout')
             ->assertRedirect('/login');
        $this->assertGuest();
    }
}

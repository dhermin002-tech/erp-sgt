<?php

namespace Tests\Feature\Preferences;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreferenceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'agent', 'direction_ui' => 'A']);
    }

    // ── Locale FR/EN ─────────────────────────────────────────────────────────

    public function test_basculer_locale_en_fr(): void
    {
        $this->actingAs($this->user)
             ->post(route('preferences.locale'), ['locale' => 'fr'])
             ->assertRedirect();

        $this->assertEquals('fr', session('locale'));
    }

    public function test_basculer_locale_en_en(): void
    {
        $this->actingAs($this->user)
             ->post(route('preferences.locale'), ['locale' => 'en'])
             ->assertRedirect();

        $this->assertEquals('en', session('locale'));
    }

    public function test_locale_invalide_rejetee(): void
    {
        $this->actingAs($this->user)
             ->post(route('preferences.locale'), ['locale' => 'de'])
             ->assertStatus(400);
    }

    // ── Direction A/B ─────────────────────────────────────────────────────────

    public function test_basculer_direction_b(): void
    {
        $this->actingAs($this->user)
             ->patchJson(route('preferences.direction'), ['direction' => 'B'])
             ->assertJson(['ok' => true, 'direction' => 'B']);

        $this->assertEquals('B', $this->user->fresh()->direction_ui);
    }

    public function test_basculer_direction_a(): void
    {
        $this->user->update(['direction_ui' => 'B']);

        $this->actingAs($this->user)
             ->patchJson(route('preferences.direction'), ['direction' => 'A'])
             ->assertJson(['ok' => true, 'direction' => 'A']);

        $this->assertEquals('A', $this->user->fresh()->direction_ui);
    }

    public function test_direction_invalide_rejetee(): void
    {
        $this->actingAs($this->user)
             ->patchJson(route('preferences.direction'), ['direction' => 'C'])
             ->assertStatus(400);
    }

    public function test_direction_persistee_en_bdd(): void
    {
        $this->actingAs($this->user)
             ->patchJson(route('preferences.direction'), ['direction' => 'B']);

        $this->assertDatabaseHas('users', [
            'id'           => $this->user->id,
            'direction_ui' => 'B',
        ]);
    }

    // ── Locale dans le layout ─────────────────────────────────────────────────

    public function test_locale_appliquee_dans_les_vues(): void
    {
        $session = $this->actingAs($this->user);
        $session->post(route('preferences.locale'), ['locale' => 'en']);

        // Après switch EN, les pages doivent charger sans erreur
        $this->actingAs($this->user)
             ->withSession(['locale' => 'en'])
             ->get('/dashboard')
             ->assertStatus(200);
    }

    // ── Filtres tableau tâches (FEAT-012) ─────────────────────────────────────

    public function test_filtre_statut_taches(): void
    {
        $this->actingAs($this->user)
             ->get('/taches?statut=en_cours')
             ->assertStatus(200);
    }

    public function test_recherche_plein_texte(): void
    {
        $this->actingAs($this->user)
             ->get('/taches?q=installation')
             ->assertStatus(200);
    }

    public function test_tri_par_date_echeance(): void
    {
        $this->actingAs($this->user)
             ->get('/taches?sort=date_echeance&dir=asc')
             ->assertStatus(200);
    }

    public function test_locale_permanente_prime_sur_session_avec_repli_pour_ancien_compte(): void
    {
        $this->actingAs($this->user)->withSession(['locale' => 'en'])->get('/dashboard')->assertOk();
        $this->assertSame('en', app()->getLocale());

        $this->post(route('preferences.locale'), ['locale' => 'fr'])->assertRedirect();
        $this->assertSame('fr', $this->user->fresh()->locale);
        $this->actingAs($this->user->fresh())->withSession(['locale' => 'en'])->get('/dashboard')->assertOk();
        $this->assertSame('fr', app()->getLocale());
    }

    public function test_mot_de_passe_actuel_absent_des_donnees_flash_web(): void
    {
        $this->actingAs($this->user)->from('/profil')->patch(route('profil.updatePassword'), [
            'password_actuel' => 'secret-de-recette', 'password' => '',
        ])->assertRedirect('/profil')->assertSessionHasErrors('password')
            ->assertSessionMissing('_old_input.password_actuel');
    }

}

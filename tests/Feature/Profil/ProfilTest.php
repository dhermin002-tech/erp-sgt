<?php

namespace Tests\Feature\Profil;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfilTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'nom'       => 'Dingoka',
            'prenom'    => 'Hermin',
            'telephone' => null,
            'password'  => Hash::make('secret123'),
        ]);
    }

    // ── Accès ────────────────────────────────────────────────────────────────

    public function test_invite_ne_peut_pas_acceder_au_profil(): void
    {
        $this->get(route('profil.index'))->assertRedirect('/login');
    }

    public function test_utilisateur_connecte_voit_sa_page_profil(): void
    {
        $this->actingAs($this->user)
            ->get(route('profil.index'))
            ->assertOk()
            ->assertViewIs('profil.index')
            ->assertViewHas('user', $this->user);
    }

    // ── updateInfos ──────────────────────────────────────────────────────────

    public function test_mise_a_jour_infos_personnelles(): void
    {
        $this->actingAs($this->user)
            ->patch(route('profil.updateInfos'), [
                'nom'       => 'Nkeret',
                'prenom'    => 'Jean',
                'telephone' => '062740860',
            ])
            ->assertRedirect()
            ->assertSessionHas('success_infos');

        $this->assertDatabaseHas('users', [
            'id'        => $this->user->id,
            'nom'       => 'Nkeret',
            'prenom'    => 'Jean',
            'telephone' => '062740860',
        ]);
    }

    public function test_nom_obligatoire_pour_maj_infos(): void
    {
        $this->actingAs($this->user)
            ->patch(route('profil.updateInfos'), ['nom' => '', 'prenom' => 'Jean'])
            ->assertSessionHasErrors('nom');
    }

    public function test_telephone_optionnel(): void
    {
        $this->actingAs($this->user)
            ->patch(route('profil.updateInfos'), ['nom' => 'Dingoka', 'prenom' => null])
            ->assertSessionHas('success_infos');
    }

    // ── updatePassword ───────────────────────────────────────────────────────

    public function test_changement_mot_de_passe_valide(): void
    {
        $this->actingAs($this->user)
            ->patch(route('profil.updatePassword'), [
                'password_actuel'          => 'secret123',
                'password'                 => 'nouveau2026!',
                'password_confirmation'    => 'nouveau2026!',
            ])
            ->assertRedirect()
            ->assertSessionHas('success_password');

        $this->user->refresh();
        $this->assertTrue(Hash::check('nouveau2026!', $this->user->password));
    }

    public function test_mauvais_mot_de_passe_actuel_retourne_erreur(): void
    {
        $this->actingAs($this->user)
            ->patch(route('profil.updatePassword'), [
                'password_actuel'       => 'mauvais',
                'password'              => 'nouveau2026!',
                'password_confirmation' => 'nouveau2026!',
            ])
            ->assertSessionHasErrors('password_actuel');
    }

    public function test_confirmation_mot_de_passe_incorrecte(): void
    {
        $this->actingAs($this->user)
            ->patch(route('profil.updatePassword'), [
                'password_actuel'       => 'secret123',
                'password'              => 'nouveau2026!',
                'password_confirmation' => 'autre_chose',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_nouveau_mot_de_passe_trop_court(): void
    {
        $this->actingAs($this->user)
            ->patch(route('profil.updatePassword'), [
                'password_actuel'       => 'secret123',
                'password'              => 'abc',
                'password_confirmation' => 'abc',
            ])
            ->assertSessionHasErrors('password');
    }

    // ── updatePreferences ────────────────────────────────────────────────────

    public function test_mise_a_jour_direction_ui(): void
    {
        $this->actingAs($this->user)
            ->patch(route('profil.updatePreferences'), [
                'direction_ui' => 'B',
                'locale'       => 'fr',
            ])
            ->assertRedirect()
            ->assertSessionHas('success_prefs');

        $this->assertDatabaseHas('users', [
            'id'           => $this->user->id,
            'direction_ui' => 'B',
        ]);
    }

    public function test_direction_ui_valeur_invalide(): void
    {
        $this->actingAs($this->user)
            ->patch(route('profil.updatePreferences'), [
                'direction_ui' => 'C',
                'locale'       => 'fr',
            ])
            ->assertSessionHasErrors('direction_ui');
    }

    public function test_locale_invalide_retourne_erreur(): void
    {
        $this->actingAs($this->user)
            ->patch(route('profil.updatePreferences'), [
                'direction_ui' => 'A',
                'locale'       => 'de',
            ])
            ->assertSessionHasErrors('locale');
    }
}

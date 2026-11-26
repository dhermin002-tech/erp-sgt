<?php

namespace Tests\Feature\Membres;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MotDePasseMembreTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_refuse_un_champ_mot_de_passe_meme_vide(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $hash = $manager->password;
        Sanctum::actingAs($manager, ['mobile']);

        foreach (['mot-de-passe-factice', null] as $password) {
            $this->patchJson('/api/v1/gestion/membres/'.$manager->id, [
                'nom' => 'Recette', 'role' => 'manager',
                'password' => $password, 'password_confirmation' => $password,
            ])->assertUnprocessable()->assertJsonValidationErrors('password');
        }
        $this->assertSame($hash, $manager->fresh()->password);
    }

    public function test_changer_le_mot_de_passe_depuis_le_web_revoque_les_jetons_du_membre(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $membre = User::factory()->create(['role' => 'technicien']);
        $membre->createToken('recette mobile', ['mobile']);
        $membre->createToken('recette MCP', ['mcp:read']);
        $jetonManager = $manager->createToken('recette manager', ['mobile']);

        $this->actingAs($manager)->patch(route('membres.update', $membre), [
            'nom' => $membre->nom, 'role' => $membre->role,
            'password' => 'nouveau-factice', 'password_confirmation' => 'nouveau-factice',
        ])->assertRedirect(route('membres.index'))->assertSessionHas('success');

        $this->assertTrue(Hash::check('nouveau-factice', $membre->fresh()->password));
        $this->assertSame(0, $membre->tokens()->count());
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $jetonManager->accessToken->id]);
    }

    public function test_b1_refuse_put_et_corps_formulaire_et_le_scope_mcp_en_gestion(): void
    {
        $manager = User::factory()->manager()->create();
        $membre = User::factory()->create();
        $hash = $membre->password;
        $url = '/api/v1/gestion/membres/'.$membre->id;
        $donnees = ['nom' => $membre->nom, 'role' => $membre->role,
            'password' => 'factice-123', 'password_confirmation' => 'factice-123'];
        Sanctum::actingAs($manager, ['mobile']);
        $this->putJson($url, $donnees)->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->patch($url, $donnees)->assertUnprocessable()->assertJsonValidationErrors('password');
        Sanctum::actingAs($manager, ['mcp:write']);
        $this->patchJson($url, $donnees)->assertForbidden();
        $this->assertSame($hash, $membre->fresh()->password);
    }

    public function test_manager_ne_change_pas_son_propre_mot_de_passe_depuis_les_membres(): void
    {
        $manager = User::factory()->manager()->create();
        $hash = $manager->password;
        $this->actingAs($manager)->from(route('membres.edit', $manager))->patch(route('membres.update', $manager), [
            'nom' => $manager->nom, 'role' => 'manager', 'password' => 'factice-123',
            'password_confirmation' => 'factice-123',
        ])->assertRedirect(route('membres.edit', $manager))->assertSessionHasErrors('password');
        $this->assertSame($hash, $manager->fresh()->password);
    }

    public function test_profil_web_revoque_ses_jetons_et_conserve_la_session_courante(): void
    {
        $compte = User::factory()->create(['password' => 'ancien-factice']);
        $autre = User::factory()->create();
        $compte->createToken('recette mobile', ['mobile']);
        $compte->createToken('recette MCP', ['mcp:read']);
        $conserve = $autre->createToken('autre compte', ['mobile']);
        $this->actingAs($compte)->from('/profil')->patch(route('profil.updatePassword'), [
            'password_actuel' => 'ancien-factice', 'password' => 'nouveau-factice',
            'password_confirmation' => 'nouveau-factice',
        ])->assertRedirect('/profil')->assertSessionHas('success_password');
        $this->assertTrue(Hash::check('nouveau-factice', $compte->fresh()->password));
        $this->assertSame(0, $compte->tokens()->count());
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $conserve->accessToken->id]);
        $this->get('/profil')->assertOk();
    }

    public function test_limite_web_et_regle_partagee_en_octets(): void
    {
        $manager = User::factory()->manager()->create();
        $membre = User::factory()->create();
        $this->actingAs($manager);
        foreach ([str_repeat('é', 37), '1234567', 'éééé'] as $password) {
            $this->patchJson(route('membres.update', $membre), [
                'nom' => $membre->nom, 'role' => $membre->role,
                'password' => $password, 'password_confirmation' => $password,
            ])->assertUnprocessable()->assertJsonValidationErrors('password');
            $this->postJson(route('membres.store'), [
                'username' => 'recette-octets', 'nom' => 'Recette', 'role' => 'agent',
                'password' => $password, 'password_confirmation' => $password,
            ])->assertUnprocessable()->assertJsonValidationErrors('password');
        }
        $limite = str_repeat('é', 36);
        $this->post(route('membres.store'), [
            'username' => 'recette-octets', 'nom' => 'Recette', 'role' => 'agent',
            'password' => $limite, 'password_confirmation' => $limite,
        ])->assertRedirect(route('membres.index'))->assertSessionHas('success');
        $this->assertTrue(Hash::check($limite, User::where('username', 'recette-octets')->firstOrFail()->password));

        for ($tentative = 0; $tentative < 5; $tentative++) {
            $this->patchJson(route('profil.updatePassword'))->assertUnprocessable();
        }
        $this->patchJson(route('profil.updatePassword'))->assertTooManyRequests();
    }


    public function test_scope_mcp_ne_peut_pas_modifier_le_mot_de_passe_du_profil_api(): void
    {
        $compte = User::factory()->manager()->create();
        Sanctum::actingAs($compte, ['mcp:write']);
        $this->patchJson('/api/v1/profil/password')->assertForbidden();
    }
}

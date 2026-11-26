<?php

namespace Tests\Feature\Membres;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GestionWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_membres_et_sites_valident_les_donnees_du_manager(): void
    {
        $this->actingAs(User::factory()->manager()->create());
        $membre = User::factory()->create();
        $site = Site::create(['nom' => 'Site existant', 'ville' => 'Libreville']);

        $this->postJson(route('membres.store'), [])->assertUnprocessable()
            ->assertJsonValidationErrors(['username', 'nom', 'role', 'password']);
        $this->putJson(route('membres.update', $membre), ['role' => 'inconnu'])
            ->assertUnprocessable()->assertJsonValidationErrors('role');
        $this->postJson(route('sites.store'), [])->assertUnprocessable()
            ->assertJsonValidationErrors('nom');
        $this->putJson(route('sites.update', $site), ['nom' => ''])
            ->assertUnprocessable()->assertJsonValidationErrors('nom');
    }

    public function test_web_membres_et_sites_refusent_les_ecritures_du_technicien(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'technicien']));
        $ressources = ['membres' => User::factory()->create(), 'sites' => Site::create(['nom' => 'Site existant', 'ville' => 'Libreville'])];

        foreach ($ressources as $nom => $ressource) {
            $this->postJson(route($nom.'.store'), [])->assertForbidden();
            $this->putJson(route($nom.'.update', $ressource), [])->assertForbidden();
            $this->deleteJson(route($nom.'.destroy', $ressource))->assertForbidden();
            $this->assertModelExists($ressource);
        }
    }

    public function test_crud_web_membres_conserve_vues_redirections_et_messages(): void
    {
        $this->actingAs(User::factory()->manager()->create());
        $this->get(route('membres.index'))->assertOk()->assertViewIs('membres.index');
        $this->post(route('membres.store'), [
            'username' => 'recette-web', 'nom' => 'Recette', 'role' => 'technicien',
            'password' => 'factice-web-123', 'password_confirmation' => 'factice-web-123',
        ])->assertRedirect(route('membres.index'))->assertSessionHas('success');
        $membre = User::where('username', 'recette-web')->firstOrFail();
        $jeton = $membre->createToken('recette sans modification', ['mobile']);
        $this->get(route('membres.edit', $membre))->assertOk()->assertViewIs('membres.edit');
        $this->put(route('membres.update', $membre), ['nom' => 'Corrigé', 'role' => 'agent'])
            ->assertRedirect(route('membres.index'))->assertSessionHas('success');
        $this->assertSame('Corrigé', $membre->fresh()->nom);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $jeton->accessToken->id]);
        $this->delete(route('membres.destroy', $membre))
            ->assertRedirect(route('membres.index'))->assertSessionHas('success');
        $this->assertSoftDeleted($membre);
    }

    public function test_crud_web_sites_conserve_vues_redirections_et_messages(): void
    {
        $this->actingAs(User::factory()->manager()->create());
        $this->get(route('sites.index'))->assertOk()->assertViewIs('sites.index');
        $this->post(route('sites.store'), ['nom' => 'Site recette', 'ville' => 'Libreville'])
            ->assertRedirect(route('sites.index'))->assertSessionHas('success');
        $site = Site::where('nom', 'Site recette')->firstOrFail();
        $this->get(route('sites.show', $site))->assertOk()->assertViewIs('sites.show');
        $this->put(route('sites.update', $site), ['nom' => $site->nom, 'ville' => 'Akanda'])
            ->assertRedirect(route('sites.index'))->assertSessionHas('success');
        $this->assertSame('Akanda', $site->fresh()->ville);
        $this->delete(route('sites.destroy', $site))
            ->assertRedirect(route('sites.index'))->assertSessionHas('success');
        $this->assertModelMissing($site);
    }
}

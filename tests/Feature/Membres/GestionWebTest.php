<?php

namespace Tests\Feature\Membres;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GestionWebTest extends TestCase
{
    use RefreshDatabase;

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

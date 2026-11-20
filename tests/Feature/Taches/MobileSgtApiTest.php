<?php

namespace Tests\Feature\Taches;

use App\Models\Tache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileSgtApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_web_par_cookie_et_deconnexion(): void
    {
        config(['sanctum.stateful' => ['sgt.test']]);
        $user = User::factory()->create(['password' => 'saisie-factice']);
        $resultat = $this->postJson('/mobile/session', [
            'username' => $user->username, 'password' => 'saisie-factice',
        ])->assertNoContent();
        $cookie = $resultat->getCookie(config('session.cookie'), false);
        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertDatabaseCount('personal_access_tokens', 0);
        Auth::forgetGuards();
        $this->withUnencryptedCookie($cookie->getName(), $cookie->getValue())
            ->withHeader('Origin', 'http://sgt.test')->getJson('/api/v1/me')
            ->assertOk()->assertJsonPath('id', $user->id);
        $this->deleteJson('/mobile/session')->assertNoContent();
        Auth::forgetGuards();
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_un_compte_agent_ia_ne_peut_pas_ouvrir_la_session_web_mobile(): void
    {
        $agent = User::factory()->create(['type_compte' => 'agent_ia', 'role' => 'agent', 'password' => 'saisie-factice']);
        $this->postJson('/mobile/session', ['username' => $agent->username, 'password' => 'saisie-factice'])
            ->assertUnprocessable()->assertJsonValidationErrors('username');
    }

    public function test_mobile_filtre_le_projet_et_retrouve_les_taches_terminees(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'manager']), ['mobile']);
        $terminee = Tache::factory()->create(['projet' => 'SGT', 'statut' => 'termine']);
        Tache::factory()->create(['projet' => 'SGT', 'statut' => 'en_cours']);
        Tache::factory()->create(['projet' => 'Autre', 'statut' => 'termine']);
        $this->getJson('/api/v1/taches?statut=termine&projet=SGT')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $terminee->id)
            ->assertJsonPath('data.0.projet', 'SGT')->assertJsonPath('meta.par_statut.termine', 1);
    }

    public function test_historique_des_statuts_et_rapports_respectent_la_visibilite(): void
    {
        $tache = Tache::factory()->create(['statut' => 'nouveau']);
        $base = '/api/v1/taches/'.$tache->id;
        $user = User::factory()->create(['role' => 'technicien']);
        Sanctum::actingAs($user, ['mobile']);
        $this->getJson($base.'/historique')->assertForbidden();
        $this->postJson($base.'/rapports', ['contenu' => 'Contrôle du dossier'])->assertForbidden();

        $tache->responsables()->attach($user);
        $this->patchJson($base.'/statut', ['statut' => 'en_cours'])->assertOk();
        $this->getJson($base.'/historique')->assertOk()
            ->assertJsonPath('data.0.ancien', 'nouveau')->assertJsonPath('data.0.nouveau', 'en_cours')
            ->assertJsonPath('data.0.acteur', $user->nom_complet);
        $this->postJson($base.'/rapports', ['contenu' => 'Contrôle du dossier'])->assertCreated();
        $this->getJson($base.'/rapports')->assertOk()->assertJsonPath('data.0.contenu', 'Contrôle du dossier');
    }
}

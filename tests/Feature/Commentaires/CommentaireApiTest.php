<?php

namespace Tests\Feature\Commentaires;

use App\Models\Tache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentaireApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_responsable_peut_ajouter_et_lire_un_commentaire_nettoye(): void
    {
        $tache = Tache::factory()->create();
        $responsable = User::factory()->create();
        $tache->responsables()->attach($responsable);
        Sanctum::actingAs($responsable, ['taches:update', 'taches:read']);

        $url = "/api/v1/taches/{$tache->id}/commentaires";
        $this->postJson($url, ['contenu' => '<b>Point vérifié</b>'])
            ->assertCreated()->assertJsonPath('data.contenu', 'Point vérifié')
            ->assertJsonPath('data.auteur.id', $responsable->id);
        $this->getJson($url)->assertOk()->assertJsonPath('meta.total', 1)
            ->assertJsonStructure(['data' => [['created_at', 'auteur']], 'links']);
        $this->postJson($url, ['contenu' => '<br>'])->assertUnprocessable();
        $this->postJson($url, ['contenu' => str_repeat('a', 5001)])->assertUnprocessable();
    }

    public function test_les_permissions_suivent_les_regles_du_web(): void
    {
        $tache = Tache::factory()->create();
        $auteur = User::factory()->create();
        $commentaire = $tache->commentaires()->create(['contenu' => 'Suivi', 'user_id' => $auteur->id]);
        $url = "/api/v1/taches/{$tache->id}/commentaires";
        $suppression = "/api/v1/commentaires/{$commentaire->id}";

        $this->getJson($url)->assertUnauthorized();
        Sanctum::actingAs(User::factory()->create(), ['mcp:write']);
        $this->getJson($url)->assertForbidden();
        $this->postJson($url, ['contenu' => 'Suivi'])->assertForbidden();
        $this->deleteJson($suppression)->assertForbidden();

        Sanctum::actingAs($auteur, ['taches:read']);
        $this->deleteJson($suppression)->assertForbidden();
        Sanctum::actingAs($auteur, ['taches:update']);
        $this->deleteJson($suppression)->assertNoContent();
        $this->assertSoftDeleted($commentaire);
    }
}

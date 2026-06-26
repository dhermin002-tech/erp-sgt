<?php

namespace Tests\Feature\Taches;

use App\Models\Tache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_peut_passer_une_tache_a_termine(): void
    {
        $manager = User::factory()->manager()->create();
        $tache   = Tache::factory()->create(['statut' => 'en_cours']);

        $res = $this->actingAs($manager)
            ->patchJson("/taches/{$tache->id}/statut", ['statut' => 'termine']);

        $res->assertOk()->assertJson(['ok' => true, 'statut' => 'termine']);
        $this->assertSame('termine', $tache->fresh()->statut);
    }

    public function test_tache_terminee_absente_de_index_et_presente_dans_historique(): void
    {
        $manager = User::factory()->manager()->create();
        $tache   = Tache::factory()->create(['statut' => 'en_cours', 'titre' => 'TACHE_TEMOIN_XYZ']);

        // Validation
        $this->actingAs($manager)
            ->patchJson("/taches/{$tache->id}/statut", ['statut' => 'termine'])
            ->assertOk();

        // Pas d'archivage automatique
        $this->assertNull($tache->fresh()->archived_at, 'la validation ne doit pas archiver');

        // La tâche terminée N'apparaît PLUS dans /taches (vue actives)
        $this->actingAs($manager)->get('/taches')
            ->assertDontSee('TACHE_TEMOIN_XYZ');

        // Elle est bien présente dans /taches/archives (historique)
        $this->actingAs($manager)->get('/taches/archives')
            ->assertSee('TACHE_TEMOIN_XYZ');
    }

    public function test_tache_archivee_presente_dans_historique(): void
    {
        $manager = User::factory()->manager()->create();
        $tache   = Tache::factory()->create(['statut' => 'termine', 'titre' => 'TACHE_ARCHIVEE_ABC']);

        // Archivage manuel
        $this->actingAs($manager)->patch("/taches/{$tache->id}/archiver")->assertRedirect();
        $this->assertNotNull($tache->fresh()->archived_at);

        // Présente dans l'historique
        $this->actingAs($manager)->get('/taches/archives')
            ->assertSee('TACHE_ARCHIVEE_ABC');

        // Absente de l'index actif
        $this->actingAs($manager)->get('/taches')
            ->assertDontSee('TACHE_ARCHIVEE_ABC');
    }

    public function test_manager_peut_archiver_manuellement_une_tache(): void
    {
        $manager = User::factory()->manager()->create();
        $tache   = Tache::factory()->create(['statut' => 'termine']);

        $this->actingAs($manager)
            ->patch("/taches/{$tache->id}/archiver")
            ->assertRedirect(route('taches.index'));

        $this->assertNotNull($tache->fresh()->archived_at);

        // Une fois archivée, elle quitte la liste.
        $this->actingAs($manager)->get('/taches')
            ->assertDontSee($tache->titre);
    }

    public function test_page_tache_ne_redeclare_pas_csrftoken(): void
    {
        // Garde-fou : 'const csrfToken' doit n'apparaître qu'UNE fois (dans le layout).
        // Une 2e déclaration au niveau global = SyntaxError qui tue tout le <script>
        // de la page → boutons "valider" morts sans aucune alerte.
        $manager = User::factory()->manager()->create();
        $tache   = Tache::factory()->create();

        $html = $this->actingAs($manager)->get("/taches/{$tache->id}")->getContent();

        $this->assertSame(
            1,
            substr_count($html, 'const csrfToken'),
            "csrfToken ne doit être déclaré qu'une seule fois (layout)."
        );
    }

    public function test_manager_peut_cocher_une_sous_tache(): void
    {
        $manager = User::factory()->manager()->create();
        $tache   = Tache::factory()->create(['statut' => 'en_cours']);
        $st      = $tache->sousTaches()->create(['titre' => 'Etape 1', 'termine' => false]);

        $res = $this->actingAs($manager)
            ->patchJson("/sous-taches/{$st->id}/toggle", ['termine' => true]);

        $res->assertOk()->assertJson(['ok' => true]);
        $this->assertTrue((bool) $st->fresh()->termine);
    }
}

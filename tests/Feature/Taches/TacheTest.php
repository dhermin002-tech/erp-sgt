<?php

namespace Tests\Feature\Taches;

use App\Models\Site;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TacheTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;
    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = User::factory()->manager()->create();
        $this->agent   = User::factory()->create(['role' => 'agent']);
    }

    // ── Accès ─────────────────────────────────────────────────────────────────

    public function test_invite_ne_peut_pas_voir_les_taches(): void
    {
        $this->get('/taches')->assertRedirect('/login');
    }

    public function test_manager_voit_la_liste(): void
    {
        $this->actingAs($this->manager)->get('/taches')->assertStatus(200);
    }

    public function test_agent_voit_la_liste(): void
    {
        $this->actingAs($this->agent)->get('/taches')->assertStatus(200);
    }

    // ── CRUD ─────────────────────────────────────────────────────────────────

    public function test_manager_peut_creer_une_tache(): void
    {
        $site = Site::factory()->create();

        $this->actingAs($this->manager)
             ->post('/taches', [
                 'titre'         => 'Installation réseau Akanda',
                 'description'   => 'Câblage LAN immeuble 3 étages',
                 'responsables'  => [$this->agent->id],
                 'site_id'       => $site->id,
                 'date_debut'    => now()->format('Y-m-d'),
                 'date_echeance' => now()->addDays(7)->format('Y-m-d'),
                 'statut'        => 'nouveau',
                 'progression'   => 0,
                 'priorite'      => 'haute',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('taches', ['titre' => 'Installation réseau Akanda']);
    }

    public function test_validation_echoue_sans_titre(): void
    {
        $this->actingAs($this->manager)
             ->post('/taches', ['titre' => '', 'responsables' => [$this->agent->id], 'statut' => 'nouveau', 'progression' => 0, 'priorite' => 'normale'])
             ->assertSessionHasErrors('titre');
    }

    public function test_validation_echoue_sans_responsable(): void
    {
        $this->actingAs($this->manager)
             ->post('/taches', ['titre' => 'Test', 'responsables' => [], 'statut' => 'nouveau', 'progression' => 0, 'priorite' => 'normale'])
             ->assertSessionHasErrors('responsables');
    }

    // ── RBAC ─────────────────────────────────────────────────────────────────

    public function test_agent_ne_voit_pas_les_taches_des_autres(): void
    {
        $autreAgent = User::factory()->create(['role' => 'agent']);
        $tache = Tache::factory()->create(['createur_id' => $this->manager->id]);
        $tache->responsables()->attach($autreAgent);

        $this->actingAs($this->agent)
             ->get("/taches/{$tache->id}")
             ->assertStatus(403);
    }

    public function test_agent_voit_sa_propre_tache(): void
    {
        $tache = Tache::factory()->create(['createur_id' => $this->manager->id]);
        $tache->responsables()->attach($this->agent);

        $this->actingAs($this->agent)
             ->get("/taches/{$tache->id}")
             ->assertStatus(200);
    }

    public function test_manager_voit_toutes_les_taches(): void
    {
        $autreUser = User::factory()->create();
        $tache = Tache::factory()->create(['createur_id' => $autreUser->id]);
        $tache->responsables()->attach($autreUser);

        $this->actingAs($this->manager)
             ->get("/taches/{$tache->id}")
             ->assertStatus(200);
    }

    // ── Statuts ───────────────────────────────────────────────────────────────

    public function test_changement_statut_ajax(): void
    {
        $tache = Tache::factory()->create(['createur_id' => $this->manager->id, 'statut' => 'nouveau']);
        $tache->responsables()->attach($this->manager);

        $this->actingAs($this->manager)
             ->patchJson("/taches/{$tache->id}/statut", ['statut' => 'en_cours'])
             ->assertJson(['ok' => true, 'statut' => 'en_cours']);

        $this->assertDatabaseHas('taches', ['id' => $tache->id, 'statut' => 'en_cours']);
    }

    public function test_tache_terminee_est_archivee(): void
    {
        $tache = Tache::factory()->create(['createur_id' => $this->manager->id, 'statut' => 'en_cours']);
        $tache->responsables()->attach($this->manager);

        $this->actingAs($this->manager)
             ->patchJson("/taches/{$tache->id}/statut", ['statut' => 'termine']);

        $this->assertNotNull($tache->fresh()->archived_at);
    }

    // ── Sous-tâches + progression ─────────────────────────────────────────────

    public function test_progression_calculee_depuis_sous_taches(): void
    {
        $tache = Tache::factory()->create(['createur_id' => $this->manager->id]);
        $tache->responsables()->attach($this->manager);
        $st1 = $tache->sousTaches()->create(['titre' => 'A', 'termine' => true, 'ordre' => 1]);
        $st2 = $tache->sousTaches()->create(['titre' => 'B', 'termine' => false, 'ordre' => 2]);

        $tache->recalculerProgression();

        $this->assertEquals(50, $tache->fresh()->progression);
    }

    // ── Archivage / restauration ──────────────────────────────────────────────

    public function test_restauration_remet_statut_nouveau(): void
    {
        $tache = Tache::factory()->create([
            'createur_id' => $this->manager->id,
            'statut'      => 'termine',
            'archived_at' => now(),
        ]);

        $this->actingAs($this->manager)
             ->patch("/taches/{$tache->id}/restaurer")
             ->assertRedirect();

        $tache->refresh();
        $this->assertEquals('nouveau', $tache->statut);
        $this->assertNull($tache->archived_at);
    }

    // ── Détection retard ─────────────────────────────────────────────────────

    public function test_tache_en_retard_detectee(): void
    {
        $tache = Tache::factory()->create([
            'createur_id'   => $this->manager->id,
            'date_echeance' => now()->subDay(),
            'statut'        => 'en_cours',
        ]);

        $this->assertTrue($tache->estEnRetard());
    }

    public function test_tache_terminee_non_en_retard(): void
    {
        $tache = Tache::factory()->create([
            'createur_id'   => $this->manager->id,
            'date_echeance' => now()->subDay(),
            'statut'        => 'termine',
        ]);

        $this->assertFalse($tache->estEnRetard());
    }
}

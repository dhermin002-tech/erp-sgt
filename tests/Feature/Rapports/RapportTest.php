<?php

namespace Tests\Feature\Rapports;

use App\Models\ActionSuivi;
use App\Models\Rapport;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RapportTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;
    private User $agent;
    private Tache $tache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = User::factory()->manager()->create();
        $this->agent   = User::factory()->create(['role' => 'agent']);
        $this->tache   = Tache::factory()->create(['createur_id' => $this->manager->id]);
        $this->tache->responsables()->attach($this->agent);
    }

    // ── Rapports ─────────────────────────────────────────────────────────────

    public function test_responsable_peut_ajouter_rapport(): void
    {
        $this->actingAs($this->agent)
             ->post("/taches/{$this->tache->id}/rapports", [
                 'contenu'           => 'Installation câblage LAN effectuée.',
                 'date_intervention' => now()->format('Y-m-d'),
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('rapports', [
            'tache_id' => $this->tache->id,
            'user_id'  => $this->agent->id,
            'contenu'  => 'Installation câblage LAN effectuée.',
        ]);
    }

    public function test_rapport_vide_retourne_erreur(): void
    {
        $this->actingAs($this->agent)
             ->post("/taches/{$this->tache->id}/rapports", ['contenu' => ''])
             ->assertSessionHasErrors('contenu');
    }

    public function test_auteur_peut_supprimer_son_rapport(): void
    {
        $rapport = Rapport::create([
            'tache_id' => $this->tache->id,
            'user_id'  => $this->agent->id,
            'contenu'  => 'Rapport test.',
        ]);

        $this->actingAs($this->agent)
             ->delete("/rapports/{$rapport->id}")
             ->assertRedirect();

        $this->assertDatabaseMissing('rapports', ['id' => $rapport->id]);
    }

    public function test_autre_user_ne_peut_pas_supprimer_rapport(): void
    {
        $autreAgent = User::factory()->create(['role' => 'agent']);
        $rapport = Rapport::create([
            'tache_id' => $this->tache->id,
            'user_id'  => $this->agent->id,
            'contenu'  => 'Rapport protégé.',
        ]);

        $this->actingAs($autreAgent)
             ->delete("/rapports/{$rapport->id}")
             ->assertStatus(403);
    }

    // ── Actions de suivi ──────────────────────────────────────────────────────

    public function test_ajouter_action_suivi(): void
    {
        $response = $this->actingAs($this->agent)
             ->postJson("/taches/{$this->tache->id}/actions", [
                 'description' => 'Vérifier les prises réseau',
             ]);

        $response->assertJson(['description' => 'Vérifier les prises réseau']);
        $this->assertDatabaseHas('actions_suivi', [
            'tache_id'    => $this->tache->id,
            'description' => 'Vérifier les prises réseau',
            'fait'        => false,
        ]);
    }

    public function test_action_description_vide_rejetee(): void
    {
        $this->actingAs($this->agent)
             ->postJson("/taches/{$this->tache->id}/actions", ['description' => ''])
             ->assertStatus(422);
    }

    public function test_toggle_action_fait(): void
    {
        $action = ActionSuivi::create([
            'tache_id'    => $this->tache->id,
            'user_id'     => $this->agent->id,
            'description' => 'Action à faire',
            'fait'        => false,
        ]);

        $this->actingAs($this->agent)
             ->patchJson("/actions/{$action->id}/toggle", ['fait' => true])
             ->assertJson(['ok' => true, 'fait' => true]);

        $this->assertTrue($action->fresh()->fait);
    }

    public function test_supprimer_action_suivi(): void
    {
        $action = ActionSuivi::create([
            'tache_id'    => $this->tache->id,
            'user_id'     => $this->agent->id,
            'description' => 'Action temporaire',
            'fait'        => false,
        ]);

        $this->actingAs($this->agent)
             ->deleteJson("/actions/{$action->id}")
             ->assertJson(['ok' => true]);

        $this->assertDatabaseMissing('actions_suivi', ['id' => $action->id]);
    }

    // ── Dashboard compteur archives ───────────────────────────────────────────

    public function test_dashboard_affiche_compteur_archives_mois(): void
    {
        Tache::factory()->create([
            'createur_id' => $this->manager->id,
            'statut'      => 'termine',
            'archived_at' => now(),
        ]);

        $this->actingAs($this->manager)
             ->get('/dashboard')
             ->assertStatus(200)
             ->assertSee('Archivées ce mois');
    }
}

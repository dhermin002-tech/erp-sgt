<?php

namespace Tests\Feature\Dashboard;

use App\Models\Site;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
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

    public function test_dashboard_accessible_pour_tous_les_roles(): void
    {
        foreach (['manager', 'technicien', 'agent', 'developpeur', 'stagiaire'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get('/dashboard')->assertStatus(200);
        }
    }

    // ── KPI ──────────────────────────────────────────────────────────────────

    public function test_kpi_taches_actives_correct(): void
    {
        $tache1 = Tache::factory()->create(['createur_id' => $this->manager->id, 'statut' => 'en_cours']);
        $tache2 = Tache::factory()->create(['createur_id' => $this->manager->id, 'statut' => 'nouveau']);
        $tache3 = Tache::factory()->create(['createur_id' => $this->manager->id, 'statut' => 'termine', 'archived_at' => now()]);
        foreach ([$tache1, $tache2, $tache3] as $t) $t->responsables()->attach($this->manager);

        $response = $this->actingAs($this->manager)->get('/dashboard');
        $response->assertStatus(200)->assertSee('2'); // 2 actives (pas la terminée)
    }

    public function test_manager_voit_kpi_global(): void
    {
        $tache = Tache::factory()->create(['createur_id' => $this->manager->id, 'statut' => 'en_cours']);
        $tache->responsables()->attach($this->agent);

        $this->actingAs($this->manager)->get('/dashboard')->assertStatus(200);
    }

    public function test_agent_ne_voit_que_ses_kpi(): void
    {
        // Tâche de l'agent
        $tache1 = Tache::factory()->create(['createur_id' => $this->manager->id]);
        $tache1->responsables()->attach($this->agent);
        // Tâche d'un autre
        $autre  = User::factory()->create(['role' => 'agent']);
        $tache2 = Tache::factory()->create(['createur_id' => $this->manager->id]);
        $tache2->responsables()->attach($autre);

        $this->actingAs($this->agent)->get('/dashboard')->assertStatus(200);
    }

    // ── API JSON /dashboard/data ───────────────────────────────────────────────

    public function test_api_data_retourne_json_structure_correcte(): void
    {
        $this->actingAs($this->manager)
             ->getJson('/dashboard/data')
             ->assertStatus(200)
             ->assertJsonStructure([
                 'donut'  => ['labels', 'data', 'backgroundColor'],
                 'courbe' => ['labels', 'dataC', 'dataT'],
                 'barres' => ['labels', 'data'],
             ]);
    }

    public function test_api_data_donut_contient_5_statuts(): void
    {
        $this->actingAs($this->manager)
             ->getJson('/dashboard/data')
             ->assertJsonCount(5, 'donut.labels');
    }

    public function test_api_data_filtre_par_periode(): void
    {
        // Tâche récente
        $tache = Tache::factory()->create(['createur_id' => $this->manager->id]);
        $tache->responsables()->attach($this->manager);

        $this->actingAs($this->manager)
             ->getJson('/dashboard/data?periode=7')
             ->assertStatus(200)
             ->assertJsonStructure(['donut', 'courbe', 'barres']);
    }

    public function test_api_data_filtre_par_site(): void
    {
        $site  = Site::factory()->create();
        $tache = Tache::factory()->create(['createur_id' => $this->manager->id, 'site_id' => $site->id]);
        $tache->responsables()->attach($this->manager);

        $this->actingAs($this->manager)
             ->getJson("/dashboard/data?site_id={$site->id}")
             ->assertStatus(200);
    }

    public function test_api_data_inaccessible_pour_invite(): void
    {
        $this->getJson('/dashboard/data')->assertStatus(401);
    }

    // ── Filtres dashboard ─────────────────────────────────────────────────────

    public function test_filtre_periode_applique(): void
    {
        $this->actingAs($this->manager)
             ->get('/dashboard?periode=7')
             ->assertStatus(200);
    }

    public function test_filtre_site_applique(): void
    {
        $site = Site::factory()->create();
        $this->actingAs($this->manager)
             ->get("/dashboard?site_id={$site->id}")
             ->assertStatus(200);
    }
}

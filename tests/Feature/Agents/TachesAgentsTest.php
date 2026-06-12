<?php

namespace Tests\Feature\Agents;

use App\Models\Tache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TachesAgentsTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;
    private User $agentIa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = User::factory()->manager()->create();
        $this->agentIa = User::factory()->create([
            'type_compte' => 'agent_ia',
            'agent_code'  => 'project-agent',
            'role'        => 'agent',
        ]);
    }

    // ── Accès ────────────────────────────────────────────────────────────────

    public function test_manager_accede_a_la_page_taches_agents(): void
    {
        $this->actingAs($this->manager)
            ->get(route('agents.taches'))
            ->assertOk()
            ->assertViewIs('agents.taches');
    }

    public function test_non_manager_refuse(): void
    {
        $tech = User::factory()->technicien()->create();
        $this->actingAs($tech)
            ->get(route('agents.taches'))
            ->assertForbidden();
    }

    // ── Contenu ──────────────────────────────────────────────────────────────

    public function test_liste_uniquement_les_taches_creees_par_agents(): void
    {
        $tacheAgent  = Tache::factory()->create(['createur_id' => $this->agentIa->id]);
        $tacheHumain = Tache::factory()->create(['createur_id' => $this->manager->id]);

        $response = $this->actingAs($this->manager)->get(route('agents.taches'));
        $response->assertOk();

        $ids = $response->viewData('taches')->pluck('id');
        $this->assertTrue($ids->contains($tacheAgent->id));
        $this->assertFalse($ids->contains($tacheHumain->id));
    }

    public function test_filtre_par_agent(): void
    {
        $autreAgent = User::factory()->create([
            'type_compte' => 'agent_ia',
            'agent_code'  => 'dev-agent',
            'role'        => 'agent',
        ]);

        $tProject = Tache::factory()->create(['createur_id' => $this->agentIa->id]);
        $tDev     = Tache::factory()->create(['createur_id' => $autreAgent->id]);

        $response = $this->actingAs($this->manager)
            ->get(route('agents.taches', ['agent_id' => $this->agentIa->id]));

        $ids = $response->viewData('taches')->pluck('id');
        $this->assertTrue($ids->contains($tProject->id));
        $this->assertFalse($ids->contains($tDev->id));
    }

    public function test_kpis_corrects(): void
    {
        Tache::factory()->create(['createur_id' => $this->agentIa->id, 'statut' => 'en_cours']);
        Tache::factory()->create(['createur_id' => $this->agentIa->id, 'statut' => 'nouveau']);

        $response = $this->actingAs($this->manager)->get(route('agents.taches'));
        $kpis = $response->viewData('kpis');

        $this->assertEquals(2, $kpis['total']);
        $this->assertEquals(1, $kpis['agents']);
        $this->assertEquals(1, $kpis['en_cours']);
    }
}

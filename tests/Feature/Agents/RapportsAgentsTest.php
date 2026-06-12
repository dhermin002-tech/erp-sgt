<?php

namespace Tests\Feature\Agents;

use App\Models\RapportAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RapportsAgentsTest extends TestCase
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

    public function test_manager_accede_a_la_page_rapports(): void
    {
        $this->actingAs($this->manager)
            ->get(route('agents.rapports'))
            ->assertOk()
            ->assertViewIs('agents.rapports');
    }

    /** Régression : la vue compilait mal (@json + crochets) → 500. */
    public function test_page_rapports_se_rend_avec_donnees(): void
    {
        RapportAgent::create([
            'user_id' => $this->agentIa->id,
            'projet'  => 'sgt',
            'type'    => 'session',
            'titre'   => 'Rapport de validation',
            'contenu' => "Ligne 1\nLigne 2 avec [crochets] et \"guillemets\".",
            'statut'  => 'info',
            'meta'    => ['duree' => '2h', 'taches' => 3],
        ]);

        $this->actingAs($this->manager)
            ->get(route('agents.rapports'))
            ->assertOk()
            ->assertSee('Rapport de validation');
    }

    public function test_non_manager_refuse(): void
    {
        $tech = User::factory()->technicien()->create();
        $this->actingAs($tech)
            ->get(route('agents.rapports'))
            ->assertForbidden();
    }
}

<?php

namespace Tests\Feature\Membres;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembresAgentTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = User::factory()->manager()->create();
    }

    // ── membres.index — séparation humains / agents IA ───────────────────────

    public function test_index_passe_humains_et_agents_ia_a_la_vue(): void
    {
        User::factory()->create(['type_compte' => 'humain', 'role' => 'technicien']);
        User::factory()->create(['type_compte' => 'humain', 'role' => 'developpeur']);
        User::factory()->create([
            'type_compte'   => 'agent_ia',
            'agent_code'    => 'test-agent-alpha',
            'agent_couleur' => '#7c3aed',
            'role'          => 'agent',
        ]);
        User::factory()->create([
            'type_compte'   => 'agent_ia',
            'agent_code'    => 'test-agent-beta',
            'agent_couleur' => '#059669',
            'role'          => 'agent',
        ]);

        $response = $this->actingAs($this->manager)
            ->get(route('membres.index'));

        $response->assertOk()
            ->assertViewIs('membres.index')
            ->assertViewHas('humains')
            ->assertViewHas('agentsIa');

        $humains   = $response->viewData('humains');
        $agentsIa  = $response->viewData('agentsIa');

        // Manager lui-même est type_compte = humain par défaut
        $this->assertTrue($humains->every(fn($u) => $u->type_compte === 'humain'));
        $this->assertTrue($agentsIa->every(fn($u) => $u->type_compte === 'agent_ia'));
    }

    public function test_agent_non_manager_ne_peut_pas_acceder_aux_membres(): void
    {
        $agent = User::factory()->create(['role' => 'agent', 'type_compte' => 'humain']);

        $this->actingAs($agent)
            ->get(route('membres.index'))
            ->assertForbidden();
    }

    // ── storeAgent ───────────────────────────────────────────────────────────

    public function test_manager_peut_creer_un_agent_ia(): void
    {
        $this->actingAs($this->manager)
            ->post(route('membres.storeAgent'), [
                'username'      => 'agent.nouveau',
                'nom'           => 'DevAgent KT',
                'agent_code'    => 'dev-agent-kt',
                'agent_couleur' => '#7c3aed',
            ])
            ->assertRedirect(route('membres.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'username'      => 'agent.nouveau',
            'nom'           => 'DevAgent KT',
            'agent_code'    => 'dev-agent-kt',
            'type_compte'   => 'agent_ia',
            'role'          => 'agent',
        ]);
    }

    public function test_agent_code_doit_etre_unique(): void
    {
        User::factory()->create([
            'type_compte' => 'agent_ia',
            'agent_code'  => 'code-existant',
            'role'        => 'agent',
        ]);

        $this->actingAs($this->manager)
            ->post(route('membres.storeAgent'), [
                'username'      => 'agent.autre',
                'nom'           => 'Agent Bis',
                'agent_code'    => 'code-existant',
                'agent_couleur' => '#7c3aed',
            ])
            ->assertSessionHasErrors('agent_code');
    }

    public function test_agent_code_invalide_refus_majuscules_espaces(): void
    {
        $this->actingAs($this->manager)
            ->post(route('membres.storeAgent'), [
                'username'      => 'agent.test',
                'nom'           => 'Agent Test',
                'agent_code'    => 'Code INVALIDE!',
                'agent_couleur' => '#7c3aed',
            ])
            ->assertSessionHasErrors('agent_code');
    }

    public function test_couleur_invalide_retourne_erreur(): void
    {
        $this->actingAs($this->manager)
            ->post(route('membres.storeAgent'), [
                'username'      => 'agent.couleur',
                'nom'           => 'Agent Couleur',
                'agent_code'    => 'agent-couleur',
                'agent_couleur' => 'rouge',
            ])
            ->assertSessionHasErrors('agent_couleur');
    }

    public function test_username_duplique_retourne_erreur(): void
    {
        User::factory()->create(['username' => 'existant']);

        $this->actingAs($this->manager)
            ->post(route('membres.storeAgent'), [
                'username'      => 'existant',
                'nom'           => 'Agent Dup',
                'agent_code'    => 'agent-dup',
                'agent_couleur' => '#7c3aed',
            ])
            ->assertSessionHasErrors('username');
    }

    public function test_non_manager_ne_peut_pas_creer_agent_ia(): void
    {
        $tech = User::factory()->technicien()->create();

        $this->actingAs($tech)
            ->post(route('membres.storeAgent'), [
                'username'      => 'agent.interdit',
                'nom'           => 'Agent Interdit',
                'agent_code'    => 'agent-interdit',
                'agent_couleur' => '#7c3aed',
            ])
            ->assertForbidden();
    }

    public function test_champs_obligatoires_manquants(): void
    {
        $this->actingAs($this->manager)
            ->post(route('membres.storeAgent'), [])
            ->assertSessionHasErrors(['username', 'nom', 'agent_code', 'agent_couleur']);
    }
}

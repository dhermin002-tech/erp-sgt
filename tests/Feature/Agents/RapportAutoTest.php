<?php

namespace Tests\Feature\Agents;

use App\Models\Tache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RapportAutoTest extends TestCase
{
    use RefreshDatabase;

    public function test_tache_agent_terminee_publie_un_rapport(): void
    {
        $agent = User::factory()->create([
            'type_compte' => 'agent_ia',
            'agent_code'  => 'dev-agent',
            'role'        => 'agent',
        ]);
        $tache = Tache::factory()->create(['statut' => 'en_cours', 'projet' => 'SGT']);
        $tache->responsables()->attach($agent->id);

        $tache->update(['statut' => 'termine']);

        $this->assertDatabaseHas('rapports_agents', [
            'user_id' => $agent->id,
            'type'    => 'quotidien',
            'titre'   => "Tâche terminée : {$tache->titre}",
            'projet'  => 'SGT',
        ]);
    }

    public function test_pas_de_doublon_si_terminee_deux_fois(): void
    {
        $agent = User::factory()->create(['type_compte' => 'agent_ia', 'agent_code' => 'qa-agent', 'role' => 'agent']);
        $tache = Tache::factory()->create(['statut' => 'en_cours']);
        $tache->responsables()->attach($agent->id);

        $tache->update(['statut' => 'termine']);
        $tache->update(['statut' => 'en_cours']);
        $tache->update(['statut' => 'termine']);

        $this->assertEquals(1, $agent->rapportsAgents()->count());
    }

    public function test_tache_humaine_ne_publie_pas_de_rapport(): void
    {
        $humain = User::factory()->technicien()->create();
        $tache  = Tache::factory()->create(['statut' => 'en_cours']);
        $tache->responsables()->attach($humain->id);

        $tache->update(['statut' => 'termine']);

        $this->assertDatabaseCount('rapports_agents', 0);
    }
}

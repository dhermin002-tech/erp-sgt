<?php

namespace Tests\Feature\Taches;

use App\Models\Tache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrdreIaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_emission_par_manager_et_verrou_de_session(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $codex = User::factory()->create(['role' => 'agent', 'type_compte' => 'agent_ia', 'agent_code' => 'codex']);
        $claude = User::factory()->create(['role' => 'agent', 'type_compte' => 'agent_ia', 'agent_code' => 'dev-agent']);
        $tache = Tache::factory()->create();
        $tache->responsables()->attach([$codex->id, $claude->id]);
        $base = '/api/v1/ordres-ia';
        $donnees = ['tache_id' => $tache->id, 'cible' => 'tout_agent',
            'projet_dossier' => 'erp.sgt', 'consigne' => 'Préparer le rapport.'];

        Sanctum::actingAs($codex, ['mcp:write']);
        $this->postJson($base, $donnees)->assertForbidden();
        Sanctum::actingAs($manager, ['mcp:write']);
        $this->postJson($base, array_replace($donnees, ['projet_dossier' => '../erp.sgt']))->assertUnprocessable();
        $id = $this->postJson($base, $donnees)->assertCreated()->json('data.id');
        $url = $base.'/'.$id;
        $rapport = ['session_id' => 'session-a', 'statut' => 'fait', 'compte_rendu' => 'Rapport prêt.'];

        Sanctum::actingAs($codex, ['mcp:write']);
        $this->getJson($base.'?statut=a_executer&projet=erp.sgt&cible=tout_agent')->assertOk()->assertJsonPath('total', 1);
        $this->postJson($url.'/rendre-compte', $rapport)->assertConflict();
        $this->postJson($url.'/prendre', ['session_id' => 'session-a'])->assertOk()->assertJsonPath('data.statut', 'pris');
        Sanctum::actingAs($claude, ['mcp:write']);
        $this->postJson($url.'/prendre', ['session_id' => 'session-b'])->assertConflict();
        $this->postJson($url.'/rendre-compte', $rapport)->assertConflict();
        Sanctum::actingAs($codex, ['mcp:write']);
        $this->postJson($url.'/rendre-compte', ['session_id' => 'autre'] + $rapport)->assertConflict();
        $this->postJson($url.'/rendre-compte', $rapport)->assertOk()->assertJsonPath('data.statut', 'fait');
        $this->postJson($url.'/rendre-compte', $rapport)->assertConflict();
        $this->assertDatabaseHas('commentaires', ['user_id' => $codex->id, 'tache_id' => $tache->id, 'contenu' => 'Rapport prêt.']);
        $this->assertDatabaseCount('commentaires', 1);
    }

    public function test_la_cible_et_l_annulation_sont_controlees(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $agent = User::factory()->create(['role' => 'agent', 'type_compte' => 'agent_ia', 'agent_code' => 'dev-agent']);
        $tache = Tache::factory()->create();
        $tache->responsables()->attach($agent);
        Sanctum::actingAs($manager, ['mcp:write']);
        $id = $this->postJson('/api/v1/ordres-ia', [
            'tache_id' => $tache->id, 'cible' => 'codex', 'projet_dossier' => 'erp.sgt', 'consigne' => 'Faire le point.',
        ])->assertCreated()->json('data.id');
        $url = '/api/v1/ordres-ia/'.$id;
        Sanctum::actingAs($agent, ['mcp:write']);
        $this->postJson($url.'/prendre', ['session_id' => 'autre'])->assertForbidden();
        $this->postJson($url.'/annuler')->assertForbidden();
        Sanctum::actingAs($manager, ['mcp:write']);
        $this->postJson($url.'/annuler')->assertOk()->assertJsonPath('data.statut', 'annule');
    }
}

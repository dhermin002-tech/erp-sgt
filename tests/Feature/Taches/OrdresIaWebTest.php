<?php

namespace Tests\Feature\Taches;

use App\Models\OrdreIa;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrdresIaWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_seul_le_manager_voit_et_utilise_les_routes_web(): void
    {
        $technicien = User::factory()->create(['role' => 'technicien']);
        $tache = Tache::factory()->create(['createur_id' => $technicien->id]);
        $this->get('/ordres-ia')->assertRedirect(route('login'));
        $this->actingAs($technicien)->get('/taches/'.$tache->id)->assertOk()
            ->assertDontSee('Confier à un agent IA');
        $this->get('/ordres-ia')->assertForbidden();
        $this->post('/taches/'.$tache->id.'/ordres-ia', $this->donnees())->assertForbidden();
        $this->assertDatabaseCount('ordres_ia', 0);
    }

    public function test_le_manager_emet_depuis_la_fiche_et_voit_le_badge(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $tache = Tache::factory()->create();
        $this->actingAs($manager)->get('/taches/'.$tache->id)->assertOk()
            ->assertSee('Confier à un agent IA')->assertSee('ERP SGT (erp.sgt)');
        $url = '/taches/'.$tache->id.'/ordres-ia';
        $this->from('/taches/'.$tache->id)->post($url, ['projet_dossier' => '../inconnu'] + $this->donnees())
            ->assertSessionHasErrors('projet_dossier', null, 'ordreIa');

        $this->post($url, ['tache_id' => 999999] + $this->donnees())->assertRedirect();
        $ordre = OrdreIa::sole();
        $this->assertSame($tache->id, $ordre->tache_id);
        $this->assertSame($manager->id, $ordre->emis_par);
        $this->get('/ordres-ia/'.$ordre->id)->assertOk()->assertSee($this->donnees()['consigne'])
            ->assertSee('aria-label="1 ordre en attente"', false);
    }

    public function test_filtres_detail_et_resume_api_partagent_les_donnees(): void
    {
        $manager = User::factory()->create(['role' => 'manager', 'prenom' => 'Mina', 'nom' => 'Recette']);
        $tache = Tache::factory()->create(['titre' => 'Dossier de recette']);
        $autre = Tache::factory()->create(['titre' => 'Dossier hors filtre']);
        $ordre = OrdreIa::create($this->donnees() + ['tache_id' => $tache->id, 'emis_par' => $manager->id]);
        $ordre->forceFill(['statut' => 'fait', 'compte_rendu' => "<b>Rapport</b>\nLigne suivante"])->save();
        OrdreIa::create(['cible' => 'claude_code', 'tache_id' => $autre->id, 'emis_par' => $manager->id] + $this->donnees());

        $this->actingAs($manager)->get('/ordres-ia?statut=fait&cible=codex')->assertOk()
            ->assertSee('Dossier de recette')->assertDontSee('Dossier hors filtre');
        $this->get('/ordres-ia/'.$ordre->id)->assertOk()
            ->assertSee('<b>Rapport</b>')->assertDontSee('<b>Rapport</b>', false)
            ->assertSee('Ligne suivante');
        Sanctum::actingAs($manager, ['mcp:read']);
        $this->getJson('/api/v1/ordres-ia?statut=fait&projet=erp.sgt')->assertOk()
            ->assertJsonPath('data.0.tache.titre', 'Dossier de recette')
            ->assertJsonPath('data.0.emetteur.prenom', 'Mina')->assertJsonStructure(['data' => [['created_at']]]);
    }

    private function donnees(): array
    {
        return ['cible' => 'codex', 'projet_dossier' => 'erp.sgt', 'consigne' => 'Préparer le rapport de recette.'];
    }
}

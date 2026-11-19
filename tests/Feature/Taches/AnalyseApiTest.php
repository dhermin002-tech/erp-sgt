<?php

namespace Tests\Feature\Taches;

use App\Models\Tache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AnalyseApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_synthese_periode_et_visibilite(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 19)->setTime(12, 0));
        $manager = User::factory()->create(['role' => 'manager']);
        $responsable = User::factory()->create();
        $tache = Tache::factory()->create([
            'projet' => 'SGT', 'statut' => 'en_cours', 'priorite' => 'haute',
            'date_echeance' => '2026-11-18', 'created_at' => now()->subDays(2),
        ]);
        $tache->responsables()->attach($responsable);
        $terminee = Tache::factory()->create([
            'statut' => 'nouveau', 'date_echeance' => '2026-11-19', 'created_at' => now()->subDay(),
        ]);
        $terminee->update(['statut' => 'termine']);
        $url = '/api/v1/analyse/synthese?du=2026-11-17&au=2026-11-19';

        Sanctum::actingAs($manager, ['mcp:read']);
        $this->getJson($url)->assertOk()->assertJsonPath('data.total', 2)
            ->assertJsonPath('data.taches_en_retard.0.id', $tache->id)
            ->assertJsonPath('data.par_projet.SGT', 1)
            ->assertJsonPath('data.taux_respect_echeances', 100)
            ->assertJsonPath('data.delai_moyen_traitement_secondes', 86400);
        Sanctum::actingAs($responsable, ['mcp:read']);
        $this->getJson($url)->assertOk()->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.charge_en_cours.0.en_cours', 1);
        $this->getJson('/api/v1/analyse/synthese?du=2026-11-20&au=2026-11-19')->assertUnprocessable();
    }
}

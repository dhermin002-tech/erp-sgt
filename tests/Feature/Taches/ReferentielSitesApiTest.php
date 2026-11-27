<?php

namespace Tests\Feature\Taches;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReferentielSitesApiTest extends TestCase
{
    use RefreshDatabase;

    // Régression : la route lisait une colonne « localisation » inexistante (erreur 500 en production)
    public function test_referentiel_sites_renvoie_les_sites_actifs_avec_leur_ville(): void
    {
        Site::factory()->create(['nom' => 'Agence Nord', 'ville' => 'Libreville', 'actif' => true]);
        Site::factory()->create(['nom' => 'Ancien dépôt', 'ville' => 'Owendo', 'actif' => false]);
        Sanctum::actingAs(User::factory()->create(['role' => 'technicien']), ['mcp:read']);

        $this->getJson('/api/v1/sites')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nom', 'Agence Nord')
            ->assertJsonPath('data.0.ville', 'Libreville')
            ->assertJsonPath('data.0.localisation', 'Libreville');
    }
}

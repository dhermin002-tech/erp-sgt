<?php

namespace Tests\Feature\Taches;

use App\Models\RapportAgent;
use App\Models\SessionAgent;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PariteApiTest extends TestCase
{
    use RefreshDatabase;

    private const API = '/api/v1';

    public function test_api_exige_authentification_ability_et_role_manager(): void
    {
        foreach (['/taches/archives', '/notifications', '/profil', '/preferences', '/rapports', '/gestion/membres', '/gestion/sites', '/agents/taches', '/agents/rapports', '/agents/sessions'] as $route) {
            $this->getJson(self::API.$route)->assertUnauthorized();
        }

        $technicien = User::factory()->create(['role' => 'technicien']);
        Sanctum::actingAs($technicien, ['mobile']);
        foreach (['/gestion/membres', '/gestion/sites', '/agents/taches', '/agents/rapports', '/agents/sessions'] as $route) {
            $this->getJson(self::API.$route)->assertForbidden();
        }
        $this->postJson(self::API.'/gestion/membres')->assertForbidden();
        $this->postJson(self::API.'/gestion/sites')->assertForbidden();

        Sanctum::actingAs(User::factory()->create(['role' => 'manager']), ['mcp:read']);
        $this->getJson(self::API.'/gestion/membres')->assertForbidden();
        $this->getJson(self::API.'/notifications')->assertForbidden();
    }

    public function test_archivage_manuel_restauration_et_visibilite_des_rapports(): void
    {
        $user = User::factory()->create(['role' => 'technicien']);
        Sanctum::actingAs($user, ['mobile']);
        $active = Tache::factory()->create(['statut' => 'en_cours', 'createur_id' => $user->id]);
        $terminee = Tache::factory()->create(['statut' => 'termine', 'createur_id' => $user->id]);
        $cachee = Tache::factory()->create(['statut' => 'termine']);

        $this->assertNull($terminee->fresh()->archived_at);
        $this->patchJson(self::API."/taches/{$cachee->id}/archiver")->assertForbidden();
        $this->patchJson(self::API."/taches/{$cachee->id}/restaurer")->assertForbidden();
        $this->getJson(self::API.'/taches/archives')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $terminee->id);
        $this->getJson(self::API.'/rapports')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $active->id);
        $this->getJson(self::API.'/rapports?vue=terminees')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $terminee->id);
        $this->patchJson(self::API."/taches/{$active->id}/archiver")->assertNoContent();
        $this->assertNotNull($active->fresh()->archived_at);
        $this->patchJson(self::API."/taches/{$active->id}/restaurer")->assertNoContent();
        $this->assertNull($active->fresh()->archived_at);
        $this->assertSame('nouveau', $active->fresh()->statut);
    }

    public function test_notifications_paginees_et_privees(): void
    {
        $user = User::factory()->create();
        $autre = User::factory()->create();
        Sanctum::actingAs($user, ['mobile']);
        $creer = fn (User $compte) => $compte->notifications()->create([
            'id' => Str::uuid()->toString(), 'type' => 'recette', 'data' => ['titre' => 'Recette'],
        ]);
        $a = $creer($user);
        $creer($user);
        $b = $creer($autre);
        $this->getJson(self::API.'/notifications')->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonPath('per_page', 20);
        $this->getJson(self::API.'/notifications/count')->assertOk()->assertJsonPath('count', 2);
        $this->patchJson(self::API."/notifications/{$b->id}/lue")->assertNotFound();
        $this->patchJson(self::API."/notifications/{$a->id}/lue")->assertNoContent();
        $this->getJson(self::API.'/notifications/count')->assertOk()->assertJsonPath('count', 1);
        $this->patchJson(self::API.'/notifications/tout-lire')->assertNoContent();
        $this->getJson(self::API.'/notifications/count')->assertOk()->assertJsonPath('count', 0);
        $this->assertNull($b->fresh()->read_at);
    }

    public function test_preferences_et_profil_modifiables_par_le_titulaire(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['mobile']);
        $this->patchJson(self::API.'/profil/infos', ['nom' => ''])->assertUnprocessable()->assertJsonValidationErrors('nom');
        $this->patchJson(self::API.'/profil/infos', ['nom' => 'Recette', 'role' => 'manager'])
            ->assertOk()->assertJsonPath('data.nom', 'Recette')->assertJsonMissingPath('data.password');
        $this->assertSame($user->role, $user->fresh()->role);
        $this->getJson(self::API.'/profil')->assertOk()->assertJsonPath('data.nom', 'Recette');
        $this->patchJson(self::API.'/preferences', ['direction_ui' => 'C', 'locale' => 'xx'])
            ->assertUnprocessable()->assertJsonValidationErrors(['direction_ui', 'locale']);
        $this->patchJson(self::API.'/preferences', ['direction_ui' => 'B', 'locale' => 'en'])
            ->assertOk()->assertJsonPath('data.locale', 'en');
        $this->getJson(self::API.'/preferences')->assertOk()->assertJsonPath('data.direction_ui', 'B')
            ->assertJsonPath('data.locale', 'en');
        $this->assertSame('en', $user->fresh()->locale);
    }

    public function test_mot_de_passe_verifie_et_revoque_les_autres_jetons(): void
    {
        $user = User::factory()->create(['password' => 'ancien-factice']);
        $courant = $user->createToken('mobile de test', ['mobile']);
        $autre = $user->createToken('autre appareil de test', ['mobile']);
        $url = self::API.'/profil/password';
        $corps = ['password' => 'nouveau-factice', 'password_confirmation' => 'nouveau-factice'];
        $this->withToken($courant->plainTextToken)->patchJson($url, $corps + ['password_actuel' => 'inexact'])
            ->assertUnprocessable()->assertJsonValidationErrors('password_actuel');
        $this->withToken($courant->plainTextToken)->patchJson($url, $corps + ['password_actuel' => 'ancien-factice'])
            ->assertNoContent()->assertHeader('X-RateLimit-Limit', '5');
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $courant->accessToken->id]);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $autre->accessToken->id]);
        $this->assertTrue(Hash::check('nouveau-factice', $user->fresh()->password));
    }

    public function test_crud_membres_et_sites_reutilise_les_validations_web(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        Sanctum::actingAs($manager, ['mobile']);
        $membres = self::API.'/gestion/membres';
        $sites = self::API.'/gestion/sites';
        $this->postJson($membres, ['username' => 'recette'])->assertUnprocessable()
            ->assertJsonValidationErrors(['nom', 'role', 'password']);
        $id = $this->postJson($membres, [
            'username' => 'recette', 'nom' => 'Recette', 'role' => 'technicien',
            'password' => 'factice-123', 'password_confirmation' => 'factice-123',
        ])->assertCreated()->assertJsonMissingPath('data.password')->json('data.id');
        $this->getJson($membres)->assertOk();
        $this->getJson("$membres/$id")->assertOk()->assertJsonPath('data.username', 'recette');
        $this->patchJson("$membres/$id", ['nom' => 'Corrigé', 'role' => 'agent'])
            ->assertOk()->assertJsonPath('data.role', 'agent');
        $this->deleteJson("$membres/{$manager->id}")->assertForbidden();
        $this->deleteJson("$membres/$id")->assertNoContent();

        $this->postJson($sites, ['nom' => 'Recette'])->assertUnprocessable()->assertJsonValidationErrors('ville');
        $id = $this->postJson($sites, ['nom' => 'Recette', 'ville' => 'Libreville'])->assertCreated()->json('data.id');
        $this->getJson($sites)->assertOk();
        $this->getJson("$sites/$id")->assertOk()->assertJsonPath('data.ville', 'Libreville');
        $this->patchJson("$sites/$id", ['nom' => 'Recette', 'ville' => 'Akanda', 'actif' => false])
            ->assertOk()->assertJsonPath('data.ville', 'Akanda');
        $this->deleteJson("$sites/$id")->assertNoContent();
    }

    public function test_supervision_ia_filtree_par_responsable_et_echeances_triees(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'manager']), ['mobile']);
        $agent = User::factory()->create(['type_compte' => 'agent_ia', 'role' => 'agent']);
        $tache = Tache::factory()->create(['statut' => 'en_cours', 'date_echeance' => now()->addDay()]);
        $tache->responsables()->attach($agent);
        Tache::factory()->create(['statut' => 'en_cours', 'date_echeance' => now()->addDays(14)]);
        Tache::factory()->create(['statut' => 'en_cours', 'date_echeance' => now()->addDays(15)]);
        $this->getJson(self::API.'/taches?echeances_a_venir=1&par_page=3')->assertOk()
            ->assertJsonCount(2, 'data')->assertJsonPath('data.0.id', $tache->id);
        $this->getJson(self::API.'/agents/taches?agent_id='.$agent->id)->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $tache->id);
        $this->getJson(self::API.'/agents/taches?statut=invalide')->assertUnprocessable();
        $rapport = RapportAgent::create([
            'user_id' => $agent->id, 'projet' => 'SGT', 'type' => 'session',
            'titre' => 'Recette', 'contenu' => 'Fictif', 'statut' => 'info',
        ]);
        $session = SessionAgent::create(['user_id' => $agent->id, 'projet' => 'SGT', 'demarree_a' => now(), 'statut' => 'en_cours']);
        $this->getJson(self::API.'/agents/rapports?agent_id='.$agent->id)->assertOk()
            ->assertJsonPath('data.0.id', $rapport->id)->assertJsonMissingPath('data.0.fichier_md');
        $this->getJson(self::API.'/agents/sessions?projet=SG')->assertOk()
            ->assertJsonPath('data.0.id', $session->id);
    }
}

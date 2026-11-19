<?php

namespace Tests\Feature\Auth;

use App\Models\Tache;
use App\Models\User;
use Database\Seeders\AgentAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AbilitiesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecture_seule_et_compatibilite_des_jetons_historiques(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $tache = Tache::factory()->create();
        $url = "/api/v1/taches/{$tache->id}/progression";
        Sanctum::actingAs($manager, ['mcp:read']);
        $this->getJson('/api/v1/taches')->assertOk();
        $this->patchJson($url, ['progression' => 25])->assertForbidden();

        Sanctum::actingAs($manager, ['taches:update']);
        $this->patchJson($url, ['progression' => 25])->assertOk();
        Sanctum::actingAs($manager, ['mobile']);
        $this->patchJson($url, ['progression' => 50])->assertOk();
    }

    public function test_limitation_par_identifiant_et_ip(): void
    {
        $url = '/api/v1/auth/token';
        for ($i = 0; $i < 5; $i++) {
            $this->postJson($url, ['username' => 'poste-test', 'password' => 'saisie-factice'])->assertUnprocessable();
        }
        $this->postJson($url, ['username' => 'POSTE-TEST', 'password' => 'saisie-factice'])->assertTooManyRequests();
        $this->postJson($url, ['username' => 'autre-poste', 'password' => 'saisie-factice'])->assertUnprocessable();
    }

    public function test_audit_et_seeder_idempotent(): void
    {
        $this->seed(AgentAccountsSeeder::class);
        $codex = User::where('agent_code', 'codex')->sole();
        $this->seed(AgentAccountsSeeder::class);
        $this->assertSame($codex->id, User::where('agent_code', 'codex')->sole()->id);

        $user = User::factory()->create(['password' => 'saisie-factice']);
        Log::spy();
        $reponse = $this->postJson('/api/v1/auth/token', [
            'username' => $user->username, 'password' => 'saisie-factice', 'client' => 'mobile',
        ])->assertCreated();
        $jeton = $user->tokens()->sole();
        $this->assertSame(['mobile'], $jeton->abilities);
        $audit = ['jeton_id' => $jeton->id, 'compte_id' => $user->id];
        Log::shouldHaveReceived('info')->with('Jeton Sanctum émis.', $audit)->once();
        $this->withToken($reponse->json('token'))->deleteJson('/api/v1/auth/token')->assertOk();
        Log::shouldHaveReceived('info')->with('Jeton Sanctum révoqué.', $audit)->once();
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $jeton->id]);
    }
}

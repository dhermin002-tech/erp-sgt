<?php

namespace Tests\Feature\Notifications;

use App\Jobs\EnvoyerNotificationPush;
use App\Models\AppareilPush;
use App\Models\OrdreIa;
use App\Models\Tache;
use App\Models\User;
use App\Services\NotificationPush;
use App\Services\TransportPush;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AppareilPushApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_destination_chiffree_et_protegee_par_compte(): void
    {
        $url = '/api/v1/push/appareils';
        $user = User::factory()->create();
        $destination = ['plateforme' => 'android', 'jeton' => 'marqueur-public-test'];
        Sanctum::actingAs($user, ['mcp:write']);
        $this->postJson($url, $destination)->assertForbidden();
        Sanctum::actingAs($user, ['mobile']);
        $id = $this->postJson($url, $destination)->assertCreated()->assertJsonMissingPath('data.destination')->json('data.id');
        $this->postJson($url, $destination)->assertOk()->assertJsonPath('data.id', $id);
        $this->assertStringNotContainsString($destination['jeton'], DB::table('appareils_push')->where('id', $id)->value('destination'));
        Sanctum::actingAs(User::factory()->create(), ['mobile']);
        $this->postJson($url, $destination)->assertConflict();
        $this->deleteJson($url, ['id' => $id])->assertNotFound();
        Sanctum::actingAs($user, ['mobile']);
        $this->deleteJson($url, ['id' => $id])->assertNoContent();
    }

    public function test_web_push_limite_aux_services_autorises(): void
    {
        Sanctum::actingAs(User::factory()->create(), ['mobile']);
        $abonnement = ['endpoint' => 'https://fcm.googleapis.com/fcm/send/exemple',
            'keys' => ['auth' => str_repeat('a', 22), 'p256dh' => str_repeat('b', 87)]];
        $url = '/api/v1/push/appareils';
        $this->postJson($url, ['plateforme' => 'web', 'abonnement' => $abonnement])->assertCreated();
        $abonnement['endpoint'] = 'https://exemple.invalid/push';
        $this->postJson($url, ['plateforme' => 'web', 'abonnement' => $abonnement])
            ->assertUnprocessable()->assertJsonValidationErrors('abonnement.endpoint');
    }

    public function test_evenements_mis_en_file_et_transport_simule(): void
    {
        Queue::fake();
        $manager = User::factory()->create(['role' => 'manager']);
        $tache = Tache::factory()->create(['date_echeance' => now()->addDay(), 'statut' => 'en_cours']);
        $tache->responsables()->attach($manager);
        $appareil = AppareilPush::create([
            'user_id' => $manager->id, 'plateforme' => 'android', 'empreinte' => hash('sha256', 'exemple'),
            'destination' => ['jeton' => 'marqueur-public-test'], 'dernier_usage' => now(),
        ]);
        $ordre = OrdreIa::create(['tache_id' => $tache->id, 'emis_par' => $manager->id,
            'cible' => 'tout_agent', 'projet_dossier' => 'erp.sgt', 'consigne' => 'Point de suivi.']);

        $notifications = app(NotificationPush::class);
        $notifications->assignation($tache, [$manager->id]);
        $notifications->echeancesDemain();
        $notifications->ordreRendu($ordre);

        Queue::assertPushed(EnvoyerNotificationPush::class, 3);
        foreach (['assignation', 'echeance', 'ordre_rendu'] as $type) {
            Queue::assertPushed(EnvoyerNotificationPush::class, fn ($job) => $job->appareilId === $appareil->id
                && $job->message['type'] === $type);
        }
        $transport = $this->mock(TransportPush::class);
        $transport->shouldReceive('envoyer')->once()->andReturnFalse();
        (new EnvoyerNotificationPush($appareil->id, ['type' => 'assignation']))->handle($transport);
        $this->assertModelMissing($appareil);
    }
}

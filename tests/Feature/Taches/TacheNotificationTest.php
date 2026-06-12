<?php

namespace Tests\Feature\Taches;

use App\Models\Tache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TacheNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = User::factory()->manager()->create();
    }

    // ── Notifications à la création ──────────────────────────────────────────

    public function test_creation_tache_notifie_les_managers(): void
    {
        $autreManager = User::factory()->manager()->create();
        $createur     = User::factory()->technicien()->create();

        $this->actingAs($createur)->post('/taches', [
            'titre'         => 'Tâche de supervision',
            'responsables'  => [$createur->id],
            'statut'        => 'nouveau',
            'priorite'      => 'normale',
            'progression'   => 0,
        ])->assertRedirect();

        // Les deux managers reçoivent une notification de supervision
        $this->assertEquals(1, $this->manager->fresh()->notifications()->count());
        $this->assertEquals(1, $autreManager->fresh()->notifications()->count());
    }

    public function test_responsable_assigne_recoit_notification(): void
    {
        $responsable = User::factory()->technicien()->create();

        $this->actingAs($this->manager)->post('/taches', [
            'titre'        => 'Câblage immeuble',
            'responsables' => [$responsable->id],
            'statut'       => 'nouveau',
            'priorite'     => 'normale',
            'progression'  => 0,
        ])->assertRedirect();

        $notif = $responsable->fresh()->notifications()->first();
        $this->assertNotNull($notif);
        $this->assertEquals('assignation', $notif->data['type']);
    }

    public function test_le_createur_ne_se_notifie_pas_lui_meme(): void
    {
        // Le manager crée et s'assigne : il ne doit recevoir aucune notification
        $this->actingAs($this->manager)->post('/taches', [
            'titre'        => 'Tâche perso',
            'responsables' => [$this->manager->id],
            'statut'       => 'nouveau',
            'priorite'     => 'normale',
            'progression'  => 0,
        ])->assertRedirect();

        $this->assertEquals(0, $this->manager->fresh()->notifications()->count());
    }

    // ── Filtre « Créé par » ──────────────────────────────────────────────────

    public function test_filtre_createur_agent_ia(): void
    {
        $agentIa = User::factory()->create([
            'type_compte' => 'agent_ia',
            'agent_code'  => 'test-agent',
            'role'        => 'agent',
        ]);

        $tacheAgent  = Tache::factory()->create(['createur_id' => $agentIa->id, 'titre' => 'Tache creee par agent']);
        $tacheHumain = Tache::factory()->create(['createur_id' => $this->manager->id, 'titre' => 'Tache creee par humain']);

        $response = $this->actingAs($this->manager)->get('/taches?createur=agent_ia');
        $response->assertStatus(200);

        $taches = $response->viewData('taches');
        $ids    = $taches->pluck('id');

        $this->assertTrue($ids->contains($tacheAgent->id));
        $this->assertFalse($ids->contains($tacheHumain->id));
    }
}

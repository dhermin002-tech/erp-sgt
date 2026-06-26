<?php

namespace Tests\Feature\Taches;

use App\Models\Tache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ModifierTacheApiTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;
    private User $technicien;
    private User $agentIA;
    private Tache $tache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager    = User::factory()->manager()->create();
        $this->technicien = User::factory()->create(['role' => 'technicien']);
        $this->agentIA    = User::factory()->create([
            'role'        => 'agent',
            'type_compte' => 'agent_ia',
            'agent_code'  => 'dev-agent',
        ]);

        $this->tache = Tache::factory()->create([
            'titre'    => 'Tâche initiale',
            'statut'   => 'nouveau',
            'priorite' => 'normale',
        ]);

        $this->tache->responsables()->attach($this->technicien->id);
    }

    private function actingAsManagerApi(): self
    {
        Sanctum::actingAs($this->manager, ['*']);
        return $this;
    }

    private function actingAsTechnicienApi(): self
    {
        Sanctum::actingAs($this->technicien, ['*']);
        return $this;
    }

    // ── 200 — Patch partiel ──────────────────────────────────────────────────

    public function test_patch_partiel_titre_seul(): void
    {
        $this->actingAsManagerApi();

        $this->patchJson("/api/v1/taches/{$this->tache->id}", [
            'titre' => 'Titre modifié',
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.titre', 'Titre modifié')
        ->assertJsonPath('data.statut', 'nouveau'); // les autres champs inchangés
    }

    public function test_patch_partiel_priorite_seule(): void
    {
        $this->actingAsManagerApi();

        $this->patchJson("/api/v1/taches/{$this->tache->id}", [
            'priorite' => 'urgente',
        ])
        ->assertOk()
        ->assertJsonPath('data.priorite', 'urgente')
        ->assertJsonPath('data.titre', 'Tâche initiale'); // titre inchangé
    }

    public function test_patch_plusieurs_champs_simultanement(): void
    {
        $this->actingAsManagerApi();

        $this->patchJson("/api/v1/taches/{$this->tache->id}", [
            'titre'        => 'Nouveau titre',
            'priorite'     => 'haute',
            'date_echeance'=> now()->addDays(14)->format('Y-m-d'),
        ])
        ->assertOk()
        ->assertJsonPath('data.titre', 'Nouveau titre')
        ->assertJsonPath('data.priorite', 'haute');
    }

    // ── 404 — Tâche inexistante ───────────────────────────────────────────────

    public function test_404_si_tache_inexistante(): void
    {
        $this->actingAsManagerApi();

        $this->patchJson('/api/v1/taches/99999', ['titre' => 'X'])
             ->assertNotFound();
    }

    // ── 422 — Code agent inconnu ──────────────────────────────────────────────

    public function test_422_si_code_agent_inconnu(): void
    {
        $this->actingAsManagerApi();

        $this->patchJson("/api/v1/taches/{$this->tache->id}", [
            'responsables_codes' => ['agent-fantome'],
        ])
        ->assertStatus(422)
        ->assertJsonFragment(['message' => 'Code(s) agent inconnu(s) : agent-fantome']);
    }

    // ── 403 — Statut "termine" par non-manager ────────────────────────────────

    public function test_403_si_technicien_tente_de_cloturer(): void
    {
        $this->actingAsTechnicienApi();

        $this->patchJson("/api/v1/taches/{$this->tache->id}", [
            'statut' => 'termine',
        ])
        ->assertForbidden()
        ->assertJsonFragment(['message' => 'Seul un manager peut clôturer une tâche.']);
    }

    public function test_manager_peut_cloturer_une_tache(): void
    {
        $this->actingAsManagerApi();

        $this->patchJson("/api/v1/taches/{$this->tache->id}", [
            'statut' => 'termine',
        ])
        ->assertOk()
        ->assertJsonPath('data.statut', 'termine');
    }

    // ── Responsables : [] vide la liste ──────────────────────────────────────

    public function test_responsables_vide_supprime_tous_les_responsables(): void
    {
        $this->actingAsManagerApi();

        $this->patchJson("/api/v1/taches/{$this->tache->id}", [
            'responsables' => [],
        ])
        ->assertOk();

        $this->assertCount(0, $this->tache->fresh()->responsables);
    }

    // ── Clé absente = responsables inchangés ─────────────────────────────────

    public function test_absence_cle_responsables_ne_modifie_pas_les_responsables(): void
    {
        $this->actingAsManagerApi();

        // Patch sans fournir la clé "responsables"
        $this->patchJson("/api/v1/taches/{$this->tache->id}", [
            'titre' => 'Titre sans toucher aux responsables',
        ])
        ->assertOk();

        // Le technicien doit toujours être responsable
        $this->assertCount(1, $this->tache->fresh()->responsables);
        $this->assertEquals($this->technicien->id, $this->tache->fresh()->responsables->first()->id);
    }

    // ── responsables_codes résolu en IDs ─────────────────────────────────────

    public function test_responsables_codes_resolu_en_ids(): void
    {
        $this->actingAsManagerApi();

        $this->patchJson("/api/v1/taches/{$this->tache->id}", [
            'responsables'       => [],
            'responsables_codes' => ['dev-agent'],
        ])
        ->assertOk();

        $responsables = $this->tache->fresh()->responsables;
        $this->assertCount(1, $responsables);
        $this->assertEquals($this->agentIA->id, $responsables->first()->id);
    }

    // ── 401 — Non authentifié ─────────────────────────────────────────────────

    public function test_401_sans_authentification(): void
    {
        $this->patchJson("/api/v1/taches/{$this->tache->id}", ['titre' => 'X'])
             ->assertUnauthorized();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Commentaire;
use App\Models\Rapport;
use App\Models\SousTache;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Vérifie que les sous-ressources (sous-tâches, commentaires, rapports)
 * sont inaccessibles à un utilisateur non assigné à la tâche parente.
 */
class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    private User  $manager;
    private User  $assignee;   // agent assigné à la tâche
    private User  $outsider;   // agent sans lien avec la tâche
    private Tache $tache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager  = User::factory()->manager()->create();
        $this->assignee = User::factory()->create();
        $this->outsider = User::factory()->create();

        $this->tache = Tache::factory()->create([
            'createur_id' => $this->manager->id,
        ]);
        $this->tache->responsables()->attach($this->assignee->id);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function sousTache(): SousTache
    {
        return $this->tache->sousTaches()->create(['titre' => 'ST test', 'ordre' => 1]);
    }

    private function commentaire(): Commentaire
    {
        return $this->tache->commentaires()->create([
            'user_id' => $this->assignee->id,
            'contenu' => 'Commentaire de test',
        ]);
    }

    private function rapport(): Rapport
    {
        return $this->tache->rapports()->create([
            'user_id' => $this->assignee->id,
            'contenu' => 'Rapport de test',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SOUS-TÂCHES WEB
    // ══════════════════════════════════════════════════════════════════════════

    public function test_outsider_ne_peut_pas_creer_sous_tache(): void
    {
        $this->actingAs($this->outsider)
             ->post(route('sous-taches.store', $this->tache), ['titre' => 'Intrusion'])
             ->assertStatus(403);
    }

    public function test_assignee_peut_creer_sous_tache(): void
    {
        $this->actingAs($this->assignee)
             ->post(route('sous-taches.store', $this->tache), ['titre' => 'Légitime'])
             ->assertStatus(200);
    }

    public function test_outsider_ne_peut_pas_toggle_sous_tache(): void
    {
        $st = $this->sousTache();

        $this->actingAs($this->outsider)
             ->patch(route('sous-taches.toggle', $st), ['termine' => true])
             ->assertStatus(403);
    }

    public function test_assignee_peut_toggle_sous_tache(): void
    {
        $st = $this->sousTache();

        $this->actingAs($this->assignee)
             ->patch(route('sous-taches.toggle', $st), ['termine' => true])
             ->assertStatus(200);
    }

    public function test_outsider_ne_peut_pas_supprimer_sous_tache(): void
    {
        $st = $this->sousTache();

        $this->actingAs($this->outsider)
             ->delete(route('sous-taches.destroy', $st))
             ->assertStatus(403);
    }

    public function test_manager_peut_supprimer_sous_tache(): void
    {
        $st = $this->sousTache();

        $this->actingAs($this->manager)
             ->delete(route('sous-taches.destroy', $st))
             ->assertStatus(200);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SOUS-TÂCHES API
    // ══════════════════════════════════════════════════════════════════════════

    public function test_api_outsider_ne_peut_pas_creer_sous_tache(): void
    {
        $token = $this->outsider->createToken('test')->plainTextToken;

        $this->postJson(
            "/api/v1/taches/{$this->tache->id}/sous-taches",
            ['titre' => 'Intrusion API'],
            ['Authorization' => "Bearer $token"]
        )->assertStatus(403);
    }

    public function test_api_outsider_ne_peut_pas_toggle_sous_tache(): void
    {
        $st    = $this->sousTache();
        $token = $this->outsider->createToken('test')->plainTextToken;

        $this->patchJson(
            "/api/v1/sous-taches/{$st->id}/toggle",
            ['termine' => true],
            ['Authorization' => "Bearer $token"]
        )->assertStatus(403);
    }

    public function test_api_outsider_ne_peut_pas_supprimer_sous_tache(): void
    {
        $st    = $this->sousTache();
        $token = $this->outsider->createToken('test')->plainTextToken;

        $this->deleteJson(
            "/api/v1/sous-taches/{$st->id}",
            [],
            ['Authorization' => "Bearer $token"]
        )->assertStatus(403);
    }

    public function test_api_assignee_peut_creer_sous_tache(): void
    {
        $token = $this->assignee->createToken('test')->plainTextToken;

        $this->postJson(
            "/api/v1/taches/{$this->tache->id}/sous-taches",
            ['titre' => 'API légitime'],
            ['Authorization' => "Bearer $token"]
        )->assertStatus(201);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // COMMENTAIRES
    // ══════════════════════════════════════════════════════════════════════════

    public function test_outsider_ne_peut_pas_commenter_la_tache(): void
    {
        $this->actingAs($this->outsider)
             ->post(route('commentaires.store', $this->tache), ['contenu' => 'Intrusion'])
             ->assertStatus(403);
    }

    public function test_assignee_peut_commenter_la_tache(): void
    {
        $this->actingAs($this->assignee)
             ->post(route('commentaires.store', $this->tache), ['contenu' => 'Légitime'])
             ->assertRedirect();

        $this->assertDatabaseHas('commentaires', ['contenu' => 'Légitime']);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RAPPORTS
    // ══════════════════════════════════════════════════════════════════════════

    public function test_outsider_ne_peut_pas_soumettre_rapport(): void
    {
        $this->actingAs($this->outsider)
             ->post(route('rapports.store', $this->tache), ['contenu' => 'Intrusion'])
             ->assertStatus(403);
    }

    public function test_assignee_peut_soumettre_rapport(): void
    {
        $this->actingAs($this->assignee)
             ->post(route('rapports.store', $this->tache), ['contenu' => 'Rapport légit'])
             ->assertRedirect();

        $this->assertDatabaseHas('rapports', ['contenu' => 'Rapport légit']);
    }

    public function test_outsider_ne_peut_pas_supprimer_rapport_dautrui(): void
    {
        $rapport = $this->rapport();

        $this->actingAs($this->outsider)
             ->delete(route('rapports.destroy', $rapport))
             ->assertStatus(403);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // scope visiblePar() — non-régression
    // ══════════════════════════════════════════════════════════════════════════

    public function test_visible_par_ne_retourne_pas_les_taches_dautrui_avec_filtre_status(): void
    {
        $tacheInvisible = Tache::factory()->create([
            'createur_id' => $this->manager->id,
            'statut'      => 'en_cours',
        ]);

        $ids = Tache::visiblePar($this->outsider)
                    ->where('statut', 'en_cours')
                    ->pluck('id');

        $this->assertNotContains($tacheInvisible->id, $ids);
    }

    public function test_visible_par_retourne_les_taches_assignees_avec_filtre_status(): void
    {
        $this->tache->update(['statut' => 'en_cours']);

        $ids = Tache::visiblePar($this->assignee)
                    ->where('statut', 'en_cours')
                    ->pluck('id');

        $this->assertContains($this->tache->id, $ids);
    }
}

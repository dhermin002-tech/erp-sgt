<?php

namespace Tests\Feature\Commentaires;

use App\Models\Commentaire;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommentaireTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;
    private User $agent;
    private Tache $tache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = User::factory()->manager()->create();
        $this->agent   = User::factory()->create(['role' => 'agent']);
        $this->tache   = Tache::factory()->create(['createur_id' => $this->manager->id]);
        $this->tache->responsables()->attach($this->agent);
    }

    // ── Ajout commentaire ─────────────────────────────────────────────────────

    public function test_responsable_peut_commenter(): void
    {
        $this->actingAs($this->agent)
             ->post("/taches/{$this->tache->id}/commentaires", ['contenu' => 'Intervention en cours.'])
             ->assertRedirect();

        $this->assertDatabaseHas('commentaires', [
            'tache_id' => $this->tache->id,
            'user_id'  => $this->agent->id,
            'contenu'  => 'Intervention en cours.',
        ]);
    }

    public function test_commentaire_vide_retourne_erreur(): void
    {
        $this->actingAs($this->agent)
             ->post("/taches/{$this->tache->id}/commentaires", ['contenu' => ''])
             ->assertSessionHasErrors('contenu');
    }

    public function test_upload_photo_terrain(): void
    {
        Storage::fake('public');

        $photo = UploadedFile::fake()->image('chantier.jpg', 800, 600);

        $this->actingAs($this->agent)
             ->post("/taches/{$this->tache->id}/commentaires", [
                 'contenu' => 'Photo prise sur place.',
                 'photo'   => $photo,
             ])
             ->assertRedirect();

        $com = Commentaire::first();
        $this->assertNotNull($com->photo_path);
        Storage::disk('public')->assertExists($com->photo_path);
    }

    public function test_upload_fichier_non_image_rejete(): void
    {
        Storage::fake('public');

        $pdf = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

        $this->actingAs($this->agent)
             ->post("/taches/{$this->tache->id}/commentaires", [
                 'contenu' => 'Fichier invalide.',
                 'photo'   => $pdf,
             ])
             ->assertSessionHasErrors('photo');
    }

    // ── Suppression ──────────────────────────────────────────────────────────

    public function test_auteur_peut_supprimer_son_commentaire(): void
    {
        $com = Commentaire::create([
            'tache_id' => $this->tache->id,
            'user_id'  => $this->agent->id,
            'contenu'  => 'À supprimer.',
        ]);

        $this->actingAs($this->agent)
             ->delete("/commentaires/{$com->id}")
             ->assertRedirect();

        $this->assertSoftDeleted('commentaires', ['id' => $com->id]);
    }

    public function test_autre_user_ne_peut_pas_supprimer(): void
    {
        $autreAgent = User::factory()->create(['role' => 'agent']);
        $com = Commentaire::create([
            'tache_id' => $this->tache->id,
            'user_id'  => $this->agent->id,
            'contenu'  => 'Commentaire protégé.',
        ]);

        $this->actingAs($autreAgent)
             ->delete("/commentaires/{$com->id}")
             ->assertStatus(403);
    }

    public function test_manager_peut_supprimer_tout_commentaire(): void
    {
        $com = Commentaire::create([
            'tache_id' => $this->tache->id,
            'user_id'  => $this->agent->id,
            'contenu'  => 'Commentaire agent.',
        ]);

        $this->actingAs($this->manager)
             ->delete("/commentaires/{$com->id}")
             ->assertRedirect();

        $this->assertSoftDeleted('commentaires', ['id' => $com->id]);
    }

    // ── Notifications ─────────────────────────────────────────────────────────

    public function test_notification_envoyee_lors_assignation(): void
    {
        $nouveauAgent = User::factory()->create(['role' => 'agent']);

        $this->actingAs($this->manager)
             ->post('/taches', [
                 'titre'        => 'Tâche notifiée',
                 'responsables' => [$nouveauAgent->id],
                 'statut'       => 'nouveau',
                 'progression'  => 0,
                 'priorite'     => 'normale',
             ])
             ->assertRedirect();

        $this->assertCount(1, $nouveauAgent->fresh()->notifications);
        $this->assertEquals('assignation', $nouveauAgent->fresh()->notifications->first()->data['type']);
    }

    public function test_notification_changement_statut(): void
    {
        $this->actingAs($this->manager)
             ->patchJson("/taches/{$this->tache->id}/statut", ['statut' => 'en_cours']);

        $this->assertCount(1, $this->agent->fresh()->notifications);
        $this->assertEquals('statut', $this->agent->fresh()->notifications->first()->data['type']);
    }

    public function test_marquer_toutes_notif_lues(): void
    {
        $this->tache->responsables->each(fn($r) => $r->notify(
            new \App\Notifications\StatutTacheNotification($this->tache, 'en_cours', 'Manager')
        ));

        $this->actingAs($this->agent)
             ->patch(route('notifications.tout-lire'))
             ->assertRedirect();

        $this->assertEquals(0, $this->agent->fresh()->unreadNotifications()->count());
    }
}

<?php

namespace App\Notifications;

use App\Models\Tache;
use Illuminate\Notifications\Notification;

class StatutTacheNotification extends Notification
{
    public function __construct(private Tache $tache, private string $nouveauStatut, private string $auteur) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $libelle = Tache::libelleStatut($this->nouveauStatut);

        return [
            'type'    => 'statut',
            'tache_id' => $this->tache->id,
            'tache'   => $this->tache->titre,
            'statut'  => $this->nouveauStatut,
            'auteur'  => $this->auteur,
            'message' => "La tâche « {$this->tache->titre} » est maintenant « {$libelle} » (modifié par {$this->auteur}).",
            'url'     => route('taches.show', $this->tache->id),
        ];
    }
}

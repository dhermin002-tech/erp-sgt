<?php

namespace App\Notifications;

use App\Models\Tache;
use App\Models\User;
use Illuminate\Notifications\Notification;

class TacheCreeeNotification extends Notification
{
    public function __construct(private Tache $tache, private User $createur) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $estAgent = $this->createur->type_compte === 'agent_ia';
        $auteur   = $estAgent ? "🤖 {$this->createur->nom_complet}" : $this->createur->nom_complet;

        return [
            'type'      => 'creation',
            'tache_id'  => $this->tache->id,
            'tache'     => $this->tache->titre,
            'createur'  => $this->createur->nom_complet,
            'par_agent' => $estAgent,
            'message'   => "Nouvelle tâche « {$this->tache->titre} » créée par {$auteur}.",
            'url'       => route('taches.show', $this->tache->id),
        ];
    }
}

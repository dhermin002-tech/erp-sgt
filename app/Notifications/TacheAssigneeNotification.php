<?php

namespace App\Notifications;

use App\Models\Tache;
use Illuminate\Notifications\Notification;

class TacheAssigneeNotification extends Notification
{
    public function __construct(private Tache $tache, private string $assigneur) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'      => 'assignation',
            'tache_id'  => $this->tache->id,
            'tache'     => $this->tache->titre,
            'assigneur' => $this->assigneur,
            'message'   => "Vous avez été assigné à la tâche « {$this->tache->titre} » par {$this->assigneur}.",
            'url'       => route('taches.show', $this->tache->id),
        ];
    }
}

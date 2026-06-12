<?php

namespace App\Services;

use App\Models\Tache;
use App\Models\User;
use App\Notifications\TacheAssigneeNotification;
use App\Notifications\TacheCreeeNotification;

/**
 * Centralise les notifications déclenchées à la création d'une tâche.
 * Appelé depuis la création web (TacheController) ET la création API par
 * les agents IA (TacheApiController) — garantit un comportement identique
 * quelle que soit la source de la tâche.
 */
class TacheNotifier
{
    public static function notifierCreation(Tache $tache, User $createur): void
    {
        $tache->loadMissing('responsables');

        // 1. Notifier chaque responsable humain assigné (sauf le créateur)
        foreach ($tache->responsables as $responsable) {
            if ($responsable->id !== $createur->id && $responsable->type_compte === 'humain') {
                $responsable->notify(new TacheAssigneeNotification($tache, $createur->nom_complet));
            }
        }

        // 2. Notifier les managers (supervision) — pour qu'ils voient toute création,
        //    en particulier celles des agents IA. On évite le créateur lui-même et
        //    les managers déjà notifiés comme responsables (pas de doublon).
        $managers = User::where('role', 'manager')
            ->where('type_compte', 'humain')
            ->where('id', '!=', $createur->id)
            ->get();

        foreach ($managers as $manager) {
            if ($tache->responsables->contains('id', $manager->id)) {
                continue;
            }
            $manager->notify(new TacheCreeeNotification($tache, $createur));
        }
    }
}

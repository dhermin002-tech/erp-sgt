<?php

namespace App\Services;

use App\Jobs\EnvoyerNotificationPush;
use App\Models\AppareilPush;
use App\Models\OrdreIa;
use App\Models\Tache;
use App\Models\User;

class NotificationPush
{
    public function assignation(Tache $tache, array $responsables): void
    {
        $this->mettreEnFile($responsables, $tache->id, 'assignation', 'Nouvelle tâche', 'Une tâche vous a été assignée.');
    }

    public function echeancesDemain(): void
    {
        $demain = now()->addDay()->startOfDay();
        Tache::query()->actives()
            ->where('date_echeance', '>=', $demain->toDateString())
            ->where('date_echeance', '<', $demain->copy()->addDay()->toDateString())
            ->with('responsables:id')->chunkById(100, function ($taches) {
                foreach ($taches as $tache) {
                    $this->mettreEnFile($tache->responsables->modelKeys(), $tache->id, 'echeance',
                        'Échéance demain', 'Une de vos tâches arrive à échéance demain.');
                }
            });
    }

    public function ordreRendu(OrdreIa $ordre): void
    {
        $managers = User::query()->whereKey($ordre->emis_par)->where('role', 'manager')->pluck('id')->all();
        $this->mettreEnFile($managers, $ordre->tache_id, 'ordre_rendu', 'Ordre IA traité', 'Un compte rendu est disponible.');
    }

    private function mettreEnFile(array $utilisateurs, int $tacheId, string $type, string $titre, string $texte): void
    {
        if ($utilisateurs === []) {
            return;
        }

        AppareilPush::query()->select('id')->whereIn('user_id', $utilisateurs)
            ->eachById(fn (AppareilPush $appareil) => EnvoyerNotificationPush::dispatch($appareil->id, [
                'type' => $type, 'titre' => $titre, 'texte' => $texte, 'tache_id' => $tacheId,
            ])->onConnection(config('push.file'))->afterCommit());
    }
}

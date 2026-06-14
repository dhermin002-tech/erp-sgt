<?php

namespace App\Observers;

use App\Models\RapportAgent;
use App\Models\Tache;

class TacheObserver
{
    /**
     * Quand une tâche passe à "terminé", publie automatiquement un rapport
     * au nom de chaque agent IA responsable (rend le menu Rapports IA vivant).
     */
    public function updated(Tache $tache): void
    {
        if (! $tache->wasChanged('statut') || $tache->statut !== 'termine') {
            return;
        }

        $tache->loadMissing('responsables');

        foreach ($tache->responsables->where('type_compte', 'agent_ia') as $agent) {
            $titre = "Tâche terminée : {$tache->titre}";

            // Anti-doublon : un seul rapport de fin par tâche/agent
            $existe = RapportAgent::where('user_id', $agent->id)
                ->where('type', 'quotidien')
                ->where('titre', $titre)
                ->exists();

            if ($existe) {
                continue;
            }

            RapportAgent::create([
                'user_id' => $agent->id,
                'projet'  => $tache->projet ?: 'SGT',
                'type'    => 'quotidien',
                'titre'   => $titre,
                'contenu' => "L'agent {$agent->agent_code} a terminé la tâche « {$tache->titre} »."
                    . ($tache->projet ? "\nProjet : {$tache->projet}." : '')
                    . "\nProgression : 100%.",
                'statut'  => 'info',
                'meta'    => ['tache_id' => $tache->id, 'agent_code' => $agent->agent_code],
            ]);
        }
    }
}

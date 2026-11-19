<?php

namespace App\Services;

use App\Models\Tache;
use App\Models\User;
use Illuminate\Support\Carbon;

class AnalyseTaches
{
    public function synthese(User $utilisateur, array $periode): array
    {
        $taches = Tache::query()->visiblePar($utilisateur)
            ->when(isset($periode['du']), fn ($query) => $query->where('created_at', '>=', Carbon::parse($periode['du'])->startOfDay()))
            ->when(isset($periode['au']), fn ($query) => $query->where('created_at', '<', Carbon::parse($periode['au'])->addDay()->startOfDay()))
            ->with('responsables:id,nom,prenom,type_compte')
            ->select('id', 'titre', 'statut', 'priorite', 'projet', 'date_echeance', 'created_at', 'terminee_at', 'archived_at');

        $statuts = array_fill_keys(['nouveau', 'en_cours', 'en_attente', 'en_arret', 'termine'], 0);
        $priorites = array_fill_keys(['basse', 'normale', 'haute', 'urgente'], 0);
        $projets = $responsables = $retards = [];
        $durees = $traitees = $avecEcheance = $dansLesDelais = $total = 0;
        $aujourdhui = now()->startOfDay();

        foreach ($taches->lazyById(250) as $tache) {
            $total++;
            $statuts[$tache->statut]++;
            $priorites[$tache->priorite]++;
            $projet = $tache->projet ?: 'Sans projet';
            $projets[$projet] = ($projets[$projet] ?? 0) + 1;

            if ($tache->terminee_at) {
                $traitees++;
                $durees += max(0, $tache->created_at->diffInSeconds($tache->terminee_at, false));
                if ($tache->date_echeance) {
                    $avecEcheance++;
                    $dansLesDelais += (int) ($tache->terminee_at->toDateString() <= $tache->date_echeance->toDateString());
                }
            }

            if (! $tache->archived_at && $tache->statut !== 'termine' && $tache->date_echeance?->lt($aujourdhui)) {
                $retards[] = ['id' => $tache->id, 'titre' => $tache->titre, 'date_echeance' => $tache->date_echeance->toDateString()];
            }

            foreach ($tache->responsables as $responsable) {
                if (! $utilisateur->isManager() && $responsable->id !== $utilisateur->id) {
                    continue;
                }
                $id = $responsable->id;
                $responsables[$id] ??= [
                    'id' => $id,
                    'nom_complet' => $responsable->nom_complet,
                    'type_compte' => $responsable->type_compte,
                    'total' => 0,
                    'en_cours' => 0,
                ];
                $responsables[$id]['total']++;
                $responsables[$id]['en_cours'] += (int) (! $tache->archived_at && $tache->statut === 'en_cours');
            }
        }

        return [
            'total' => $total,
            'par_statut' => $statuts,
            'par_priorite' => $priorites,
            'par_projet' => (object) $projets,
            'par_responsable' => array_values($responsables),
            'charge_en_cours' => array_values(array_map(
                fn (array $personne) => ['id' => $personne['id'], 'en_cours' => $personne['en_cours']],
                $responsables,
            )),
            'taux_respect_echeances' => $avecEcheance ? round(100 * $dansLesDelais / $avecEcheance, 2) : null,
            'delai_moyen_traitement_secondes' => $traitees ? (int) round($durees / $traitees) : null,
            'taches_en_retard' => $retards,
        ];
    }
}

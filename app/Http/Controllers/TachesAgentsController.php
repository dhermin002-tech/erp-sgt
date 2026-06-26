<?php

namespace App\Http\Controllers;

use App\Models\Tache;
use App\Models\User;
use Illuminate\Http\Request;

class TachesAgentsController extends Controller
{
    /**
     * Tâches attribuées aux agents IA, groupées par agent RESPONSABLE (l'exécutant).
     * project-agent reste le créateur (définisseur) — affiché en méta sur chaque tâche.
     */
    public function index(Request $request)
    {
        $query = Tache::query()
            ->whereNull('archived_at')
            ->whereHas('responsables', fn($q) => $q->where('type_compte', 'agent_ia'))
            ->with(['createur', 'responsables', 'site', 'sousTaches']);

        if ($request->filled('agent_id')) {
            $query->whereHas('responsables', fn($q) => $q->where('users.id', $request->agent_id));
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        } else {
            // Par défaut : exclure les terminées (elles vont dans /taches/archives)
            $query->whereNotIn('statut', Tache::STATUTS_TERMINAUX);
        }

        $taches = $query->get();

        // Agents exécutants (responsables) ayant au moins une tâche, classés par rôle métier
        $agentsAvecTaches = User::where('type_compte', 'agent_ia')
            ->whereHas('taches', fn($q) => $q->whereNull('archived_at'))
            ->get()
            ->sortBy(fn($a) => [$a->rangHierarchique(), $a->agent_code])
            ->values();

        // KPIs
        $kpis = [
            'total'      => $taches->count(),
            'agents'     => $agentsAvecTaches->count(),
            'en_cours'   => $taches->where('statut', 'en_cours')->count(),
            'terminees'  => $taches->where('statut', 'termine')->count(),
        ];

        return view('agents.taches', compact('taches', 'agentsAvecTaches', 'kpis'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Tache;
use App\Models\User;
use Illuminate\Http\Request;

class TachesAgentsController extends Controller
{
    /**
     * Liste dédiée des tâches créées par les agents IA (suivi isolé).
     */
    public function index(Request $request)
    {
        $query = Tache::query()
            ->whereNull('archived_at')
            ->whereHas('createur', fn($q) => $q->where('type_compte', 'agent_ia'))
            ->with(['createur', 'responsables', 'site']);

        if ($request->filled('agent_id')) {
            $query->where('createur_id', $request->agent_id);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Tri par rang hiérarchique du créateur agent, puis par date récente
        $taches = $query->get()
            ->sortBy(fn($t) => [$t->createur->rangHierarchique(), $t->createur->agent_code, -$t->created_at->timestamp])
            ->values();

        // Agents IA ayant au moins une tâche (pour le filtre), classés par rôle métier
        $agentsAvecTaches = User::where('type_compte', 'agent_ia')
            ->whereHas('tachesCreees', fn($q) => $q->whereNull('archived_at'))
            ->get()
            ->sortBy(fn($a) => [$a->rangHierarchique(), $a->agent_code])
            ->values();

        // KPIs
        $kpis = [
            'total'      => $taches->count(),
            'agents'     => $taches->pluck('createur_id')->unique()->count(),
            'en_cours'   => $taches->where('statut', 'en_cours')->count(),
            'terminees'  => $taches->where('statut', 'termine')->count(),
        ];

        return view('agents.taches', compact('taches', 'agentsAvecTaches', 'kpis'));
    }
}

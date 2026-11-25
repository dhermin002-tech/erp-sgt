<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TacheApiResource;
use App\Models\RapportAgent;
use App\Models\SessionAgent;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupervisionApiController extends Controller
{
    public function taches(Request $request)
    {
        $filtres = $request->validate([
            'agent_id' => 'sometimes|integer|min:1',
            'statut' => ['sometimes', Rule::in(['nouveau', 'en_cours', 'en_attente', 'en_arret', 'termine'])],
        ]);
        $agents = User::query()->where('type_compte', 'agent_ia')
            ->whereHas('taches', fn ($query) => $query->whereNull('archived_at'))
            ->orderBy('nom')->get(['id', 'nom', 'prenom', 'agent_code']);
        $taches = Tache::query()->whereNull('archived_at')
            ->whereHas('responsables', fn ($query) => $query->where('type_compte', 'agent_ia')
                ->when(isset($filtres['agent_id']), fn ($responsables) => $responsables->whereKey($filtres['agent_id'])))
            ->when(isset($filtres['statut']), fn ($query) => $query->where('statut', $filtres['statut']),
                fn ($query) => $query->whereNotIn('statut', Tache::STATUTS_TERMINAUX))
            ->with(['responsables', 'createur', 'site'])
            ->latest()->orderByDesc('id')->paginate(20)->withQueryString();

        return TacheApiResource::collection($taches)->additional(['agents' => $agents]);
    }

    public function rapports(Request $request)
    {
        $filtres = $request->validate([
            'agent_id' => 'sometimes|integer|min:1',
            'projet' => 'sometimes|string|max:100',
            'type' => 'sometimes|string|max:40',
            'statut' => 'sometimes|string|max:40',
        ]);

        return response()->json(RapportAgent::select(['id', 'user_id', 'projet', 'type', 'titre', 'contenu', 'statut', 'created_at'])
            ->with('user:id,nom,prenom,agent_code')
            ->when(isset($filtres['agent_id']), fn ($query) => $query->where('user_id', $filtres['agent_id']))
            ->when(isset($filtres['projet']), fn ($query) => $query->where('projet', $filtres['projet']))
            ->when(isset($filtres['type']), fn ($query) => $query->where('type', $filtres['type']))
            ->when(isset($filtres['statut']), fn ($query) => $query->where('statut', $filtres['statut']))
            ->latest()->orderByDesc('id')->paginate(20));
    }

    public function sessions(Request $request)
    {
        $filtres = $request->validate([
            'agent_id' => 'sometimes|integer|min:1',
            'projet' => 'sometimes|string|max:100',
            'statut' => 'sometimes|string|max:40',
        ]);

        return response()->json(SessionAgent::select(['id', 'user_id', 'projet', 'contexte', 'demarree_a', 'terminee_a', 'statut', 'resume'])
            ->with('user:id,nom,prenom,agent_code')
            ->when(isset($filtres['agent_id']), fn ($query) => $query->where('user_id', $filtres['agent_id']))
            ->when(isset($filtres['projet']), fn ($query) => $query->where('projet', 'like', '%'.$filtres['projet'].'%'))
            ->when(isset($filtres['statut']), fn ($query) => $query->where('statut', $filtres['statut']))
            ->latest('demarree_a')->orderByDesc('id')->paginate(25));
    }

    public function rapportGlobal(Request $request)
    {
        $filtres = $request->validate([
            'statut' => ['sometimes', Rule::in(['nouveau', 'en_cours', 'en_attente', 'en_arret', 'termine'])],
            'site_id' => 'sometimes|integer|min:1',
            'responsable_id' => 'sometimes|integer|min:1',
            'vue' => 'sometimes|in:actives,terminees',
        ]);
        $base = Tache::visiblePar($request->user())->with(['responsables', 'site'])
            ->when(isset($filtres['site_id']), fn ($query) => $query->where('site_id', $filtres['site_id']));
        if (($filtres['vue'] ?? 'actives') === 'terminees') {
            $base->where(fn ($query) => $query->whereNotNull('archived_at')->orWhere('statut', 'termine'))
                ->latest('updated_at');
        } else {
            $base->actives()->orderBy('date_echeance')
                ->when(isset($filtres['statut']), fn ($query) => $query->where('statut', $filtres['statut']))
                ->when($request->user()->isManager() && isset($filtres['responsable_id']),
                    fn ($query) => $query->whereHas('responsables',
                        fn ($responsables) => $responsables->whereKey($filtres['responsable_id'])));
        }

        return TacheApiResource::collection($base->orderByDesc('id')->paginate(20)->withQueryString());
    }
}

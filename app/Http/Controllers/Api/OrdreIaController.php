<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrdreIa;
use App\Models\User;
use App\Services\OrdresIa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrdreIaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->isManager() || $user->isAgentIa(), 403);
        $filtres = $request->validate([
            'statut' => ['sometimes', Rule::in(OrdreIa::STATUTS)],
            'cible' => ['sometimes', Rule::in(OrdreIa::CIBLES)],
            'projet' => ['sometimes', 'string', 'max:100'],
        ]);

        $ordres = OrdreIa::query()->whereHas('tache', fn ($query) => $query->visiblePar($user));
        if (! $user->isManager()) {
            $ordres->whereIn('cible', [$this->cible($user), 'tout_agent'])
                ->where(fn ($query) => $query->whereNull('pris_par_user_id')->orWhere('pris_par_user_id', $user->id));
        }

        return response()->json($ordres->filtrer($filtres)
            ->with(['tache:id,titre', 'emetteur:id,prenom,nom'])->orderBy('id')->paginate(25));
    }

    public function store(Request $request, OrdresIa $ordres): JsonResponse
    {
        abort_unless($request->user()->isManager(), 403, 'Seul le manager peut émettre un ordre.');
        $donnees = $request->validate([
            'tache_id' => ['required', 'integer', Rule::exists('taches', 'id')->whereNull('deleted_at')],
            'cible' => ['required', Rule::in(OrdreIa::CIBLES)],
            'projet_dossier' => ['required', 'string', Rule::in(array_keys(config('ordres_ia.projets')))],
            'consigne' => ['required', 'string', 'max:10000'],
        ]);

        return response()->json(['data' => $ordres->emettre($donnees, $request->user())], 201);
    }

    public function prendre(Request $request, OrdreIa $ordre, OrdresIa $ordres): JsonResponse
    {
        $agent = $request->user();
        abort_unless($agent->isAgentIa() && $agent->agent_code && $ordre->tache && $agent->canAccessTache($ordre->tache), 403);
        abort_unless(in_array($ordre->cible, [$this->cible($agent), 'tout_agent'], true), 403);
        $donnees = $request->validate(['session_id' => ['required', 'string', 'max:100']]);

        return response()->json(['data' => $ordres->prendre($ordre, $agent, $donnees['session_id'])]);
    }

    public function rendreCompte(Request $request, OrdreIa $ordre, OrdresIa $ordres): JsonResponse
    {
        $agent = $request->user();
        abort_unless($agent->isAgentIa() && $ordre->tache && $agent->canAccessTache($ordre->tache), 403);
        $donnees = $request->validate([
            'session_id' => ['required', 'string', 'max:100'],
            'statut' => ['required', Rule::in(['fait', 'rejete'])],
            'compte_rendu' => ['required', 'string', 'max:10000'],
        ]);

        return response()->json(['data' => $ordres->rendreCompte($ordre, $agent, $donnees)]);
    }

    public function annuler(Request $request, OrdreIa $ordre, OrdresIa $ordres): JsonResponse
    {
        abort_unless($request->user()->isManager(), 403);

        return response()->json(['data' => $ordres->annuler($ordre)]);
    }

    private function cible(User $agent): string
    {
        return $agent->agent_code === 'codex' ? 'codex' : 'claude_code';
    }
}

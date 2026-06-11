<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TacheApiResource;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TacheApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = Tache::query()
            ->visiblePar($user)
            ->whereNull('archived_at')
            ->with(['site', 'createur', 'responsables', 'sousTaches']);

        if ($request->statut) {
            $query->where('statut', $request->statut);
        }
        if ($request->priorite) {
            $query->where('priorite', $request->priorite);
        }
        if ($request->site_id) {
            $query->where('site_id', $request->site_id);
        }
        if ($request->en_retard) {
            $query->enRetard();
        }

        $taches = $query->orderByDesc('created_at')->get();

        return response()->json([
            'data' => TacheApiResource::collection($taches),
            'meta' => [
                'total'      => $taches->count(),
                'par_statut' => [
                    'nouveau'    => $taches->where('statut', 'nouveau')->count(),
                    'en_cours'   => $taches->where('statut', 'en_cours')->count(),
                    'en_attente' => $taches->where('statut', 'en_attente')->count(),
                    'en_arret'   => $taches->where('statut', 'en_arret')->count(),
                    'termine'    => $taches->where('statut', 'termine')->count(),
                ],
            ],
        ]);
    }

    public function show(Request $request, Tache $tache): JsonResponse
    {
        $this->authorizeAccess($request->user(), $tache);

        $tache->load(['site', 'createur', 'responsables', 'sousTaches', 'commentaires.user']);

        return response()->json(['data' => new TacheApiResource($tache)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'titre'          => 'required|string|max:255',
            'description'    => 'nullable|string|max:5000',
            'responsables'   => 'required|array|min:1',
            'responsables.*' => 'exists:users,id',
            'site_id'        => 'nullable|exists:sites,id',
            'date_debut'     => 'nullable|date',
            'date_echeance'  => 'nullable|date|after_or_equal:date_debut',
            'statut'         => 'in:nouveau,en_cours,en_attente,en_arret,termine',
            'priorite'       => 'in:basse,normale,haute,urgente',
        ]);

        $tache = Tache::create([
            'titre'         => $data['titre'],
            'description'   => $data['description'] ?? null,
            'createur_id'   => $request->user()->id,
            'site_id'       => $data['site_id'] ?? null,
            'date_debut'    => $data['date_debut'] ?? null,
            'date_echeance' => $data['date_echeance'] ?? null,
            'statut'        => $data['statut'] ?? 'nouveau',
            'priorite'      => $data['priorite'] ?? 'normale',
            'progression'   => 0,
        ]);

        $tache->responsables()->sync($data['responsables']);
        $tache->load(['site', 'createur', 'responsables']);

        return response()->json([
            'message' => 'Tâche créée avec succès.',
            'data'    => new TacheApiResource($tache),
        ], 201);
    }

    public function changerStatut(Request $request, Tache $tache): JsonResponse
    {
        $this->authorizeAccess($request->user(), $tache);

        $data = $request->validate([
            'statut' => ['required', Rule::in(['nouveau','en_cours','en_attente','en_arret','termine'])],
        ]);

        $tache->update(['statut' => $data['statut']]);

        if ($data['statut'] === 'termine' && ! $tache->archived_at) {
            $tache->update(['archived_at' => now()]);
        }

        return response()->json([
            'ok'     => true,
            'statut' => $tache->statut,
            'libelle'=> Tache::libelleStatut($tache->statut),
        ]);
    }

    public function sousTaches(Request $request, Tache $tache): JsonResponse
    {
        $this->authorizeAccess($request->user(), $tache);
        $tache->load('sousTaches');

        return response()->json([
            'data' => $tache->sousTaches->map(fn($st) => [
                'id'      => $st->id,
                'titre'   => $st->titre,
                'termine' => (bool) $st->termine,
                'ordre'   => $st->ordre,
            ]),
            'progression' => $tache->progression,
        ]);
    }

    public function majProgression(Request $request, Tache $tache): JsonResponse
    {
        $this->authorizeAccess($request->user(), $tache);

        $request->user()->tokenCan('taches:update')
            || abort(403, 'Token sans permission taches:update.');

        $data = $request->validate([
            'progression' => 'required|integer|min:0|max:100',
        ]);

        $tache->update(['progression' => $data['progression']]);

        return response()->json([
            'ok'          => true,
            'progression' => $tache->progression,
            'tache_id'    => $tache->id,
        ]);
    }

    public function destroy(Request $request, Tache $tache): JsonResponse
    {
        if (! $request->user()->isManager()) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $tache->delete();

        return response()->json(['message' => 'Tâche supprimée.'], 200);
    }

    private function authorizeAccess(User $user, Tache $tache): void
    {
        if ($user->isManager()) return;

        $visible = $tache->responsables->contains($user->id)
            || $tache->createur_id === $user->id;

        abort_unless($visible, 403, 'Accès refusé.');
    }
}

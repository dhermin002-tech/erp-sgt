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
            ->actives()
            ->with(['site:id,nom', 'createur:id,prenom,nom', 'responsables:id,prenom,nom,role'])
            ->withCount(['sousTaches', 'sousTaches as sous_taches_terminees_count' => fn ($query) => $query->where('termine', true)]);

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
        $parStatut = $taches->countBy('statut');

        return response()->json([
            'data' => TacheApiResource::collection($taches),
            'meta' => [
                'total'      => $taches->count(),
                'par_statut' => [
                    'nouveau'    => $parStatut->get('nouveau', 0),
                    'en_cours'   => $parStatut->get('en_cours', 0),
                    'en_attente' => $parStatut->get('en_attente', 0),
                    'en_arret'   => $parStatut->get('en_arret', 0),
                    'termine'    => $parStatut->get('termine', 0),
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
            'projet'         => 'nullable|string|max:40',
            'description'    => 'nullable|string|max:5000',
            'responsables'   => 'required|array|min:1',
            'responsables.*' => 'exists:users,id',
            'site_id'        => 'nullable|exists:sites,id',
            'date_debut'     => 'nullable|date',
            'date_echeance'  => 'nullable|date|after_or_equal:date_debut',
            'statut'         => 'in:nouveau,en_cours,en_attente,en_arret,termine',
            'priorite'       => 'in:basse,normale,haute,urgente',
        ]);

        abort_if(($data['statut'] ?? null) === 'termine' && ! $request->user()->isManager(), 403, 'Seul le manager peut clôturer une tâche.');

        $tache = Tache::create([
            'titre'         => $data['titre'],
            'projet'        => $data['projet'] ?? null,
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

        // Notifie responsables + managers — même service que la création web,
        // pour que la cloche se déclenche aussi quand un agent IA crée une tâche.
        \App\Services\TacheNotifier::notifierCreation($tache, $request->user());

        $tache->load(['site', 'createur', 'responsables']);

        return response()->json([
            'message' => 'Tâche créée avec succès.',
            'data'    => new TacheApiResource($tache),
        ], 201);
    }

    public function update(Request $request, Tache $tache): JsonResponse
    {
        $this->authorizeAccess($request->user(), $tache);

        $data = $request->validate([
            'titre'               => 'sometimes|string|max:255',
            'projet'              => 'sometimes|nullable|string|max:40',
            'description'        => 'sometimes|nullable|string|max:5000',
            'priorite'           => ['sometimes', Rule::in(['basse','normale','haute','urgente'])],
            'statut'             => ['sometimes', Rule::in(['nouveau','en_cours','en_attente','en_arret','termine'])],
            'date_debut'         => 'sometimes|nullable|date',
            'date_echeance'      => 'sometimes|nullable|date',
            'site_id'            => 'sometimes|nullable|exists:sites,id',
            'responsables'       => 'sometimes|nullable|array',
            'responsables.*'     => 'exists:users,id',
            'responsables_codes' => 'sometimes|nullable|array',
            'responsables_codes.*'=> 'string',
        ]);

        // statut "termine" réservé aux managers
        if (isset($data['statut']) && $data['statut'] === 'termine' && ! $request->user()->isManager()) {
            return response()->json(['message' => 'Seul un manager peut clôturer une tâche.'], 403);
        }

        // Résolution des codes agents en IDs
        if (isset($data['responsables_codes'])) {
            $codesInconnus = [];
            $idsAgents     = [];

            foreach ($data['responsables_codes'] as $code) {
                $agent = User::where('agent_code', $code)->first();
                if (! $agent) {
                    $codesInconnus[] = $code;
                } else {
                    $idsAgents[] = $agent->id;
                }
            }

            if ($codesInconnus) {
                return response()->json([
                    'message' => 'Code(s) agent inconnu(s) : ' . implode(', ', $codesInconnus),
                ], 422);
            }

            // Fusionner avec les responsables humains si fournis ensemble
            $data['responsables'] = array_merge($data['responsables'] ?? [], $idsAgents);
            unset($data['responsables_codes']);
        }

        // Mise à jour des champs scalaires
        $champs = ['titre','projet','description','priorite','statut','date_debut','date_echeance','site_id'];
        $tache->update(array_intersect_key($data, array_flip($champs)));

        // Plus d'archivage automatique sur "terminé" (cohérence avec le web) :
        // la tâche validée reste visible, l'archivage est une action distincte.

        // Sync responsables : clé présente = remplacement, absente = ne pas toucher
        if (array_key_exists('responsables', $data)) {
            $affectations = $tache->responsables()->sync($data['responsables'] ?? []);
            app(\App\Services\NotificationPush::class)->assignation($tache, $affectations['attached']);
        }

        $tache->load(['site', 'createur', 'responsables', 'sousTaches']);

        return response()->json([
            'success' => true,
            'message' => 'Tâche mise à jour avec succès.',
            'data'    => new TacheApiResource($tache),
        ]);
    }

    public function changerStatut(Request $request, Tache $tache): JsonResponse
    {
        $this->authorizeAccess($request->user(), $tache);

        $data = $request->validate([
            'statut' => ['required', Rule::in(['nouveau','en_cours','en_attente','en_arret','termine'])],
        ]);

        abort_if($data['statut'] === 'termine' && ! $request->user()->isManager(), 403, 'Seul le manager peut clôturer une tâche.');
        $tache->update(['statut' => $data['statut']]);

        // Plus d'archivage automatique sur "terminé" (cohérence avec le web).

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

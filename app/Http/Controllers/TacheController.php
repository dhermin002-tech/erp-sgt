<?php

namespace App\Http\Controllers;

use App\Http\Requests\TacheRequest;
use App\Models\Site;
use App\Models\Tache;
use App\Models\User;
use App\Notifications\StatutTacheNotification;
use App\Notifications\TacheAssigneeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TacheController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        // Liste = tâches non archivées (les tâches "terminé" restent visibles,
        // regroupées dans la section "Terminées" de la vue). L'archivage est
        // désormais une action manuelle distincte (voir archiver()).
        $query = Tache::with(['responsables', 'site', 'createur'])
                      ->visiblePar($user)
                      ->whereNull('archived_at')
                      ->whereNotIn('statut', Tache::STATUTS_TERMINAUX);

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }
        if ($request->filled('responsable_id')) {
            $query->whereHas('responsables', fn($q) => $q->where('users.id', $request->responsable_id));
        }
        if (in_array($request->createur, ['agent_ia', 'humain'], true)) {
            $query->whereHas('createur', fn($q) => $q->where('type_compte', $request->createur));
        }
        if ($request->filled('projet')) {
            $query->where('projet', $request->projet);
        }
        if ($request->filled('q')) {
            $query->where(fn($q) => $q->where('titre', 'like', "%{$request->q}%")
                                      ->orWhere('description', 'like', "%{$request->q}%"));
        }

        // Tri
        $sort = $request->get('sort', 'date_echeance');
        $dir  = $request->get('dir', 'asc');
        if (in_array($sort, ['titre', 'statut', 'priorite', 'date_echeance', 'progression', 'created_at'])) {
            $query->orderBy($sort, $dir === 'desc' ? 'desc' : 'asc');
        }

        $sites    = Site::where('actif', true)->orderBy('nom')->get();
        $membres  = User::where('type_compte', 'humain')->orderBy('nom')->get();
        $statuts  = ['nouveau', 'en_cours', 'en_attente', 'en_arret', 'termine'];
        $projets  = Tache::whereNotNull('projet')->distinct()->orderBy('projet')->pluck('projet');

        // Mode groupé par responsable (avec collapsible)
        $grouperParUser = $request->boolean('grouper', false);
        if ($grouperParUser) {
            $toutesLesToutes = $query->get();
            $tachesGroupees  = $toutesLesToutes->groupBy(function ($tache) {
                $resp = $tache->responsables->first();
                return $resp ? $resp->id : 0;
            })->map(fn($group) => $group->sortBy('date_echeance'));
            $taches = null;
            return view('taches.index', compact('taches', 'tachesGroupees', 'sites', 'membres', 'statuts', 'grouperParUser', 'projets'));
        }

        $taches = $query->paginate(15)->withQueryString();
        $tachesGroupees = null;
        return view('taches.index', compact('taches', 'tachesGroupees', 'sites', 'membres', 'statuts', 'grouperParUser', 'projets'));
    }

    public function create()
    {
        $sites   = Site::where('actif', true)->orderBy('nom')->get();
        $membres = User::orderBy('nom')->get();
        return view('taches.create', compact('sites', 'membres'));
    }

    public function store(TacheRequest $request)
    {
        $tache = Tache::create(array_merge(
            $request->safe()->except('responsables'),
            ['createur_id' => Auth::id()]
        ));

        $tache->responsables()->sync($request->responsables);

        // Notifie responsables + managers (service centralisé, identique à l'API)
        \App\Services\TacheNotifier::notifierCreation($tache, Auth::user());

        return redirect()->route('taches.show', $tache)
                         ->with('success', 'Tâche créée avec succès.');
    }

    public function show(Tache $tache)
    {
        $this->authorizeAccess($tache);

        $tache->load(['responsables', 'site', 'createur', 'sousTaches', 'commentaires.user', 'rapports.user', 'actionsSuivi.user']);

        return view('taches.show', compact('tache'));
    }

    public function edit(Tache $tache)
    {
        $this->authorizeAccess($tache);

        $sites   = Site::where('actif', true)->orderBy('nom')->get();
        $membres = User::orderBy('nom')->get();
        return view('taches.edit', compact('tache', 'sites', 'membres'));
    }

    public function update(TacheRequest $request, Tache $tache)
    {
        $this->authorizeAccess($tache);

        $ancienStatut = $tache->statut;
        $tache->update($request->safe()->except('responsables'));

        // Détecter les nouveaux responsables pour notifier
        $ancienIds = $tache->responsables->pluck('id')->toArray();
        $tache->responsables()->sync($request->responsables);
        $tache->load('responsables');
        $nouveauxIds = array_diff($request->responsables, $ancienIds);

        $auteur = Auth::user();
        foreach ($tache->responsables->whereIn('id', $nouveauxIds) as $r) {
            if ($r->id !== $auteur->id) {
                $r->notify(new TacheAssigneeNotification($tache, $auteur->nom_complet));
            }
        }

        // Notifier les responsables si statut a changé
        if ($ancienStatut !== $tache->statut) {
            foreach ($tache->responsables as $r) {
                if ($r->id !== $auteur->id) {
                    $r->notify(new StatutTacheNotification($tache, $tache->statut, $auteur->nom_complet));
                }
            }
        }

        // Note : plus d'archivage automatique sur "terminé". La tâche validée
        // reste visible (section "Terminées"). L'archivage est manuel (archiver()).

        return redirect()->route('taches.show', $tache)
                         ->with('success', 'Tâche mise à jour.');
    }

    public function destroy(Tache $tache)
    {
        $this->authorizeAccess($tache);

        $tache->delete();

        return redirect()->route('taches.index')
                         ->with('success', 'Tâche supprimée.');
    }

    public function archives(Request $request)
    {
        $user  = Auth::user();
        $query = Tache::with(['responsables', 'site', 'createur', 'actionsSuivi.user'])
                      ->visiblePar($user)
                      ->historique();

        if ($request->filled('projet')) {
            $query->where('projet', $request->projet);
        }
        if ($request->filled('periode')) {
            [$debut, $fin] = $this->plageDate($request->periode);
            $query->whereBetween('updated_at', [$debut, $fin]);
        }
        if ($request->filled('responsable_id')) {
            $query->whereHas('responsables', fn($q) => $q->where('users.id', $request->responsable_id));
        }
        if ($request->filled('q')) {
            $query->where(fn($q) => $q->where('titre', 'like', "%{$request->q}%")
                                      ->orWhere('description', 'like', "%{$request->q}%"));
        }

        $taches   = $query->orderByDesc('updated_at')->get();
        $vue      = in_array($request->get('vue'), ['projet', 'periode', 'actions']) ? $request->get('vue') : 'projet';
        $projets  = Tache::historique()->visiblePar($user)->whereNotNull('projet')->distinct()->orderBy('projet')->pluck('projet');
        $membres  = User::where('type_compte', 'humain')->orderBy('nom')->get();

        // Stats header
        $total        = $taches->count();
        $ceMois       = $taches->filter(fn($t) => $t->updated_at && $t->updated_at->isCurrentMonth())->count();
        $topProjet    = $taches->whereNotNull('projet')->groupBy('projet')->sortByDesc(fn($g) => $g->count())->keys()->first();
        $topProjetNb  = $topProjet ? $taches->where('projet', $topProjet)->count() : 0;

        return view('taches.archives', compact('taches', 'vue', 'projets', 'membres', 'total', 'ceMois', 'topProjet', 'topProjetNb'));
    }

    private function plageDate(string $periode): array
    {
        return match($periode) {
            'semaine'   => [now()->startOfWeek(), now()->endOfWeek()],
            'mois'      => [now()->startOfMonth(), now()->endOfMonth()],
            'trimestre' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'annee'     => [now()->startOfYear(), now()->endOfYear()],
            default     => [now()->subYear(), now()],
        };
    }

    public function restaurer(Tache $tache)
    {
        $this->authorizeAccess($tache);
        $tache->update(['statut' => 'nouveau', 'archived_at' => null]);
        return redirect()->route('taches.archives')
                         ->with('success', 'Tâche restaurée.');
    }

    // Archivage manuel (remplace l'ancien archivage automatique sur "terminé")
    public function archiver(Tache $tache)
    {
        $this->authorizeAccess($tache);
        if (! $tache->archived_at) {
            $tache->update(['archived_at' => now()]);
        }
        return redirect()->route('taches.index')
                         ->with('success', 'Tâche archivée.');
    }

    // ── Patch statut (AJAX) ────────────────────────────────────────────────────
    public function patchStatut(Request $request, Tache $tache)
    {
        $request->validate(['statut' => 'required|in:nouveau,en_cours,en_attente,en_arret,termine']);
        $this->authorizeAccess($tache);

        $tache->update(['statut' => $request->statut]);

        // Plus d'archivage automatique : une tâche validée reste visible.

        // Notifier les responsables du changement de statut
        $auteur = Auth::user();
        foreach ($tache->responsables as $r) {
            if ($r->id !== $auteur->id) {
                $r->notify(new StatutTacheNotification($tache, $request->statut, $auteur->nom_complet));
            }
        }

        return response()->json(['ok' => true, 'statut' => $tache->statut]);
    }

    // ── Accès ─────────────────────────────────────────────────────────────────

    private function authorizeAccess(Tache $tache): void
    {
        $user = Auth::user();
        if ($user->isManager()) return;

        $peutVoir = $tache->createur_id === $user->id
            || $tache->responsables->contains('id', $user->id);

        abort_unless($peutVoir, 403, 'Accès refusé.');
    }
}

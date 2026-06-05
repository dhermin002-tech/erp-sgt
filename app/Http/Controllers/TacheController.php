<?php

namespace App\Http\Controllers;

use App\Http\Requests\TacheRequest;
use App\Models\Site;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TacheController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $query = Tache::with(['responsables', 'site', 'createur'])
                      ->visiblePar($user)
                      ->actives();

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

        $taches   = $query->paginate(15)->withQueryString();
        $sites    = Site::where('actif', true)->orderBy('nom')->get();
        $membres  = User::orderBy('nom')->get();
        $statuts  = ['nouveau', 'en_cours', 'en_attente', 'en_arret', 'termine'];

        return view('taches.index', compact('taches', 'sites', 'membres', 'statuts'));
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

        $tache->update($request->safe()->except('responsables'));
        $tache->responsables()->sync($request->responsables);

        // Archivage automatique si statut = termine
        if ($tache->statut === 'termine' && ! $tache->archived_at) {
            $tache->update(['archived_at' => now()]);
        }

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

    public function archives()
    {
        $user   = Auth::user();
        $taches = Tache::with(['responsables', 'site'])
                       ->visiblePar($user)
                       ->whereNotNull('archived_at')
                       ->orderByDesc('archived_at')
                       ->paginate(20);

        return view('taches.archives', compact('taches'));
    }

    public function restaurer(Tache $tache)
    {
        $tache->update(['statut' => 'nouveau', 'archived_at' => null]);
        return redirect()->route('taches.archives')
                         ->with('success', 'Tâche restaurée.');
    }

    // ── Patch statut (AJAX) ────────────────────────────────────────────────────
    public function patchStatut(Request $request, Tache $tache)
    {
        $request->validate(['statut' => 'required|in:nouveau,en_cours,en_attente,en_arret,termine']);
        $this->authorizeAccess($tache);

        $tache->update(['statut' => $request->statut]);

        if ($request->statut === 'termine' && ! $tache->archived_at) {
            $tache->update(['archived_at' => now()]);
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

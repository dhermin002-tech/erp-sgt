<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PageRapportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $queryActives = Tache::with(['responsables', 'site'])
            ->visiblePar($user)
            ->actives();

        if ($request->filled('statut')) {
            $queryActives->where('statut', $request->statut);
        }
        if ($request->filled('site_id')) {
            $queryActives->where('site_id', $request->site_id);
        }
        if ($user->isManager() && $request->filled('responsable_id')) {
            $queryActives->whereHas('responsables', fn($q) => $q->where('users.id', $request->responsable_id));
        }

        $tachesActives = $queryActives->orderBy('date_echeance')->get();

        $queryTerminees = Tache::with(['responsables', 'site'])
            ->visiblePar($user)
            ->where(fn($q) => $q->whereNotNull('archived_at')->orWhere('statut', 'termine'));

        if ($request->filled('site_id')) {
            $queryTerminees->where('site_id', $request->site_id);
        }

        $tachesTerminees = $queryTerminees->orderByDesc('updated_at')->get();

        if ($user->isManager()) {
            $membres = User::orderBy('nom')->get();
            $kpiResponsables = $membres->map(function ($membre) {
                $actives   = $membre->taches()->actives()->count();
                $terminees = $membre->taches()->where('statut', 'termine')->count();
                $total     = $actives + $terminees;
                return [
                    'user'      => $membre,
                    'actives'   => $actives,
                    'terminees' => $terminees,
                    'total'     => $total,
                    'taux'      => $total > 0 ? round($terminees / $total * 100) : 0,
                ];
            });
        } else {
            $actives   = $user->taches()->actives()->count();
            $terminees = $user->taches()->where('statut', 'termine')->count();
            $total     = $actives + $terminees;
            $kpiResponsables = collect([[
                'user'      => $user,
                'actives'   => $actives,
                'terminees' => $terminees,
                'total'     => $total,
                'taux'      => $total > 0 ? round($terminees / $total * 100) : 0,
            ]]);
        }

        $sites        = Site::where('actif', true)->orderBy('nom')->get();
        $responsables = $user->isManager() ? User::orderBy('nom')->get() : collect();
        $statuts      = ['nouveau', 'en_cours', 'en_attente', 'en_arret', 'termine'];

        return view('rapports.index', compact(
            'tachesActives', 'tachesTerminees', 'kpiResponsables',
            'sites', 'responsables', 'statuts'
        ));
    }
}

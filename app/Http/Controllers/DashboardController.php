<?php

namespace App\Http\Controllers;

use App\Models\Tache;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Filtres
        $periode      = $request->get('periode', '30');  // 7, 30, 90, tout
        $responsableId = $request->get('responsable_id');
        $siteId        = $request->get('site_id');

        $query = Tache::query()->visiblePar($user);

        if ($responsableId && $user->isManager()) {
            $query->whereHas('responsables', fn($q) => $q->where('users.id', $responsableId));
        }
        if ($siteId) {
            $query->where('site_id', $siteId);
        }
        if ($periode !== 'tout') {
            $query->where('created_at', '>=', now()->subDays((int) $periode));
        }

        $stats = [
            'total_actives'   => (clone $query)->actives()->count(),
            'en_cours'        => (clone $query)->where('statut', 'en_cours')->count(),
            'en_retard'       => (clone $query)->enRetard()->count(),
            'taux_completion' => $this->calculerTauxCompletion($query),
            'archivees_mois'  => (clone $query)->whereNotNull('archived_at')
                                               ->whereMonth('archived_at', now()->month)
                                               ->count(),
        ];

        $membres = $user->isManager() ? User::orderBy('nom')->get() : collect();
        $sites   = \App\Models\Site::where('actif', true)->orderBy('nom')->get();

        return view('dashboard', compact('stats', 'membres', 'sites', 'periode', 'responsableId', 'siteId'));
    }

    // ── API JSON pour Chart.js ─────────────────────────────────────────────────
    public function data(Request $request)
    {
        $user  = Auth::user();
        $query = Tache::query()->visiblePar($user);

        $periode       = $request->get('periode', '30');
        $responsableId = $request->get('responsable_id');
        $siteId        = $request->get('site_id');

        if ($responsableId && $user->isManager()) {
            $query->whereHas('responsables', fn($q) => $q->where('users.id', $responsableId));
        }
        if ($siteId) {
            $query->where('site_id', $siteId);
        }
        if ($periode !== 'tout') {
            $query->where('created_at', '>=', now()->subDays((int) $periode));
        }

        return response()->json([
            'donut'     => $this->dataDonutStatuts($query),
            'courbe'    => $this->dataCourbeTemps($user, $periode, $responsableId, $siteId),
            'barres'    => $this->dataBarresResponsables($user, $periode, $siteId),
        ]);
    }

    // ── Donut : répartition par statut ────────────────────────────────────────
    private function dataDonutStatuts($query): array
    {
        $counts = (clone $query)
            ->select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->pluck('total', 'statut')
            ->toArray();

        $statuts = ['nouveau', 'en_cours', 'en_attente', 'en_arret', 'termine'];
        $couleurs = [
            'nouveau'    => '#64748B',
            'en_cours'   => '#2563EB',
            'en_attente' => '#C97A0A',
            'en_arret'   => '#B0202E',
            'termine'    => '#15885A',
        ];
        $libelles = [
            'nouveau'    => 'Nouveau',
            'en_cours'   => 'En cours',
            'en_attente' => 'En attente',
            'en_arret'   => 'En arrêt',
            'termine'    => 'Terminé',
        ];

        return [
            'labels'          => array_map(fn($s) => $libelles[$s], $statuts),
            'data'            => array_map(fn($s) => $counts[$s] ?? 0, $statuts),
            'backgroundColor' => array_map(fn($s) => $couleurs[$s], $statuts),
        ];
    }

    // ── Courbe : tâches terminées vs créées sur 30 jours ─────────────────────
    private function dataCourbeTemps($user, string $periode, ?string $responsableId, ?string $siteId): array
    {
        $jours = $periode === 'tout' ? 30 : min((int) $periode, 90);
        $debut = now()->subDays($jours - 1)->startOfDay();

        $baseQuery = Tache::query()->visiblePar($user);
        if ($responsableId && $user->isManager()) {
            $baseQuery->whereHas('responsables', fn($q) => $q->where('users.id', $responsableId));
        }
        if ($siteId) {
            $baseQuery->where('site_id', $siteId);
        }

        $creees = (clone $baseQuery)
            ->where('created_at', '>=', $debut)
            ->select(DB::raw('DATE(created_at) as jour'), DB::raw('count(*) as n'))
            ->groupBy('jour')
            ->pluck('n', 'jour')
            ->toArray();

        $terminees = (clone $baseQuery)
            ->where('statut', 'termine')
            ->whereNotNull('archived_at')
            ->where('archived_at', '>=', $debut)
            ->select(DB::raw('DATE(archived_at) as jour'), DB::raw('count(*) as n'))
            ->groupBy('jour')
            ->pluck('n', 'jour')
            ->toArray();

        $labels   = [];
        $dataC    = [];
        $dataT    = [];
        for ($i = $jours - 1; $i >= 0; $i--) {
            $date    = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('d/m');
            $dataC[]  = $creees[$date] ?? 0;
            $dataT[]  = $terminees[$date] ?? 0;
        }

        return compact('labels', 'dataC', 'dataT');
    }

    // ── Barres : charge par responsable ──────────────────────────────────────
    private function dataBarresResponsables($user, string $periode, ?string $siteId): array
    {
        if (! $user->isManager()) {
            return ['labels' => [$user->nom_complet], 'data' => [
                Tache::query()->visiblePar($user)->actives()->count()
            ]];
        }

        $query = DB::table('tache_user')
            ->join('taches', 'taches.id', '=', 'tache_user.tache_id')
            ->join('users', 'users.id', '=', 'tache_user.user_id')
            ->whereNull('taches.deleted_at')
            ->whereNull('taches.archived_at')
            ->where('taches.statut', '!=', 'termine');

        if ($siteId) {
            $query->where('taches.site_id', $siteId);
        }
        if ($periode !== 'tout') {
            $query->where('taches.created_at', '>=', now()->subDays((int) $periode));
        }

        $nomExpr = config('database.default') === 'sqlite'
            ? DB::raw("(users.prenom || ' ' || users.nom) as nom")
            : DB::raw("CONCAT(users.prenom, ' ', users.nom) as nom");

        $data = $query->select(
                $nomExpr,
                DB::raw('count(*) as total')
            )
            ->groupBy('tache_user.user_id', 'users.prenom', 'users.nom')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return [
            'labels' => $data->pluck('nom')->toArray(),
            'data'   => $data->pluck('total')->toArray(),
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function calculerTauxCompletion($query): int
    {
        $total    = (clone $query)->count();
        $termines = (clone $query)->where('statut', 'termine')->count();
        return $total > 0 ? (int) round(($termines / $total) * 100) : 0;
    }
}

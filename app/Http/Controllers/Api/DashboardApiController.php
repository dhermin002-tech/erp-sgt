<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    public function kpis(Request $request): JsonResponse
    {
        $maintenant = now();
        $debut = $maintenant->copy()->startOfMonth();
        $fin = $debut->copy()->addMonth();

        $kpis = Tache::query()->visiblePar($request->user())->toBase()->selectRaw(
            'SUM(CASE WHEN archived_at IS NULL THEN 1 ELSE 0 END) AS total,
             SUM(CASE WHEN archived_at IS NULL AND statut = ? THEN 1 ELSE 0 END) AS terminees,
             SUM(CASE WHEN archived_at IS NULL AND statut = ? THEN 1 ELSE 0 END) AS en_cours,
             SUM(CASE WHEN archived_at IS NULL AND statut <> ? AND date_echeance < ? THEN 1 ELSE 0 END) AS en_retard,
             SUM(CASE WHEN archived_at >= ? AND archived_at < ? THEN 1 ELSE 0 END) AS archivees',
            ['termine', 'en_cours', 'termine', $maintenant->toDateString(), $debut, $fin],
        )->first();

        $total = (int) $kpis->total;
        $terminees = (int) $kpis->terminees;

        return response()->json(['data' => [
            'taches_actives' => $total - $terminees,
            'en_cours' => (int) $kpis->en_cours,
            'en_retard' => (int) $kpis->en_retard,
            'taux_completion' => $total ? (int) round(100 * $terminees / $total) : 0,
            'archivees_ce_mois' => (int) $kpis->archivees,
        ]]);
    }
}

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
        $user  = $request->user();
        $query = Tache::query()->visiblePar($user)->whereNull('archived_at');

        $total    = (clone $query)->count();
        $termines = (clone $query)->where('statut', 'termine')->count();

        return response()->json([
            'data' => [
                'taches_actives'   => (clone $query)->actives()->count(),
                'en_cours'         => (clone $query)->where('statut', 'en_cours')->count(),
                'en_retard'        => (clone $query)->enRetard()->count(),
                'taux_completion'  => $total > 0 ? (int) round(($termines / $total) * 100) : 0,
                'archivees_ce_mois'=> Tache::query()->visiblePar($user)
                    ->whereNotNull('archived_at')
                    ->whereMonth('archived_at', now()->month)
                    ->count(),
            ],
        ]);
    }
}

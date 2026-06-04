<?php

namespace App\Http\Controllers;

use App\Models\Tache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Tache::query()->visiblePar($user);

        $stats = [
            'total_actives'  => (clone $query)->actives()->count(),
            'en_cours'       => (clone $query)->where('statut', 'en_cours')->count(),
            'en_retard'      => (clone $query)->enRetard()->count(),
            'taux_completion' => $this->calculerTauxCompletion($user),
        ];

        return view('dashboard', compact('stats'));
    }

    private function calculerTauxCompletion($user): int
    {
        $query = Tache::query()->visiblePar($user);
        $total    = (clone $query)->count();
        $termines = (clone $query)->where('statut', 'termine')->count();
        return $total > 0 ? (int) round(($termines / $total) * 100) : 0;
    }
}

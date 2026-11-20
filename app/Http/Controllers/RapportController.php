<?php

namespace App\Http\Controllers;

use App\Models\Rapport;
use App\Models\Tache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RapportController extends Controller
{
    public function listeApi(Request $request, Tache $tache)
    {
        abort_unless($request->user()->canAccessTache($tache), 403);

        return response()->json($tache->rapports()->with('user:id,prenom,nom')
            ->paginate(20)->through(fn (Rapport $rapport) => $this->donneesApi($rapport)));
    }

    public function store(Request $request, Tache $tache)
    {
        abort_unless(Auth::user()->canAccessTache($tache), 403);

        $request->validate([
            'contenu'           => 'required|string|max:5000',
            'date_intervention' => 'nullable|date',
        ], [
            'contenu.required' => 'Le compte-rendu ne peut pas être vide.',
        ]);

        $rapport = $tache->rapports()->create([
            'user_id'           => Auth::id(),
            'contenu'           => $request->contenu,
            'date_intervention' => $request->date_intervention,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['data' => $this->donneesApi($rapport->setRelation('user', $request->user()))], 201);
        }

        return back()->with('success', 'Rapport ajouté.');
    }

    private function donneesApi(Rapport $rapport): array
    {
        return [
            'id' => $rapport->id,
            'contenu' => $rapport->contenu,
            'auteur' => $rapport->user?->nom_complet,
            'date_intervention' => $rapport->date_intervention?->toDateString(),
            'created_at' => $rapport->created_at->toISOString(),
        ];
    }

    public function destroy(Rapport $rapport)
    {
        $user = Auth::user();
        abort_unless($user->isManager() || $rapport->user_id === $user->id, 403);
        $rapport->delete();
        return back()->with('success', 'Rapport supprimé.');
    }
}

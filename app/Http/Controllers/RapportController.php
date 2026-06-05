<?php

namespace App\Http\Controllers;

use App\Models\Rapport;
use App\Models\Tache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RapportController extends Controller
{
    public function store(Request $request, Tache $tache)
    {
        $request->validate([
            'contenu'           => 'required|string|max:5000',
            'date_intervention' => 'nullable|date',
        ], [
            'contenu.required' => 'Le compte-rendu ne peut pas être vide.',
        ]);

        $tache->rapports()->create([
            'user_id'           => Auth::id(),
            'contenu'           => $request->contenu,
            'date_intervention' => $request->date_intervention,
        ]);

        return back()->with('success', 'Rapport ajouté.');
    }

    public function destroy(Rapport $rapport)
    {
        $user = Auth::user();
        abort_unless($user->isManager() || $rapport->user_id === $user->id, 403);
        $rapport->delete();
        return back()->with('success', 'Rapport supprimé.');
    }
}

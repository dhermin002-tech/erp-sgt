<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SousTache;
use App\Models\Tache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SousTacheApiController extends Controller
{
    public function store(Request $request, Tache $tache): JsonResponse
    {
        abort_unless(Auth::user()->canAccessTache($tache), 403);

        $request->validate(['titre' => 'required|string|max:255']);

        $ordre = $tache->sousTaches()->max('ordre') + 1;
        $st    = $tache->sousTaches()->create([
            'titre' => $request->titre,
            'ordre' => $ordre,
        ]);

        return response()->json([
            'message' => 'Sous-tâche créée.',
            'data'    => ['id' => $st->id, 'titre' => $st->titre, 'termine' => false],
        ], 201);
    }

    public function toggle(Request $request, SousTache $sousTache): JsonResponse
    {
        abort_unless(Auth::user()->canAccessTache($sousTache->tache), 403);

        // Vrai toggle : bascule l'état si 'termine' n'est pas fourni,
        // sinon applique la valeur explicite (compatibilité interface web + MCP).
        $nouvelEtat = $request->has('termine')
            ? $request->boolean('termine')
            : ! $sousTache->termine;

        $sousTache->update(['termine' => $nouvelEtat]);
        $tache = $sousTache->tache;
        $tache->recalculerProgression();

        return response()->json([
            'ok'          => true,
            'termine'     => $sousTache->termine,
            'progression' => $tache->fresh()->progression,
        ]);
    }

    public function destroy(SousTache $sousTache): JsonResponse
    {
        $tache = $sousTache->tache;
        abort_unless(Auth::user()->canAccessTache($tache), 403);

        $sousTache->delete();
        $tache->recalculerProgression();

        return response()->json(['ok' => true, 'progression' => $tache->fresh()->progression]);
    }
}

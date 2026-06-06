<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SousTache;
use App\Models\Tache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SousTacheApiController extends Controller
{
    public function store(Request $request, Tache $tache): JsonResponse
    {
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
        $request->validate(['termine' => 'required|boolean']);

        $sousTache->update(['termine' => $request->termine]);
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
        $sousTache->delete();
        $tache->recalculerProgression();

        return response()->json(['ok' => true, 'progression' => $tache->fresh()->progression]);
    }
}

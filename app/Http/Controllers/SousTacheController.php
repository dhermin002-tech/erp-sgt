<?php

namespace App\Http\Controllers;

use App\Models\SousTache;
use App\Models\Tache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SousTacheController extends Controller
{
    public function store(Request $request, Tache $tache)
    {
        $request->validate(['titre' => 'required|string|max:255']);

        $ordre = $tache->sousTaches()->max('ordre') + 1;

        $st = $tache->sousTaches()->create([
            'titre' => $request->titre,
            'ordre' => $ordre,
        ]);

        return response()->json(['id' => $st->id, 'titre' => $st->titre]);
    }

    public function toggle(Request $request, SousTache $sousTache)
    {
        $request->validate(['termine' => 'required|boolean']);

        $sousTache->update(['termine' => $request->termine]);

        $tache = $sousTache->tache;
        $tache->recalculerProgression();

        return response()->json(['ok' => true, 'progression' => $tache->fresh()->progression]);
    }

    public function destroy(SousTache $sousTache)
    {
        $tache = $sousTache->tache;
        $sousTache->delete();
        $tache->recalculerProgression();

        return response()->json(['ok' => true, 'progression' => $tache->fresh()->progression]);
    }
}

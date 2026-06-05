<?php

namespace App\Http\Controllers;

use App\Models\ActionSuivi;
use App\Models\Tache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActionSuiviController extends Controller
{
    public function store(Request $request, Tache $tache)
    {
        $request->validate(['description' => 'required|string|max:500']);

        $action = $tache->actionsSuivi()->create([
            'user_id'     => Auth::id(),
            'description' => $request->description,
            'fait'        => false,
        ]);

        return response()->json(['id' => $action->id, 'description' => $action->description]);
    }

    public function toggle(Request $request, ActionSuivi $actionSuivi)
    {
        $request->validate(['fait' => 'required|boolean']);
        $actionSuivi->update(['fait' => $request->fait]);
        return response()->json(['ok' => true, 'fait' => $actionSuivi->fait]);
    }

    public function destroy(ActionSuivi $actionSuivi)
    {
        $user = Auth::user();
        abort_unless($user->isManager() || $actionSuivi->user_id === $user->id, 403);
        $actionSuivi->delete();
        return response()->json(['ok' => true]);
    }
}

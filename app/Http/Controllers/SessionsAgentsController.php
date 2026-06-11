<?php

namespace App\Http\Controllers;

use App\Models\SessionAgent;
use App\Models\User;
use Illuminate\Http\Request;

class SessionsAgentsController extends Controller
{
    public function index(Request $request)
    {
        $query = SessionAgent::with('user')->orderByDesc('demarree_a');

        if ($request->filled('agent_id')) {
            $query->where('user_id', $request->agent_id);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('projet')) {
            $query->where('projet', 'like', '%' . $request->projet . '%');
        }

        $sessions = $query->paginate(25)->withQueryString();
        $agents   = User::where('type_compte', 'agent_ia')->orderBy('nom')->get();

        $actives  = SessionAgent::with('user')->where('statut', 'en_cours')
                        ->orderBy('demarree_a')->get();

        return view('agents.sessions', compact('sessions', 'agents', 'actives'));
    }
}

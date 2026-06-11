<?php

namespace App\Http\Controllers;

use App\Models\RapportAgent;
use App\Models\User;
use Illuminate\Http\Request;

class RapportsAgentsController extends Controller
{
    public function index(Request $request)
    {
        $query = RapportAgent::with('user')->orderByDesc('created_at');

        if ($request->filled('agent_id')) {
            $query->where('user_id', $request->agent_id);
        }
        if ($request->filled('projet')) {
            $query->where('projet', $request->projet);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $rapports = $query->paginate(20)->withQueryString();
        $agents   = User::where('type_compte', 'agent_ia')->orderBy('nom')->get();
        $projets  = RapportAgent::distinct()->orderBy('projet')->pluck('projet');

        return view('agents.rapports', compact('rapports', 'agents', 'projets'));
    }
}

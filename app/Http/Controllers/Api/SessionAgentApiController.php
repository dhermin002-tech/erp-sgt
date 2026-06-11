<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SessionAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionAgentApiController extends Controller
{
    /** Démarrer une session de travail pour l'agent connecté. */
    public function start(Request $request): JsonResponse
    {
        $agent = $request->user();

        // Clôturer toute session en cours (idempotence — crash résolu)
        SessionAgent::where('user_id', $agent->id)
            ->where('statut', 'en_cours')
            ->update(['statut' => 'interrompue', 'terminee_a' => now()]);

        $data = $request->validate([
            'projet'   => 'required|string|max:100',
            'contexte' => 'nullable|string|max:500',
        ]);

        $session = SessionAgent::create([
            'user_id'    => $agent->id,
            'projet'     => $data['projet'],
            'contexte'   => $data['contexte'] ?? null,
            'demarree_a' => now(),
            'statut'     => 'en_cours',
        ]);

        return response()->json([
            'message'    => 'Session démarrée.',
            'session_id' => $session->id,
            'demarree_a' => $session->demarree_a->toIso8601String(),
        ], 201);
    }

    /** Clôturer la session active de l'agent. */
    public function end(Request $request): JsonResponse
    {
        $agent = $request->user();

        $session = SessionAgent::where('user_id', $agent->id)
            ->where('statut', 'en_cours')
            ->latest()
            ->first();

        if (! $session) {
            return response()->json(['message' => 'Aucune session active.'], 404);
        }

        $data = $request->validate([
            'resume' => 'nullable|array',
        ]);

        $session->update([
            'statut'     => 'terminee',
            'terminee_a' => now(),
            'resume'     => $data['resume'] ?? null,
        ]);

        return response()->json([
            'message'    => 'Session clôturée.',
            'session_id' => $session->id,
            'duree'      => $session->duree,
        ]);
    }

    /** Sessions actives (supervision manager). */
    public function actives(): JsonResponse
    {
        $sessions = SessionAgent::with('user')
            ->where('statut', 'en_cours')
            ->orderBy('demarree_a')
            ->get();

        return response()->json([
            'data' => $sessions->map(fn($s) => [
                'session_id' => $s->id,
                'agent'      => $s->user?->nom_complet,
                'agent_code' => $s->user?->agent_code,
                'projet'     => $s->projet,
                'contexte'   => $s->contexte,
                'demarree_a' => $s->demarree_a?->toIso8601String(),
                'duree'      => $s->duree,
            ]),
        ]);
    }
}

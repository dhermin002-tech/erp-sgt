<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RapportAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RapportAgentApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = RapportAgent::with('user')
            ->orderByDesc('created_at');

        if ($request->filled('projet')) {
            $query->where('projet', $request->projet);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->boolean('aujourd_hui')) {
            $query->aujourdhui();
        }
        if ($request->boolean('par_agent')) {
            $query->parAgent();
        }

        $rapports = $query->paginate(20);

        return response()->json([
            'data' => $rapports->map(fn($r) => $this->formatRapport($r)),
            'meta' => [
                'total'        => $rapports->total(),
                'current_page' => $rapports->currentPage(),
                'last_page'    => $rapports->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->user()->tokenCan('rapports:create')
            || abort(403, 'Token sans permission rapports:create.');

        $data = $request->validate([
            'projet'  => 'required|string|max:100',
            'type'    => 'required|in:session,sprint,bug,deploiement,audit,quotidien',
            'titre'   => 'required|string|max:250',
            'contenu' => 'required|string|max:50000',
            'statut'  => 'in:info,warning,erreur',
            'meta'    => 'nullable|array',
        ]);

        $rapport = RapportAgent::create([
            ...$data,
            'user_id' => $request->user()->id,
            'statut'  => $data['statut'] ?? 'info',
        ]);

        // Sauvegarde du fichier .md dans le projet local (si chemin configuré)
        $this->sauvegarderFichierMd($rapport);

        return response()->json([
            'message' => 'Rapport publié.',
            'data'    => $this->formatRapport($rapport->load('user')),
        ], 201);
    }

    public function show(RapportAgent $rapportAgent): JsonResponse
    {
        return response()->json(['data' => $this->formatRapport($rapportAgent->load('user'))]);
    }

    private function sauvegarderFichierMd(RapportAgent $rapport): void
    {
        $agentCode = $rapport->user?->agent_code ?? 'agent';
        $filename  = now()->format('Y-m-d\TH-i-s') . '.md';
        $path      = "rapports-agents/{$agentCode}/{$rapport->projet}/{$filename}";

        $contenu = "---\nprojet: {$rapport->projet}\ntype: {$rapport->type}\nstatut: {$rapport->statut}\ncreated_at: {$rapport->created_at}\n---\n\n# {$rapport->titre}\n\n{$rapport->contenu}";

        Storage::put($path, $contenu);
        $rapport->update(['fichier_md' => storage_path("app/{$path}")]);
    }

    private function formatRapport(RapportAgent $r): array
    {
        return [
            'id'         => $r->id,
            'projet'     => $r->projet,
            'type'       => $r->type,
            'titre'      => $r->titre,
            'contenu'    => $r->contenu,
            'statut'     => $r->statut,
            'meta'       => $r->meta,
            'fichier_md' => $r->fichier_md,
            'agent'      => $r->user ? [
                'id'         => $r->user->id,
                'nom'        => $r->user->nom_complet,
                'agent_code' => $r->user->agent_code,
            ] : null,
            'created_at' => $r->created_at?->toIso8601String(),
        ];
    }
}

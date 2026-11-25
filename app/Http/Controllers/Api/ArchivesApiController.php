<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TacheApiResource;
use App\Models\Tache;
use Illuminate\Http\Request;

class ArchivesApiController extends Controller
{
    public function index(Request $request)
    {
        $filtres = $request->validate([
            'projet' => 'sometimes|string|max:40',
            'responsable_id' => 'sometimes|integer|min:1',
            'q' => 'sometimes|string|max:255',
        ]);

        return TacheApiResource::collection(Tache::historique()->visiblePar($request->user())
            ->with(['responsables', 'site', 'createur'])
            ->when($filtres['projet'] ?? null, fn ($query, $projet) => $query->where('projet', $projet))
            ->when($filtres['responsable_id'] ?? null, fn ($query, $id) => $query
                ->whereHas('responsables', fn ($responsables) => $responsables->whereKey($id)))
            ->when(isset($filtres['q']), fn ($query) => $query->where(fn ($recherche) => $recherche
                ->where('titre', 'like', '%'.$filtres['q'].'%')
                ->orWhere('description', 'like', '%'.$filtres['q'].'%')))
            ->latest('updated_at')->orderByDesc('id')->paginate(20)->withQueryString());
    }

    public function archiver(Request $request, Tache $tache)
    {
        abort_unless($request->user()->canAccessTache($tache), 403);
        $tache->newQuery()->whereKey($tache->id)->whereNull('archived_at')
            ->update(['archived_at' => now()]);

        return response()->noContent();
    }

    public function restaurer(Request $request, Tache $tache)
    {
        abort_unless($request->user()->canAccessTache($tache), 403);
        $tache->update(['statut' => 'nouveau', 'archived_at' => null]);

        return response()->noContent();
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentaireApiRequest;
use App\Http\Resources\CommentaireApiResource;
use App\Models\Commentaire;
use App\Models\Tache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class CommentaireApiController extends Controller
{
    public function index(Request $request, Tache $tache): AnonymousResourceCollection
    {
        abort_unless($request->user()->canAccessTache($tache), 403, 'Accès refusé.');

        return CommentaireApiResource::collection(
            $tache->commentaires()->with('user')->orderBy('id')->paginate(20)
        );
    }

    public function store(CommentaireApiRequest $request, Tache $tache): JsonResponse
    {
        $commentaire = $tache->commentaires()->create([
            'user_id' => $request->user()->id,
            'contenu' => $request->validated('contenu'),
        ]);

        return (new CommentaireApiResource($commentaire->setRelation('user', $request->user())))
            ->response()->setStatusCode(201);
    }

    public function destroy(Request $request, Commentaire $commentaire): Response
    {
        $user = $request->user();
        abort_unless($user->isManager() || $commentaire->user_id === $user->id, 403, 'Action non autorisée.');

        if ($commentaire->photo_path) {
            Storage::disk('public')->delete($commentaire->photo_path);
        }
        $commentaire->delete();

        return response()->noContent();
    }
}

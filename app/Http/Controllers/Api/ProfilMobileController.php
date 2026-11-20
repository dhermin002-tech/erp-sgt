<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppareilPush;
use App\Models\AutorisationOAuthSgt;
use App\Services\OAuthSgt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProfilMobileController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $id = $request->user()->id;
        $appareils = AppareilPush::where('user_id', $id)->latest()->get(['id', 'plateforme', 'dernier_usage']);
        $applications = AutorisationOAuthSgt::where('user_id', $id)
            ->whereNotNull('jeton_id')->where('expire_le', '>', now())->with('client:id,nom')->latest()->get()
            ->map(fn (AutorisationOAuthSgt $autorisation) => [
                'id' => $autorisation->id,
                'nom' => $autorisation->client->nom,
            ]);

        return response()->json(['data' => compact('appareils', 'applications')])->header('Cache-Control', 'no-store');
    }

    public function revoquer(Request $request, int $autorisation, OAuthSgt $oauth): Response
    {
        $oauth->revoquerApplication($request->user(), $autorisation);

        return response()->noContent();
    }
}

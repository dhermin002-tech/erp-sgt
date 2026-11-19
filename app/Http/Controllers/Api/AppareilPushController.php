<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AppareilPushRequest;
use App\Models\AppareilPush;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AppareilPushController extends Controller
{
    public function store(AppareilPushRequest $request): JsonResponse
    {
        $plateforme = $request->validated('plateforme');
        $destination = $plateforme === 'android'
            ? ['jeton' => $request->validated('jeton')]
            : $request->validated('abonnement');
        $empreinte = hash('sha256', $plateforme.'|'.($destination['jeton'] ?? $destination['endpoint']));

        $appareil = AppareilPush::firstOrCreate(['empreinte' => $empreinte], [
            'plateforme' => $plateforme,
            'user_id' => $request->user()->id,
            'destination' => $destination,
            'dernier_usage' => now(),
        ]);
        abort_unless($appareil->user_id === $request->user()->id, 409, 'Appareil déjà associé à un compte.');

        $nouveau = $appareil->wasRecentlyCreated;
        if (! $nouveau) {
            $appareil->update(['destination' => $destination, 'dernier_usage' => now()]);
        }

        return response()->json(['data' => $appareil->only('id', 'plateforme', 'dernier_usage')], $nouveau ? 201 : 200);
    }

    public function retirer(Request $request): Response
    {
        $id = $request->validate(['id' => ['required', 'integer', 'min:1']])['id'];

        return $this->destroy($request, AppareilPush::findOrFail($id));
    }

    public function destroy(Request $request, AppareilPush $appareil): Response
    {
        abort_unless($appareil->user_id === $request->user()->id, 404);
        $appareil->delete();

        return response()->noContent();
    }
}

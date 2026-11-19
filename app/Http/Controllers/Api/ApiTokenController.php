<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenController extends Controller
{
    public function issue(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'client' => 'sometimes|in:mcp,mobile',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (! $user || $user->isAgentIa() || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Identifiants incorrects.'],
            ]);
        }

        $mobile = $request->input('client') === 'mobile';
        $token = $user->createToken(
            $mobile ? 'mobile-sgt' : 'mcp-sgt',
            $mobile ? ['mobile'] : ['mcp:read', 'mcp:write'],
        )->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'         => $user->id,
                'nom_complet'=> $user->nom_complet,
                'role'       => $user->role,
            ],
        ], 201);
    }

    public function revoke(Request $request): JsonResponse
    {
        $jeton = $request->user()->currentAccessToken();
        abort_unless($jeton instanceof PersonalAccessToken, 403, 'Un jeton personnel est requis.');
        $jeton->delete();

        return response()->json(['message' => 'Token révoqué.'], 200);
    }
}

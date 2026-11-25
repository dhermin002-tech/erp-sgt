<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class CompteApiController extends Controller
{
    public function profil(Request $request)
    {
        return response()->json(['data' => $request->user()->only([
            'id', 'username', 'nom', 'prenom', 'telephone', 'role',
        ])]);
    }

    public function modifierProfil(Request $request)
    {
        $request->user()->update($request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:30',
        ]));

        return $this->profil($request);
    }

    public function modifierMotDePasse(Request $request)
    {
        $donnees = $request->validate([
            'password_actuel' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        $jeton = $request->user()->currentAccessToken();

        DB::transaction(function () use ($request, $donnees, $jeton) {
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->id);
            if (! Hash::check($donnees['password_actuel'], $user->password)) {
                throw ValidationException::withMessages(['password_actuel' => 'Mot de passe actuel incorrect.']);
            }
            $user->update(['password' => Hash::make($donnees['password'])]);
            $user->tokens()->when($jeton instanceof PersonalAccessToken,
                fn ($query) => $query->whereKeyNot($jeton->id))->delete();

            if ($request->hasSession() && Auth::guard('web')->check()) {
                $request->session()->put('password_hash_web', $user->password);
            }
        });

        return response()->noContent();
    }

    public function preferences(Request $request)
    {
        return response()->json(['data' => [
            'direction_ui' => $request->user()->direction_ui ?? 'A',
            'locale' => $request->user()->locale
                ?? ($request->hasSession() ? $request->session()->get('locale', 'fr') : 'fr'),
        ]]);
    }

    public function modifierPreferences(Request $request)
    {
        $donnees = $request->validate([
            'direction_ui' => 'required|in:A,B',
            'locale' => 'required|in:fr,en',
        ]);
        $request->user()->update($donnees);
        if ($request->hasSession()) {
            $request->session()->put('locale', $donnees['locale']);
        }

        return $this->preferences($request);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AutorisationOAuthSgt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Rules\MotDePasseSgt;

class ProfilController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $applicationsConnectees = AutorisationOAuthSgt::query()->where('user_id', $user->id)
            ->whereNotNull('jeton_id')->where('expire_le', '>', now())->with('client')->latest()->get();

        return view('profil.index', compact('user', 'applicationsConnectees'));
    }

    public function updateInfos(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'nom'       => 'required|string|max:100',
            'prenom'    => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:30',
        ]);

        $user->update($request->only('nom', 'prenom', 'telephone'));

        return back()->with('success_infos', 'Informations mises à jour.');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'password_actuel'   => 'required|string',
            'password'          => ['bail', 'required', 'string', 'confirmed', 'different:password_actuel', new MotDePasseSgt],
        ], [
            'password_actuel.required' => 'Le mot de passe actuel est obligatoire.',
            'password.confirmed'       => 'Les nouveaux mots de passe ne correspondent pas.',
        ]);

        $modifie = DB::transaction(function () use ($request, $user): bool {
            $compte = User::query()->lockForUpdate()->findOrFail($user->id);
            if (! Hash::check($request->password_actuel, $compte->password)) {
                return false;
            }

            $compte->update(['password' => Hash::make($request->password)]);
            $compte->tokens()->delete();

            return true;
        });
        if (! $modifie) {
            return back()->withErrors(['password_actuel' => 'Mot de passe actuel incorrect.'])->with('tab', 'password');
        }

        $user->refresh();

        // Invalide toutes les autres sessions de cet utilisateur (sécurité : si le compte
        // était compromis, l'attaquant est déconnecté immédiatement). Requiert le middleware
        // AuthenticateSession (cf. bootstrap/app.php).
        auth()->logoutOtherDevices($request->password);
        $request->session()->regenerate();

        return back()->with('success_password', 'Mot de passe modifié avec succès.')->with('tab', 'password');
    }

    public function updatePreferences(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'direction_ui' => 'required|in:A,B',
            'locale'       => 'required|in:fr,en',
        ]);

        $user->update(['direction_ui' => $request->direction_ui, 'locale' => $request->locale]);

        // Locale via session (même logique que PreferenceController)
        session(['locale' => $request->locale]);
        app()->setLocale($request->locale);

        return back()->with('success_prefs', 'Préférences enregistrées.')->with('tab', 'preferences');
    }
}

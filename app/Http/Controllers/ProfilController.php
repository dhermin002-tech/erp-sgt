<?php

namespace App\Http\Controllers;

use App\Models\AutorisationOAuthSgt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

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
            'password'          => ['bail', 'required', 'string', 'confirmed', 'different:password_actuel', 'max:72', Password::min(8),
                function (string $champ, string $valeur, \Closure $refuser): void {
                    if (strlen($valeur) > 72) {
                        $refuser('Le mot de passe ne doit pas dépasser 72 octets.');
                    }
                },],
        ], [
            'password_actuel.required' => 'Le mot de passe actuel est obligatoire.',
            'password.confirmed'       => 'Les nouveaux mots de passe ne correspondent pas.',
            'password.min'             => 'Le nouveau mot de passe doit faire au moins 8 caractères.',
        ]);

        if (! Hash::check($request->password_actuel, $user->password)) {
            return back()->withErrors(['password_actuel' => 'Mot de passe actuel incorrect.'])->with('tab', 'password');
        }

        $user->update(['password' => Hash::make($request->password)]);

        // Invalide toutes les autres sessions de cet utilisateur (sécurité : si le compte
        // était compromis, l'attaquant est déconnecté immédiatement). Requiert le middleware
        // AuthenticateSession (cf. bootstrap/app.php).
        auth()->logoutOtherDevices($request->password);

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

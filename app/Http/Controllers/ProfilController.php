<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfilController extends Controller
{
    public function index()
    {
        return view('profil.index', ['user' => auth()->user()]);
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
            'password'          => ['required', 'confirmed', Password::min(8)],
        ], [
            'password_actuel.required' => 'Le mot de passe actuel est obligatoire.',
            'password.confirmed'       => 'Les nouveaux mots de passe ne correspondent pas.',
            'password.min'             => 'Le nouveau mot de passe doit faire au moins 8 caractères.',
        ]);

        if (! Hash::check($request->password_actuel, $user->password)) {
            return back()->withErrors(['password_actuel' => 'Mot de passe actuel incorrect.'])->with('tab', 'password');
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success_password', 'Mot de passe modifié avec succès.')->with('tab', 'password');
    }

    public function updatePreferences(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'direction_ui' => 'required|in:A,B',
            'locale'       => 'required|in:fr,en',
        ]);

        $user->update(['direction_ui' => $request->direction_ui]);

        // Locale via session (même logique que PreferenceController)
        session(['locale' => $request->locale]);
        app()->setLocale($request->locale);

        return back()->with('success_prefs', 'Préférences enregistrées.')->with('tab', 'preferences');
    }
}

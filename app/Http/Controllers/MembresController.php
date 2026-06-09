<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MembresController extends Controller
{
    public function index()
    {
        $membres = User::orderBy('role')->orderBy('nom')->paginate(20);
        return view('membres.index', compact('membres'));
    }

    public function create()
    {
        return view('membres.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'nom'      => 'required|string|max:100',
            'prenom'   => 'nullable|string|max:100',
            'role'     => 'required|in:manager,technicien,agent,developpeur,stagiaire',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'username.unique'   => 'Cet identifiant est déjà utilisé.',
            'password.min'      => 'Le mot de passe doit faire au moins 8 caractères.',
            'password.confirmed'=> 'Les mots de passe ne correspondent pas.',
        ]);

        User::create([
            'username' => $request->username,
            'nom'      => $request->nom,
            'prenom'   => $request->prenom,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('membres.index')->with('success', 'Membre ajouté avec succès.');
    }

    public function edit(User $membre)
    {
        return view('membres.edit', compact('membre'));
    }

    public function update(Request $request, User $membre)
    {
        $request->validate([
            'nom'    => 'required|string|max:100',
            'prenom' => 'nullable|string|max:100',
            'role'   => 'required|in:manager,technicien,agent,developpeur,stagiaire',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = $request->only('nom', 'prenom', 'role');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $membre->update($data);

        return redirect()->route('membres.index')->with('success', 'Membre mis à jour.');
    }

    public function destroy(User $membre)
    {
        abort_if($membre->id === auth()->id(), 403, 'Vous ne pouvez pas vous supprimer vous-même.');
        $membre->delete();
        return redirect()->route('membres.index')->with('success', 'Membre supprimé.');
    }
}

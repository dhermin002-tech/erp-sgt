<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MembresController extends Controller
{
    public function index()
    {
        $humains = User::where('type_compte', 'humain')
            ->orderBy('nom')
            ->get()
            ->sortBy(fn($u) => [$u->rangHierarchique(), $u->nom])
            ->values();

        // Agents IA classés par rôle métier (le-doyen > expert > project > dev > qa > design > audit).
        // Eager-load des sessions en cours + rapports du jour pour éviter le N+1 dans la vue.
        $agentsIa = User::where('type_compte', 'agent_ia')
            ->with([
                'sessionsAgents' => fn($q) => $q->where('statut', 'en_cours')->latest(),
                'rapportsAgents' => fn($q) => $q->whereDate('created_at', today()),
            ])
            ->get()
            ->sortBy(fn($a) => [$a->rangHierarchique(), $a->agent_code])
            ->values();

        return view('membres.index', compact('humains', 'agentsIa'));
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

    public function storeAgent(Request $request)
    {
        $request->validate([
            'username'      => 'required|string|max:50|unique:users,username',
            'nom'           => 'required|string|max:100',
            'agent_code'    => 'required|string|max:40|unique:users,agent_code|regex:/^[a-z0-9\-]+$/',
            'agent_couleur' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
        ], [
            'agent_code.regex'   => 'Le code doit être en minuscules, chiffres et tirets uniquement.',
            'agent_code.unique'  => 'Ce code agent est déjà utilisé.',
            'username.unique'    => 'Cet identifiant est déjà utilisé.',
        ]);

        User::create([
            'username'      => $request->username,
            'nom'           => $request->nom,
            'prenom'        => 'Agent',
            'role'          => 'agent',
            'type_compte'   => 'agent_ia',
            'agent_code'    => $request->agent_code,
            'agent_couleur' => $request->agent_couleur,
            'password'      => Hash::make(\Illuminate\Support\Str::random(32)),
        ]);

        return redirect()->route('membres.index')->with('success', "Agent IA « {$request->agent_code} » créé. Générez son token via : php artisan sgt:agent-token --agent={$request->agent_code}");
    }

    public function destroy(User $membre)
    {
        abort_if($membre->id === auth()->id(), 403, 'Vous ne pouvez pas vous supprimer vous-même.');

        // Un agent IA avec une session en cours laisserait une session orpheline en base.
        if ($membre->isAgentIa() && $membre->sessionActive()) {
            return back()->withErrors([
                'delete' => "L'agent « {$membre->agent_code} » a une session active. Fermez-la avant de le supprimer.",
            ]);
        }

        $membre->delete();
        return redirect()->route('membres.index')->with('success', 'Membre supprimé.');
    }
}

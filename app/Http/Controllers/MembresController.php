<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\MotDePasseSgt;
use App\Http\Resources\PageParite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MembresController extends Controller
{
    public function index(Request $request)
    {
        if ($request->routeIs('api.gestion.*')) {
            return PageParite::reponse(User::where('type_compte', 'humain')->orderBy('nom')->orderBy('id')
                ->paginate(20, ['id', 'username', 'nom', 'prenom', 'role']));
        }

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
            'password' => ['bail', 'required', 'string', 'confirmed', new MotDePasseSgt],
        ], [
            'username.unique'   => 'Cet identifiant est déjà utilisé.',
            'password.confirmed'=> 'Les mots de passe ne correspondent pas.',
        ]);

        $membre = User::create([
            'username' => $request->username,
            'nom'      => $request->nom,
            'prenom'   => $request->prenom,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
        ]);

        if ($request->routeIs('api.gestion.*')) {
            return response()->json(['data' => $this->donnees($membre)], 201);
        }

        return redirect()->route('membres.index')->with('success', 'Membre ajouté avec succès.');
    }

    public function showApi(User $membre)
    {
        abort_unless($membre->isHumain(), 404);

        return response()->json(['data' => $this->donnees($membre)]);
    }

    private function donnees(User $membre): array
    {
        return $membre->only(['id', 'username', 'nom', 'prenom', 'role']);
    }

    public function edit(User $membre)
    {
        return view('membres.edit', compact('membre'));
    }

    public function update(Request $request, User $membre)
    {
        if ($request->routeIs('api.gestion.*') && $request->has('password')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'password' => "La modification du mot de passe d’un membre n’est pas autorisée depuis cette page.",
            ]);
        }

        abort_if($request->routeIs('api.gestion.*') && ! $membre->isHumain(), 404);
        $request->validate([
            'nom'    => 'required|string|max:100',
            'prenom' => 'nullable|string|max:100',
            'role'   => 'required|in:manager,technicien,agent,developpeur,stagiaire',
            'password' => ['bail', 'nullable', 'string', 'confirmed', new MotDePasseSgt],
        ]);

        if ($request->user()->is($membre) && $request->filled('password')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'password' => 'Modifiez votre mot de passe depuis votre profil.',
            ]);
        }

        $data = $request->only('nom', 'prenom', 'role');
        if ($request->filled('password')) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $membre, $data): void {
                $membre->update($data + ['password' => Hash::make($request->password)]);
                $membre->tokens()->delete();
            });
        } else {
            $membre->update($data);
        }

        if ($request->routeIs('api.gestion.*')) {
            return $this->showApi($membre);
        }

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

    public function destroy(Request $request, User $membre)
    {
        abort_if($request->routeIs('api.gestion.*') && ! $membre->isHumain(), 404);
        abort_if($membre->id === auth()->id(), 403, 'Vous ne pouvez pas vous supprimer vous-même.');

        // Un agent IA avec une session en cours laisserait une session orpheline en base.
        if ($membre->isAgentIa() && $membre->sessionActive()) {
            return back()->withErrors([
                'delete' => "L'agent « {$membre->agent_code} » a une session active. Fermez-la avant de le supprimer.",
            ]);
        }

        $membre->delete();
        if ($request->routeIs('api.gestion.*')) {
            return response()->noContent();
        }

        return redirect()->route('membres.index')->with('success', 'Membre supprimé.');
    }
}

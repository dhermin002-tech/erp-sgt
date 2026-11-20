<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmettreOrdreIaRequest;
use App\Models\OrdreIa;
use App\Models\Tache;
use App\Services\OrdresIa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrdresIaWebController extends Controller
{
    public function index(Request $request): View
    {
        $filtres = $request->validate([
            'statut' => ['nullable', Rule::in(OrdreIa::STATUTS)],
            'cible' => ['nullable', Rule::in(OrdreIa::CIBLES)],
        ]);
        $ordres = OrdreIa::query()->whereHas('tache')
            ->with(['tache:id,titre', 'emetteur:id,nom,prenom'])
            ->filtrer($filtres)->latest('id')->paginate(20)->withQueryString();

        return view('ordres-ia.index', compact('ordres', 'filtres'));
    }

    public function show(OrdreIa $ordre): View
    {
        $ordre->load(['tache:id,titre', 'emetteur:id,nom,prenom']);
        abort_unless($ordre->tache, 404);

        return view('ordres-ia.show', compact('ordre'));
    }

    public function store(EmettreOrdreIaRequest $request, Tache $tache, OrdresIa $ordres): RedirectResponse
    {
        $ordre = $ordres->emettre($request->validated() + ['tache_id' => $tache->id], $request->user());

        return redirect()->route('ordres-ia.show', $ordre)->with('success', 'Ordre IA enregistré.');
    }
}

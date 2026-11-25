<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Http\Resources\PageParite;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index(Request $request)
    {
        $sites = Site::withCount('taches')->orderBy('nom')->paginate(20);
        if ($request->routeIs('api.gestion.*')) {
            return PageParite::reponse($sites, fn (Site $site) => $this->donneesApi($site));
        }

        return view('sites.index', compact('sites'));
    }

    public function create()
    {
        return view('sites.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'   => 'required|string|max:255|unique:sites,nom',
            'ville' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        $site = Site::create($request->only('nom', 'ville', 'description'));

        if ($request->routeIs('api.gestion.*')) {
            return response()->json(['data' => $this->donneesApi($site)], 201);
        }

        return redirect()->route('sites.index')->with('success', 'Site créé.');
    }

    public function show(Request $request, Site $site)
    {
        if ($request->routeIs('api.gestion.*')) {
            return response()->json(['data' => $this->donneesApi($site->loadCount('taches'))]);
        }


        $site->load('taches');
        return view('sites.show', compact('site'));
    }

    public function edit(Site $site)
    {
        return view('sites.edit', compact('site'));
    }

    public function update(Request $request, Site $site)
    {
        $request->validate([
            'nom'   => 'required|string|max:255|unique:sites,nom,' . $site->id,
            'ville' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'actif' => 'boolean',
        ]);

        $site->update($request->only('nom', 'ville', 'description', 'actif'));

        if ($request->routeIs('api.gestion.*')) {
            return response()->json(['data' => $this->donneesApi($site)]);
        }

        return redirect()->route('sites.index')->with('success', 'Site mis à jour.');
    }

    public function destroy(Request $request, Site $site)
    {
        $site->delete();
        if ($request->routeIs('api.gestion.*')) {
            return response()->noContent();
        }

        return redirect()->route('sites.index')->with('success', 'Site supprimé.');
    }
    private function donneesApi(Site $site): array
    {
        return $site->only(['id', 'nom', 'ville', 'description', 'actif', 'taches_count']);
    }
}

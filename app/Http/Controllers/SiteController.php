<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index()
    {
        $sites = Site::withCount('taches')->orderBy('nom')->paginate(20);
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

        Site::create($request->only('nom', 'ville', 'description'));

        return redirect()->route('sites.index')->with('success', 'Site créé.');
    }

    public function show(Site $site)
    {
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

        return redirect()->route('sites.index')->with('success', 'Site mis à jour.');
    }

    public function destroy(Site $site)
    {
        $site->delete();
        return redirect()->route('sites.index')->with('success', 'Site supprimé.');
    }
}

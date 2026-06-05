<?php

namespace App\Http\Controllers;

use App\Models\Commentaire;
use App\Models\Tache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommentaireController extends Controller
{
    public function store(Request $request, Tache $tache)
    {
        $request->validate([
            'contenu' => 'required|string|max:3000',
            'photo'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'contenu.required' => 'Le commentaire ne peut pas être vide.',
            'photo.image'      => 'Le fichier doit être une image.',
            'photo.max'        => 'La photo ne doit pas dépasser 5 Mo.',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
        }

        $tache->commentaires()->create([
            'user_id'    => Auth::id(),
            'contenu'    => $request->contenu,
            'photo_path' => $photoPath,
        ]);

        return back()->with('success', 'Commentaire ajouté.');
    }

    public function destroy(Commentaire $commentaire)
    {
        $user = Auth::user();
        $peutSupprimer = $user->isManager() || $commentaire->user_id === $user->id;
        abort_unless($peutSupprimer, 403, 'Action non autorisée.');

        if ($commentaire->photo_path) {
            Storage::disk('public')->delete($commentaire->photo_path);
        }

        $commentaire->delete();

        return back()->with('success', 'Commentaire supprimé.');
    }
}

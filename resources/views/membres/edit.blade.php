@extends('layouts.app')
@section('title', 'Modifier : ' . $membre->nom_complet)

@section('content')
<div style="margin-bottom:1rem">
    <a href="{{ route('membres.index') }}" style="color:var(--slate-500);font-size:.875rem;text-decoration:none">← Membres</a>
    <h1 style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--kt-navy);margin-top:.5rem">Modifier : {{ $membre->nom_complet }}</h1>
</div>

<div style="background:var(--white);border-radius:12px;border:1px solid var(--slate-200);padding:1.75rem;max-width:500px">
    <form method="POST" action="{{ route('membres.update', $membre) }}">
        @csrf @method('PUT')

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
            <div>
                <label style="display:block;font-size:.85rem;font-weight:600;color:var(--slate-700);margin-bottom:.35rem">Nom *</label>
                <input type="text" name="nom" value="{{ old('nom', $membre->nom) }}" required
                    style="width:100%;padding:.55rem .875rem;border:1.5px solid var(--slate-200);border-radius:8px;font-size:.875rem;box-sizing:border-box;outline:none">
            </div>
            <div>
                <label style="display:block;font-size:.85rem;font-weight:600;color:var(--slate-700);margin-bottom:.35rem">Prénom</label>
                <input type="text" name="prenom" value="{{ old('prenom', $membre->prenom) }}"
                    style="width:100%;padding:.55rem .875rem;border:1.5px solid var(--slate-200);border-radius:8px;font-size:.875rem;box-sizing:border-box;outline:none">
            </div>
        </div>

        <div style="margin-bottom:1rem">
            <label style="display:block;font-size:.85rem;font-weight:600;color:var(--slate-700);margin-bottom:.35rem">Identifiant</label>
            <input type="text" value="{{ $membre->username }}" disabled
                style="width:100%;padding:.55rem .875rem;border:1.5px solid var(--slate-100);border-radius:8px;font-size:.875rem;box-sizing:border-box;background:var(--slate-50);color:var(--slate-400)">
            <div style="font-size:.75rem;color:var(--slate-400);margin-top:.2rem">L'identifiant ne peut pas être modifié.</div>
        </div>

        <div style="margin-bottom:1rem">
            <label style="display:block;font-size:.85rem;font-weight:600;color:var(--slate-700);margin-bottom:.35rem">Rôle *</label>
            <select name="role" style="width:100%;padding:.55rem .875rem;border:1.5px solid var(--slate-200);border-radius:8px;font-size:.875rem;outline:none">
                @foreach(['manager'=>'Manager','technicien'=>'Technicien','agent'=>'Agent','developpeur'=>'Développeur','stagiaire'=>'Stagiaire'] as $val => $lib)
                <option value="{{ $val }}" {{ old('role',$membre->role) === $val ? 'selected' : '' }}>{{ $lib }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:1rem">
            <label style="display:block;font-size:.85rem;font-weight:600;color:var(--slate-700);margin-bottom:.35rem">Nouveau mot de passe <span style="color:var(--slate-400);font-weight:400">(laisser vide = inchangé)</span></label>
            <input type="password" name="password" autocomplete="new-password"
                style="width:100%;padding:.55rem .875rem;border:1.5px solid var(--slate-200);border-radius:8px;font-size:.875rem;box-sizing:border-box;outline:none">
            @error('password')<div style="color:var(--kt-maroon);font-size:.78rem;margin-top:.2rem">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:1.5rem">
            <label style="display:block;font-size:.85rem;font-weight:600;color:var(--slate-700);margin-bottom:.35rem">Confirmer le nouveau mot de passe</label>
            <input type="password" name="password_confirmation"
                style="width:100%;padding:.55rem .875rem;border:1.5px solid var(--slate-200);border-radius:8px;font-size:.875rem;box-sizing:border-box;outline:none">
        </div>

        <div style="display:flex;gap:.75rem">
            <button type="submit" style="background:var(--kt-navy);color:#fff;padding:.55rem 1.1rem;border-radius:7px;border:none;font-size:.875rem;font-weight:600;cursor:pointer">Enregistrer</button>
            <a href="{{ route('membres.index') }}" style="background:none;border:1px solid var(--slate-200);color:var(--slate-600);padding:.55rem 1.1rem;border-radius:7px;text-decoration:none;font-size:.875rem;font-weight:600">Annuler</a>
        </div>
    </form>
</div>
@endsection

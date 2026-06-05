@extends('layouts.app')
@section('title', 'Nouveau membre')

@section('content')
<div style="margin-bottom:1rem">
    <a href="{{ route('membres.index') }}" style="color:var(--slate-500);font-size:.875rem;text-decoration:none">← Membres</a>
    <h1 style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--kt-navy);margin-top:.5rem">Nouveau membre</h1>
</div>

<div style="background:var(--white);border-radius:12px;border:1px solid var(--slate-200);padding:1.75rem;max-width:500px">
    <form method="POST" action="{{ route('membres.store') }}">
        @csrf

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
            <div>
                <label style="display:block;font-size:.85rem;font-weight:600;color:var(--slate-700);margin-bottom:.35rem">Nom *</label>
                <input type="text" name="nom" value="{{ old('nom') }}" required
                    style="width:100%;padding:.55rem .875rem;border:1.5px solid {{ $errors->has('nom') ? 'var(--kt-maroon)' : 'var(--slate-200)' }};border-radius:8px;font-size:.875rem;box-sizing:border-box;outline:none">
                @error('nom')<div style="color:var(--kt-maroon);font-size:.78rem;margin-top:.2rem">{{ $message }}</div>@enderror
            </div>
            <div>
                <label style="display:block;font-size:.85rem;font-weight:600;color:var(--slate-700);margin-bottom:.35rem">Prénom</label>
                <input type="text" name="prenom" value="{{ old('prenom') }}"
                    style="width:100%;padding:.55rem .875rem;border:1.5px solid var(--slate-200);border-radius:8px;font-size:.875rem;box-sizing:border-box;outline:none">
            </div>
        </div>

        <div style="margin-bottom:1rem">
            <label style="display:block;font-size:.85rem;font-weight:600;color:var(--slate-700);margin-bottom:.35rem">Identifiant (username) *</label>
            <input type="text" name="username" value="{{ old('username') }}" required autocomplete="off"
                style="width:100%;padding:.55rem .875rem;border:1.5px solid {{ $errors->has('username') ? 'var(--kt-maroon)' : 'var(--slate-200)' }};border-radius:8px;font-size:.875rem;box-sizing:border-box;outline:none">
            @error('username')<div style="color:var(--kt-maroon);font-size:.78rem;margin-top:.2rem">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:1rem">
            <label style="display:block;font-size:.85rem;font-weight:600;color:var(--slate-700);margin-bottom:.35rem">Rôle *</label>
            <select name="role" style="width:100%;padding:.55rem .875rem;border:1.5px solid var(--slate-200);border-radius:8px;font-size:.875rem;outline:none">
                @foreach(['manager'=>'Manager','technicien'=>'Technicien','agent'=>'Agent','developpeur'=>'Développeur','stagiaire'=>'Stagiaire'] as $val => $lib)
                <option value="{{ $val }}" {{ old('role','agent') === $val ? 'selected' : '' }}>{{ $lib }}</option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:1rem">
            <label style="display:block;font-size:.85rem;font-weight:600;color:var(--slate-700);margin-bottom:.35rem">Mot de passe *</label>
            <input type="password" name="password" required autocomplete="new-password"
                style="width:100%;padding:.55rem .875rem;border:1.5px solid {{ $errors->has('password') ? 'var(--kt-maroon)' : 'var(--slate-200)' }};border-radius:8px;font-size:.875rem;box-sizing:border-box;outline:none">
            @error('password')<div style="color:var(--kt-maroon);font-size:.78rem;margin-top:.2rem">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:1.5rem">
            <label style="display:block;font-size:.85rem;font-weight:600;color:var(--slate-700);margin-bottom:.35rem">Confirmer le mot de passe *</label>
            <input type="password" name="password_confirmation" required
                style="width:100%;padding:.55rem .875rem;border:1.5px solid var(--slate-200);border-radius:8px;font-size:.875rem;box-sizing:border-box;outline:none">
        </div>

        <div style="display:flex;gap:.75rem">
            <button type="submit" style="background:var(--kt-navy);color:#fff;padding:.55rem 1.1rem;border-radius:7px;border:none;font-size:.875rem;font-weight:600;cursor:pointer">Créer le membre</button>
            <a href="{{ route('membres.index') }}" style="background:none;border:1px solid var(--slate-200);color:var(--slate-600);padding:.55rem 1.1rem;border-radius:7px;text-decoration:none;font-size:.875rem;font-weight:600">Annuler</a>
        </div>
    </form>
</div>
@endsection

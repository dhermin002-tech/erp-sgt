@extends('layouts.app')
@section('title', 'Sites d\'intervention')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
    <div>
        <h1 style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--kt-navy)">Sites d'intervention</h1>
        <p style="color:var(--slate-500);font-size:.85rem;margin-top:.15rem">{{ $sites->total() }} site(s) enregistré(s)</p>
    </div>
    <a href="{{ route('sites.create') }}" style="background:var(--kt-navy);color:#fff;padding:.5rem 1rem;border-radius:7px;text-decoration:none;font-size:.875rem;font-weight:600">+ Nouveau site</a>
</div>

<div style="background:var(--white);border-radius:12px;border:1px solid var(--slate-200);overflow:hidden">
    @if($sites->isEmpty())
    <div style="text-align:center;padding:3rem;color:var(--slate-400)">
        <div style="font-size:2.5rem;margin-bottom:.5rem">📍</div>
        <div style="font-weight:600">Aucun site enregistré</div>
        <div style="margin-top:.5rem"><a href="{{ route('sites.create') }}" style="color:var(--kt-navy)">Créer le premier site</a></div>
    </div>
    @else
    <table style="width:100%;border-collapse:collapse">
        <thead>
            <tr style="background:var(--slate-50)">
                <th style="padding:.65rem 1rem;text-align:left;font-size:.8rem;font-weight:700;color:var(--slate-600);border-bottom:1px solid var(--slate-200)">Nom</th>
                <th style="padding:.65rem 1rem;text-align:left;font-size:.8rem;font-weight:700;color:var(--slate-600);border-bottom:1px solid var(--slate-200)">Ville</th>
                <th style="padding:.65rem 1rem;text-align:left;font-size:.8rem;font-weight:700;color:var(--slate-600);border-bottom:1px solid var(--slate-200)">Tâches actives</th>
                <th style="padding:.65rem 1rem;text-align:left;font-size:.8rem;font-weight:700;color:var(--slate-600);border-bottom:1px solid var(--slate-200)">Statut</th>
                <th style="padding:.65rem 1rem;border-bottom:1px solid var(--slate-200)"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($sites as $site)
            <tr style="border-bottom:1px solid var(--slate-100)">
                <td style="padding:.75rem 1rem;font-weight:600;color:var(--slate-700)">{{ $site->nom }}</td>
                <td style="padding:.75rem 1rem;color:var(--slate-600)">{{ $site->ville }}</td>
                <td style="padding:.75rem 1rem;color:var(--slate-600)">{{ $site->taches_count }}</td>
                <td style="padding:.75rem 1rem">
                    @if($site->actif)
                        <span style="background:#D1FAE5;color:#065F46;font-size:.72rem;font-weight:700;padding:.2rem .6rem;border-radius:999px">Actif</span>
                    @else
                        <span style="background:var(--slate-100);color:var(--slate-500);font-size:.72rem;font-weight:700;padding:.2rem .6rem;border-radius:999px">Inactif</span>
                    @endif
                </td>
                <td style="padding:.75rem 1rem">
                    <div style="display:flex;gap:.5rem">
                        <a href="{{ route('sites.edit', $site) }}" style="font-size:.82rem;color:var(--kt-navy);text-decoration:none;font-weight:600">Modifier</a>
                        <form method="POST" action="{{ route('sites.destroy', $site) }}" onsubmit="return confirm('Supprimer ce site ?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="background:none;border:none;cursor:pointer;font-size:.82rem;color:#991B1B">Supprimer</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@if($sites->hasPages())
<div style="margin-top:1rem">{{ $sites->links('pagination::bootstrap-4') }}</div>
@endif
@endsection

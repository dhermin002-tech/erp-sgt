@extends('layouts.app')
@section('title', 'Membres')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
    <div>
        <h1 style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--kt-navy)">Membres</h1>
        <p style="color:var(--slate-500);font-size:.85rem;margin-top:.15rem">{{ $membres->total() }} membre(s) dans l'équipe</p>
    </div>
    <a href="{{ route('membres.create') }}" style="background:var(--kt-navy);color:#fff;padding:.5rem 1rem;border-radius:7px;text-decoration:none;font-size:.875rem;font-weight:600">+ Nouveau membre</a>
</div>

@php
$roleColors = [
    'manager'     => ['bg'=>'#EFF6FF','color'=>'#1D4ED8'],
    'technicien'  => ['bg'=>'#F0FDF4','color'=>'#166534'],
    'agent'       => ['bg'=>'#FFF7ED','color'=>'#9A3412'],
    'developpeur' => ['bg'=>'#FDF4FF','color'=>'#7E22CE'],
    'stagiaire'   => ['bg'=>'var(--slate-100)','color'=>'var(--slate-600)'],
];
@endphp

<div style="background:var(--white);border-radius:12px;border:1px solid var(--slate-200);overflow:hidden">
    @if($membres->isEmpty())
    <div style="text-align:center;padding:3rem;color:var(--slate-400)">
        <div style="font-size:2.5rem;margin-bottom:.5rem">👥</div>
        <div>Aucun membre trouvé.</div>
    </div>
    @else
    <table style="width:100%;border-collapse:collapse">
        <thead>
            <tr style="background:var(--slate-50)">
                <th style="padding:.65rem 1rem;text-align:left;font-size:.8rem;font-weight:700;color:var(--slate-600);border-bottom:1px solid var(--slate-200)">Membre</th>
                <th style="padding:.65rem 1rem;text-align:left;font-size:.8rem;font-weight:700;color:var(--slate-600);border-bottom:1px solid var(--slate-200)">Identifiant</th>
                <th style="padding:.65rem 1rem;text-align:left;font-size:.8rem;font-weight:700;color:var(--slate-600);border-bottom:1px solid var(--slate-200)">Rôle</th>
                <th style="padding:.65rem 1rem;text-align:left;font-size:.8rem;font-weight:700;color:var(--slate-600);border-bottom:1px solid var(--slate-200)">Tâches actives</th>
                <th style="padding:.65rem 1rem;border-bottom:1px solid var(--slate-200)"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($membres as $membre)
            @php $rc = $roleColors[$membre->role] ?? $roleColors['stagiaire']; @endphp
            <tr style="border-bottom:1px solid var(--slate-100){{ $membre->id === auth()->id() ? ';background:var(--slate-50)' : '' }}">
                <td style="padding:.75rem 1rem">
                    <div style="display:flex;align-items:center;gap:.65rem">
                        <div style="width:34px;height:34px;border-radius:50%;background:var(--kt-navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.78rem;font-weight:700;flex-shrink:0">
                            {{ strtoupper(substr($membre->prenom ?? '',0,1)) }}{{ strtoupper(substr($membre->nom,0,1)) }}
                        </div>
                        <div>
                            <div style="font-weight:600;color:var(--slate-700);font-size:.875rem">{{ $membre->nom_complet }}</div>
                            @if($membre->id === auth()->id())
                            <div style="font-size:.7rem;color:var(--slate-400)">Vous</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td style="padding:.75rem 1rem;font-family:var(--font-display);font-size:.85rem;color:var(--slate-600)">{{ $membre->username }}</td>
                <td style="padding:.75rem 1rem">
                    <span style="background:{{ $rc['bg'] }};color:{{ $rc['color'] }};font-size:.72rem;font-weight:700;padding:.2rem .65rem;border-radius:999px;text-transform:capitalize">{{ $membre->role }}</span>
                </td>
                <td style="padding:.75rem 1rem;color:var(--slate-600);font-size:.875rem">
                    {{ $membre->taches()->actives()->count() }}
                </td>
                <td style="padding:.75rem 1rem">
                    <div style="display:flex;gap:.5rem">
                        <a href="{{ route('membres.edit', $membre) }}" style="font-size:.82rem;color:var(--kt-navy);text-decoration:none;font-weight:600">Modifier</a>
                        @if($membre->id !== auth()->id())
                        <form method="POST" action="{{ route('membres.destroy', $membre) }}" onsubmit="return confirm('Supprimer ce membre ?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="background:none;border:none;cursor:pointer;font-size:.82rem;color:#991B1B">Supprimer</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@if($membres->hasPages())
<div style="margin-top:1rem">{{ $membres->links('pagination::bootstrap-4') }}</div>
@endif
@endsection

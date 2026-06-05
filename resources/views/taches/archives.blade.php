@extends('layouts.app')
@section('title', 'Archives')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
    <div>
        <h1 style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--kt-navy)">Tâches archivées</h1>
        <p style="color:var(--slate-500);font-size:.85rem">{{ $taches->total() }} tâche(s) terminée(s)</p>
    </div>
    <a href="{{ route('taches.index') }}" style="color:var(--slate-500);font-size:.875rem;text-decoration:none">← Tâches actives</a>
</div>

<div style="background:var(--white);border-radius:12px;border:1px solid var(--slate-200);overflow:hidden">
    @if($taches->isEmpty())
    <div style="text-align:center;padding:3rem;color:var(--slate-400)">
        <div style="font-size:2.5rem;margin-bottom:.5rem">🗄</div>
        <div>Aucune tâche archivée pour l'instant.</div>
    </div>
    @else
    <table style="width:100%;border-collapse:collapse">
        <thead>
            <tr style="background:var(--slate-50)">
                <th style="padding:.65rem 1rem;font-size:.78rem;font-weight:700;color:var(--slate-600);text-align:left;border-bottom:1px solid var(--slate-200)">Tâche</th>
                <th style="padding:.65rem 1rem;font-size:.78rem;font-weight:700;color:var(--slate-600);text-align:left;border-bottom:1px solid var(--slate-200)">Responsables</th>
                <th style="padding:.65rem 1rem;font-size:.78rem;font-weight:700;color:var(--slate-600);text-align:left;border-bottom:1px solid var(--slate-200)">Site</th>
                <th style="padding:.65rem 1rem;font-size:.78rem;font-weight:700;color:var(--slate-600);text-align:left;border-bottom:1px solid var(--slate-200)">Archivée le</th>
                <th style="padding:.65rem 1rem;font-size:.78rem;font-weight:700;color:var(--slate-600);border-bottom:1px solid var(--slate-200)">Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($taches as $tache)
        <tr style="border-bottom:1px solid var(--slate-100)">
            <td style="padding:.75rem 1rem">
                <a href="{{ route('taches.show', $tache) }}" style="font-weight:600;color:var(--slate-600);text-decoration:none">{{ $tache->titre }}</a>
                @include('partials.badge_statut', ['statut' => $tache->statut])
            </td>
            <td style="padding:.75rem 1rem;font-size:.85rem;color:var(--slate-600)">
                {{ $tache->responsables->pluck('nom')->implode(', ') ?: '—' }}
            </td>
            <td style="padding:.75rem 1rem;font-size:.85rem;color:var(--slate-600)">{{ $tache->site?->nom ?? '—' }}</td>
            <td style="padding:.75rem 1rem;font-size:.85rem;color:var(--slate-500)">{{ $tache->archived_at?->format('d/m/Y') }}</td>
            <td style="padding:.75rem 1rem">
                <form method="POST" action="{{ route('taches.restaurer', $tache) }}">
                    @csrf @method('PATCH')
                    <button type="submit" style="background:var(--slate-100);color:var(--slate-700);border:1px solid var(--slate-200);border-radius:6px;padding:.3rem .65rem;font-size:.8rem;cursor:pointer">
                        Restaurer
                    </button>
                </form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>

@if($taches->hasPages())
<div style="margin-top:1rem">{{ $taches->links('pagination::bootstrap-4') }}</div>
@endif
@endsection

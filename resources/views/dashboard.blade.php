@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
<div style="margin-bottom:1.5rem">
    <h1 style="font-family:var(--font-display);font-size:1.5rem;font-weight:700;color:var(--kt-navy)">
        Tableau de bord
    </h1>
    <p style="color:var(--slate-500);font-size:.875rem;margin-top:.25rem">
        Bonjour {{ auth()->user()->nom_complet }} — {{ ucfirst(auth()->user()->role) }}
    </p>
</div>

{{-- KPI Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:2rem">

    <div style="background:var(--white);border-radius:12px;padding:1.25rem;border:1px solid var(--slate-200);box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:.8rem;font-weight:600;color:var(--slate-500);text-transform:uppercase;letter-spacing:.05em">Tâches actives</div>
        <div style="font-size:2rem;font-weight:800;color:var(--kt-navy);font-family:var(--font-display);margin:.5rem 0">{{ $stats['total_actives'] }}</div>
        <div style="font-size:.8rem;color:var(--slate-400)">en cours de traitement</div>
    </div>

    <div style="background:var(--white);border-radius:12px;padding:1.25rem;border:1px solid var(--slate-200);box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:.8rem;font-weight:600;color:var(--slate-500);text-transform:uppercase;letter-spacing:.05em">En cours</div>
        <div style="font-size:2rem;font-weight:800;color:#2563EB;font-family:var(--font-display);margin:.5rem 0">{{ $stats['en_cours'] }}</div>
        <div style="font-size:.8rem;color:var(--slate-400)">travail actif</div>
    </div>

    <div style="background:var(--white);border-radius:12px;padding:1.25rem;border:1px solid var(--slate-200);box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:.8rem;font-weight:600;color:var(--slate-500);text-transform:uppercase;letter-spacing:.05em">Taux de complétion</div>
        <div style="font-size:2rem;font-weight:800;color:#15885A;font-family:var(--font-display);margin:.5rem 0">{{ $stats['taux_completion'] }}%</div>
        <div style="font-size:.8rem;color:var(--slate-400)">tâches terminées</div>
    </div>

    <div style="background:{{ $stats['en_retard'] > 0 ? '#FEE2E2' : 'var(--white)' }};border-radius:12px;padding:1.25rem;border:1px solid {{ $stats['en_retard'] > 0 ? '#FCA5A5' : 'var(--slate-200)' }};box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:.8rem;font-weight:600;color:{{ $stats['en_retard'] > 0 ? '#991B1B' : 'var(--slate-500)' }};text-transform:uppercase;letter-spacing:.05em">En retard</div>
        <div style="font-size:2rem;font-weight:800;color:{{ $stats['en_retard'] > 0 ? '#B0202E' : 'var(--slate-300)' }};font-family:var(--font-display);margin:.5rem 0">{{ $stats['en_retard'] }}</div>
        <div style="font-size:.8rem;color:{{ $stats['en_retard'] > 0 ? '#991B1B' : 'var(--slate-400)' }}">
            {{ $stats['en_retard'] > 0 ? '⚠️ Attention requise' : 'Aucun retard' }}
        </div>
    </div>

</div>

{{-- Placeholder graphiques (Sprint 5) --}}
<div style="background:var(--white);border-radius:12px;padding:2rem;border:1px solid var(--slate-200);text-align:center;color:var(--slate-400)">
    <div style="font-size:2.5rem;margin-bottom:.5rem">📈</div>
    <div style="font-weight:600;margin-bottom:.25rem">Graphiques disponibles au Sprint 5</div>
    <div style="font-size:.875rem">Répartition par statut · Avancement dans le temps · Charge par responsable</div>
</div>
@endsection

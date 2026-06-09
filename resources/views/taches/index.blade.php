@extends('layouts.app')
@section('title', 'Tâches')

@php
$railVar = [
    'urgente' => 'var(--st-stop)',
    'haute'   => 'var(--st-wait)',
    'normale' => 'var(--st-progress)',
    'basse'   => 'var(--st-todo)',
];
$initiales = fn($r) => strtoupper(mb_substr($r->prenom, 0, 1) . mb_substr($r->nom, 0, 1));
$avatarBg = ['var(--kt-navy)', 'var(--kt-orange)', 'var(--kt-purple)', 'var(--kt-maroon)', 'var(--st-progress)'];
@endphp

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
<style>
.btn { display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;border-radius:7px;font-size:.875rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;transition:all .15s; }
.btn-primary { background:var(--kt-navy);color:#fff; }
.btn-primary:hover { background:var(--kt-navy-700); }
.btn-ghost { background:none;color:var(--slate-600);border:1px solid var(--slate-200); }
.btn-ghost:hover { background:var(--slate-50); }
.btn-danger { background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5; }
.btn-danger:hover { background:#FCA5A5; }
.btn-sm { padding:.3rem .65rem;font-size:.8rem; }

.filters-bar { background:var(--white);border-radius:12px;border:1px solid var(--slate-200);padding:1rem;margin-bottom:1.25rem;display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end; }
.filter-group { display:flex;flex-direction:column;gap:.3rem; }
.filter-label { font-size:.75rem;font-weight:600;color:var(--slate-500);text-transform:uppercase;letter-spacing:.04em; }
.filter-input { padding:.45rem .75rem;border:1.5px solid var(--slate-200);border-radius:7px;font-size:.85rem;color:var(--slate-700);background:var(--slate-50);outline:none; }
.filter-input:focus { border-color:var(--kt-navy); }

/* ── Page header premium ── */
.page-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 1.25rem; gap: 1rem; flex-wrap: wrap;
    background: linear-gradient(135deg, #001f3f 0%, #003366 60%, #002244 100%);
    border-radius: 14px;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,.15);
    position: relative; overflow: hidden;
}
.page-header::before {
    content: '';
    position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.04) 1px, transparent 1px);
    background-size: 20px 20px;
    pointer-events: none;
}
.page-header::after {
    content: '';
    position: absolute; bottom: -30px; right: -30px;
    width: 150px; height: 150px; border-radius: 50%;
    background: radial-gradient(circle, rgba(204,85,0,.1) 0%, transparent 70%);
    pointer-events: none;
}
.page-header h1 {
    font-family: 'Space Grotesk', sans-serif !important;
    font-size: 1.3rem !important; font-weight: 700 !important;
    color: #fff !important; letter-spacing: -.025em;
    position: relative; z-index: 1;
}
.page-header p {
    color: rgba(255,255,255,.5) !important;
    font-size: .82rem; margin-top: .1rem;
    position: relative; z-index: 1;
}
.page-header .btn-primary {
    background: #CC5500 !important; border: none;
    box-shadow: 0 4px 14px rgba(204,85,0,.35);
    position: relative; z-index: 1;
}
.page-header .btn-primary:hover { background: #E06010 !important; }

.empty-state { text-align:center;padding:3rem;color:var(--slate-400);background:var(--white);border-radius:12px;border:1px solid var(--slate-200); }

/* ---- Liste de tâches ---- */
.task-list { display:flex; flex-direction:column; gap:.6rem; }

.task-row-link { text-decoration:none; color:inherit; display:block; }
.task-row-link:hover .kt-task-row {
    box-shadow: 0 6px 20px rgba(15,23,42,.12);
    transform: translateY(-2px);
}
.kt-task-row {
    transition: box-shadow .18s ease, transform .18s ease;
    box-shadow: 0 1px 4px rgba(15,23,42,.06);
}

/* Carte "Mes tâches" — fond bleu très léger + bordure supérieure */
.kt-task-row.mine { background:#EFF6FF !important; border-top-color:#BFDBFE !important; }

/* Badge "Moi" */
.badge-mine {
    display:inline-flex; align-items:center; gap:.25rem;
    background:#1E40AF; color:#fff;
    font-size:.65rem; font-weight:700;
    padding:.15rem .5rem; border-radius:999px;
    white-space:nowrap; letter-spacing:.02em;
}

/* ── Séparateurs de sections — design premium ── */
.section-sep {
    display: flex; align-items: center; gap: .75rem;
    margin: 1.1rem 0 .7rem;
}
.section-sep-line { flex: 1; height: 1.5px; }
.section-sep-label {
    display: flex; align-items: center; gap: .5rem;
    font-family: 'Space Grotesk', sans-serif;
    font-size: .72rem; font-weight: 700;
    letter-spacing: .08em; text-transform: uppercase;
    white-space: nowrap;
    padding: .3rem .85rem;
    border-radius: 999px;
    border: 1.5px solid transparent;
}
.section-sep-count {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 1.5rem; height: 1.5rem;
    border-radius: 999px; font-size: .68rem; font-weight: 800;
    padding: 0 .4rem;
}

/* Section "Mes tâches" — bleu avec fond */
.section-sep.mine .section-sep-line   { background: linear-gradient(90deg, transparent, #BFDBFE, #BFDBFE, transparent); }
.section-sep.mine .section-sep-label  {
    color: #1D4ED8;
    background: #EFF6FF;
    border-color: #BFDBFE;
}
.section-sep.mine .section-sep-count  { background: #DBEAFE; color: #1D4ED8; }

/* Section "Équipe" — gris slate */
.section-sep.equipe .section-sep-line  { background: linear-gradient(90deg, transparent, var(--slate-200), var(--slate-200), transparent); }
.section-sep.equipe .section-sep-label {
    color: var(--slate-600);
    background: var(--slate-50);
    border-color: var(--slate-200);
}
.section-sep.equipe .section-sep-count { background: var(--slate-200); color: var(--slate-600); }

.task-top { display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem; flex-wrap:wrap; }
.task-titre { font-family:'Space Grotesk', var(--font-display), sans-serif; font-weight:700; font-size:.95rem; color:var(--kt-navy); line-height:1.3; }
.task-sub { font-size:.74rem; color:var(--slate-400); margin-top:.15rem; }
.task-badges { display:flex; align-items:center; gap:.4rem; flex-wrap:wrap; }
.badge-retard { background:var(--st-stop); color:#fff; font-size:.65rem; font-weight:700; padding:.15rem .5rem; border-radius:999px; white-space:nowrap; }

.task-meta { display:flex; align-items:center; gap:1.1rem; flex-wrap:wrap; margin-top:.65rem; font-size:.8rem; color:var(--slate-500); }
.task-meta .meta-item { display:flex; align-items:center; gap:.35rem; }
.task-meta .echeance.late { color:var(--st-stop); font-weight:700; }

.avatar-stack { display:flex; }
.avatar-stack .kt-avatar { margin-left:-8px; }
.avatar-stack .kt-avatar:first-child { margin-left:0; }

.task-actions { display:flex; gap:.3rem; align-items:center; }

@media (max-width: 640px) {
    .task-meta { gap:.6rem .9rem; }
    .task-actions { width:100%; justify-content:flex-end; margin-top:.5rem; }
}
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--kt-navy)">Tâches actives</h1>
        <p style="color:var(--slate-500);font-size:.85rem;margin-top:.15rem">{{ $taches->total() }} tâche(s) trouvée(s)</p>
    </div>
    <a href="{{ route('taches.create') }}" class="btn btn-primary">+ Nouvelle tâche</a>
</div>

{{-- Filtres --}}
<form method="GET" action="{{ route('taches.index') }}" class="filters-bar">
    <div class="filter-group" style="flex:1;min-width:160px">
        <label class="filter-label">Recherche</label>
        <input type="text" name="q" class="filter-input" placeholder="Titre ou description..." value="{{ request('q') }}">
    </div>
    <div class="filter-group">
        <label class="filter-label">Statut</label>
        <select name="statut" class="filter-input">
            <option value="">Tous</option>
            @foreach(['nouveau','en_cours','en_attente','en_arret','termine'] as $s)
            <option value="{{ $s }}" {{ request('statut') === $s ? 'selected' : '' }}>
                {{ ['nouveau'=>'Nouveau','en_cours'=>'En cours','en_attente'=>'En attente','en_arret'=>'En arrêt','termine'=>'Terminé'][$s] }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <label class="filter-label">Site</label>
        <select name="site_id" class="filter-input">
            <option value="">Tous</option>
            @foreach($sites as $site)
            <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>{{ $site->nom }}</option>
            @endforeach
        </select>
    </div>
    @if(auth()->user()->isManager())
    <div class="filter-group">
        <label class="filter-label">Responsable</label>
        <select name="responsable_id" class="filter-input">
            <option value="">Tous</option>
            @foreach($membres as $m)
            <option value="{{ $m->id }}" {{ request('responsable_id') == $m->id ? 'selected' : '' }}>{{ $m->nom_complet }}</option>
            @endforeach
        </select>
    </div>
    @endif
    <div class="filter-group" style="flex-direction:row;align-items:flex-end;gap:.5rem">
        <button type="submit" class="btn btn-primary">Filtrer</button>
        @if(request()->hasAny(['q','statut','site_id','responsable_id']))
        <a href="{{ route('taches.index') }}" class="btn btn-ghost">Réinitialiser</a>
        @endif
    </div>
</form>

{{-- Liste des tâches --}}
@if($taches->isEmpty())
<div class="empty-state">
    <div style="font-size:2.5rem;margin-bottom:.5rem">📋</div>
    <div style="font-weight:600">Aucune tâche trouvée</div>
    <div style="font-size:.875rem;margin-top:.25rem">
        <a href="{{ route('taches.create') }}" style="color:var(--kt-navy)">Créer la première tâche</a>
    </div>
</div>
@else

@php
$isManager = auth()->user()->isManager();
$myId      = auth()->id();

if ($isManager) {
    [$mesTaches, $tachesEquipe] = $taches->getCollection()->partition(
        fn($t) => $t->responsables->contains('id', $myId)
    );
}
@endphp

@if($isManager)
{{-- ═══════════════ VUE MANAGER : 2 sections ═══════════════ --}}

{{-- Section : Mes tâches --}}
<div class="section-sep mine">
    <div class="section-sep-line"></div>
    <div class="section-sep-label">
        <span>👤 Mes tâches</span>
        <span class="section-sep-count">{{ $mesTaches->count() }}</span>
    </div>
    <div class="section-sep-line"></div>
</div>

@if($mesTaches->isEmpty())
<div class="empty-state" style="padding:1.5rem;margin-bottom:.5rem">
    <span style="font-size:.875rem">Aucune tâche assignée à vous sur cette page.</span>
</div>
@else
<div class="task-list">
@foreach($mesTaches as $tache)
    @include('taches._card', ['tache' => $tache, 'isMine' => true, 'railVar' => $railVar, 'avatarBg' => $avatarBg, 'initiales' => $initiales])
@endforeach
</div>
@endif

{{-- Section : Tâches équipe --}}
<div class="section-sep equipe" style="margin-top:1.5rem">
    <div class="section-sep-line"></div>
    <div class="section-sep-label">
        <span>👥 Tâches équipe</span>
        <span class="section-sep-count">{{ $tachesEquipe->count() }}</span>
    </div>
    <div class="section-sep-line"></div>
</div>

@if($tachesEquipe->isEmpty())
<div class="empty-state" style="padding:1.5rem">
    <span style="font-size:.875rem">Toutes les tâches de cette page vous sont assignées.</span>
</div>
@else
<div class="task-list">
@foreach($tachesEquipe as $tache)
    @include('taches._card', ['tache' => $tache, 'isMine' => false, 'railVar' => $railVar, 'avatarBg' => $avatarBg, 'initiales' => $initiales])
@endforeach
</div>
@endif

@else
{{-- ═══════════════ VUE COLLABORATEUR : liste simple ═══════════════ --}}
<div class="task-list">
@foreach($taches as $tache)
    @include('taches._card', ['tache' => $tache, 'isMine' => false, 'railVar' => $railVar, 'avatarBg' => $avatarBg, 'initiales' => $initiales])
@endforeach
</div>
@endif

@endif

@if($taches->hasPages())
<div style="margin-top:1.25rem">{{ $taches->links('pagination::bootstrap-4') }}</div>
@endif
@endsection

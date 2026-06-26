@extends('layouts.app')
@section('title', 'Tâches des agents IA')

@php
$railVar = [
    'urgente' => 'var(--st-stop)',
    'haute'   => 'var(--st-wait)',
    'normale' => 'var(--st-progress)',
    'basse'   => 'var(--st-todo)',
];
$initiales = fn($r) => strtoupper(mb_substr($r->prenom ?? '', 0, 1) . mb_substr($r->nom ?? '', 0, 1));
$avatarBg = ['var(--kt-navy)', 'var(--kt-orange)', 'var(--kt-purple)', 'var(--kt-maroon)', 'var(--st-progress)'];
@endphp

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
.agents-hero {
    background: linear-gradient(135deg, #1a0533 0%, #3b0764 60%, #1a0533 100%);
    border-radius: 16px; padding: 1.4rem 1.75rem; margin-bottom: 1.35rem;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: .75rem; position: relative; overflow: hidden;
    box-shadow: 0 4px 24px rgba(76,29,149,.25);
}
.agents-hero::before {
    content: ''; position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.04) 1px, transparent 1px);
    background-size: 22px 22px; pointer-events: none;
}
.agents-hero-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.35rem; font-weight: 700; color: #fff;
    letter-spacing: -.03em; display: flex; align-items: center; gap: .6rem;
}
.agents-hero-sub { font-size: .82rem; color: rgba(255,255,255,.5); margin-top: .2rem; }

/* KPIs */
.kpi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:.85rem; margin-bottom:1.35rem; }
.kpi-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:1rem 1.1rem; box-shadow:0 1px 4px rgba(0,0,0,.05); }
.kpi-val { font-family:'Space Grotesk',sans-serif; font-size:1.6rem; font-weight:700; color:#4c1d95; line-height:1; }
.kpi-label { font-size:.74rem; color:#94a3b8; margin-top:.35rem; font-weight:600; }

.filters-bar { background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:.8rem 1.1rem; margin-bottom:1.25rem; display:flex; flex-wrap:wrap; gap:.65rem; align-items:flex-end; }
.filter-group { display:flex; flex-direction:column; gap:.2rem; }
.filter-label { font-size:.7rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.05em; }
.filter-select { padding:.4rem .75rem; border:1.5px solid #e2e8f0; border-radius:7px; font-size:.82rem; color:#334155; background:#f8fafc; outline:none; min-width:160px; }
.filter-select:focus { border-color:#7c3aed; }
.btn-filter { padding:.45rem 1rem; background:#7c3aed; color:#fff; border:none; border-radius:7px; font-size:.82rem; font-weight:600; cursor:pointer; }
.btn-filter:hover { background:#6d28d9; }
.btn-reset { padding:.45rem 1rem; background:#f1f5f9; color:#475569; border:none; border-radius:7px; font-size:.82rem; font-weight:600; text-decoration:none; }

/* Groupe agent */
.agent-block { margin-bottom:1.5rem; }
.agent-block-header {
    display:flex; align-items:center; gap:.7rem;
    background:linear-gradient(135deg,#ede9fe,#f5f3ff);
    border:1px solid #ddd6fe; border-radius:12px;
    padding:.7rem 1rem; margin-bottom:.6rem;
}
.agent-block-avatar { width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.05rem; flex-shrink:0; color:#fff; }
.agent-block-name { font-family:'Space Grotesk',sans-serif; font-weight:700; color:#5b21b6; font-size:.95rem; }
.agent-block-code { display:inline-flex; align-items:center; gap:.3rem; background:#1E1B4B; color:#C4B5FD; font-size:.68rem; font-weight:700; padding:.18rem .55rem; border-radius:999px; border:1px solid #4C1D95; }
.agent-block-count { margin-left:auto; font-size:.74rem; font-weight:700; background:#ede9fe; color:#6d28d9; padding:.25rem .65rem; border-radius:999px; }

.empty-state { text-align:center; padding:2.5rem; color:#94a3b8; background:#fff; border-radius:12px; border:1px solid #e2e8f0; }

/* Lignes compactes — remplace le _card lourd */
.task-list { display:flex; flex-direction:column; gap:0; }
.task-compact {
    display:flex; align-items:center; gap:.75rem;
    padding:.55rem .85rem;
    background:#fff;
    border-bottom:1px solid #F1F5F9;
    text-decoration:none; color:inherit;
    transition:background .12s;
}
.task-compact:first-child { border-radius:10px 10px 0 0; }
.task-compact:last-child  { border-bottom:none; border-radius:0 0 10px 10px; }
.task-compact:hover { background:#F8FAFC; }

.tc-rail { width:3px; height:32px; border-radius:999px; flex-shrink:0; background:var(--collab-color,#6D28D9); }
.tc-body { flex:1; min-width:0; }
.tc-titre { font-size:.85rem; font-weight:600; color:#0F172A; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.tc-meta  { font-size:.71rem; color:#94A3B8; margin-top:.1rem; }
.tc-badges { display:flex; align-items:center; gap:.35rem; flex-shrink:0; }

/* Badges statut compacts */
.bst { font-size:.65rem; font-weight:700; padding:.12rem .45rem; border-radius:999px; white-space:nowrap; }
.bst-nouveau  { background:#EFF6FF; color:#1E40AF; }
.bst-cours    { background:#FEF9C3; color:#92400E; }
.bst-attente  { background:#FEF3C7; color:#B45309; }
.bst-arret    { background:#FEE2E2; color:#B91C1C; }
.bst-urgente  { background:#FEE2E2; color:#B91C1C; }
.bst-haute    { background:#FEF3C7; color:#92400E; }
.bst-normale  { background:#EFF6FF; color:#1E40AF; }
.bst-basse    { background:#F1F5F9; color:#475569; }
</style>
@endpush

@section('content')
<div class="agents-hero">
    <div>
        <div class="agents-hero-title">🤖 Tâches des agents IA</div>
        <div class="agents-hero-sub">Tâches actives par agent — {{ $kpis['total'] }} en cours · terminées dans <a href="{{ route('taches.archives') }}" style="color:rgba(255,255,255,.7);text-underline-offset:3px">l'historique</a></div>
    </div>
    <a href="{{ route('taches.index', ['createur' => 'agent_ia']) }}" class="btn-filter" style="text-decoration:none">Voir dans les tâches →</a>
</div>

{{-- KPIs --}}
<div class="kpi-grid">
    <div class="kpi-card"><div class="kpi-val">{{ $kpis['total'] }}</div><div class="kpi-label">Tâches actives</div></div>
    <div class="kpi-card"><div class="kpi-val">{{ $kpis['agents'] }}</div><div class="kpi-label">Agents mobilisés</div></div>
    <div class="kpi-card"><div class="kpi-val">{{ $kpis['en_cours'] }}</div><div class="kpi-label">En cours</div></div>
    <div class="kpi-card"><div class="kpi-val">{{ $kpis['terminees'] }}</div><div class="kpi-label">Terminées</div></div>
</div>

{{-- Filtres --}}
<form method="GET" action="{{ route('agents.taches') }}" class="filters-bar">
    <div class="filter-group">
        <label class="filter-label">Agent</label>
        <select name="agent_id" class="filter-select">
            <option value="">Tous les agents</option>
            @foreach($agentsAvecTaches as $a)
            <option value="{{ $a->id }}" {{ request('agent_id') == $a->id ? 'selected' : '' }}>{{ $a->nom_complet }} ({{ $a->agent_code }})</option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <label class="filter-label">Statut</label>
        <select name="statut" class="filter-select">
            <option value="">Tous</option>
            @foreach(['nouveau'=>'Nouveau','en_cours'=>'En cours','en_attente'=>'En attente','en_arret'=>'En arrêt','termine'=>'Terminé'] as $val=>$lib)
            <option value="{{ $val }}" {{ request('statut') === $val ? 'selected' : '' }}>{{ $lib }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn-filter">Filtrer</button>
    @if(request()->hasAny(['agent_id','statut']))
    <a href="{{ route('agents.taches') }}" class="btn-reset">Réinitialiser</a>
    @endif
</form>

{{-- Liste groupée par agent créateur --}}
@if($taches->isEmpty())
<div class="empty-state">
    <div style="font-size:2.5rem;margin-bottom:.5rem">🤖</div>
    <div style="font-weight:600;color:#475569">Aucune tâche créée par un agent IA</div>
    <div style="font-size:.875rem;margin-top:.25rem">Les tâches créées par les agents via le MCP apparaîtront ici.</div>
</div>
@else
@php
    // Groupé par agent RESPONSABLE (l'exécutant), trié par rang hiérarchique métier
    $parAgent = $taches
        ->sortBy(fn($t) => optional($t->responsables->firstWhere('type_compte','agent_ia'))->rangHierarchique() ?? 99)
        ->groupBy(fn($t) => optional($t->responsables->firstWhere('type_compte','agent_ia'))->id);
@endphp
@foreach($parAgent as $agentId => $groupe)
@php
    $agent = $groupe->first()->responsables->firstWhere('type_compte','agent_ia');
    $couleur = $agent?->agent_couleur ?? '#6D28D9';
@endphp
<div class="agent-block">
    <div class="agent-block-header">
        <div class="agent-block-avatar" style="background:{{ $couleur }}">🤖</div>
        <span class="agent-block-name">{{ $agent?->nom_complet ?? 'Agent IA' }}</span>
        <span class="agent-block-code"><i class="bi bi-cpu"></i> {{ $agent?->agent_code }}</span>
        <span class="agent-block-count">{{ $groupe->count() }} tâche{{ $groupe->count() > 1 ? 's' : '' }} à exécuter</span>
    </div>
    <div class="task-list" style="--collab-color:{{ $couleur }}">
        @foreach($groupe->sortBy('statut') as $tache)
        @php
            $stClass = match($tache->statut) {
                'en_cours'  => 'bst-cours',
                'en_attente'=> 'bst-attente',
                'en_arret'  => 'bst-arret',
                default     => 'bst-nouveau',
            };
            $prioClass = match($tache->priorite) {
                'urgente' => 'bst-urgente',
                'haute'   => 'bst-haute',
                'basse'   => 'bst-basse',
                default   => 'bst-normale',
            };
            $stLib = match($tache->statut) {
                'nouveau'   => 'Nouveau',
                'en_cours'  => 'En cours',
                'en_attente'=> 'En attente',
                'en_arret'  => 'En arrêt',
                default     => ucfirst($tache->statut),
            };
            $stDone = $tache->sousTaches->where('termine', true)->count();
            $stTotal = $tache->sousTaches->count();
        @endphp
        <a href="{{ route('taches.show', $tache) }}" class="task-compact">
            <div class="tc-rail"></div>
            <div class="tc-body">
                <div class="tc-titre">{{ $tache->titre }}</div>
                <div class="tc-meta">
                    {{ $tache->site?->nom ?? '—' }}
                    @if($tache->projet) · {{ $tache->projet }} @endif
                    @if($stTotal > 0) · {{ $stDone }}/{{ $stTotal }} sous-tâches @endif
                    @if($tache->date_echeance) · {{ $tache->date_echeance->format('d/m/Y') }} @endif
                </div>
            </div>
            <div class="tc-badges">
                <span class="bst {{ $stClass }}">{{ $stLib }}</span>
                <span class="bst {{ $prioClass }}">{{ ucfirst($tache->priorite) }}</span>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endforeach
@endif
@endsection

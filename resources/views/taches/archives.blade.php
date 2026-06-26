@extends('layouts.app')
@section('title', 'Historique & Archives')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
/* ── Page header ── */
.page-header {
    display:flex;align-items:flex-start;justify-content:space-between;
    gap:1rem;flex-wrap:wrap;
    background:linear-gradient(135deg,#001f3f 0%,#003366 60%,#002244 100%);
    border-radius:14px;padding:1.25rem 1.5rem 1rem;
    box-shadow:0 4px 20px rgba(0,0,0,.15);
    position:relative;overflow:hidden;margin-bottom:1.25rem;
}
.page-header::before {
    content:'';position:absolute;inset:0;
    background-image:radial-gradient(circle,rgba(255,255,255,.04) 1px,transparent 1px);
    background-size:20px 20px;pointer-events:none;
}
.page-header::after {
    content:'';position:absolute;bottom:-30px;right:-30px;
    width:150px;height:150px;border-radius:50%;
    background:radial-gradient(circle,rgba(204,85,0,.1) 0%,transparent 70%);
    pointer-events:none;
}
.page-header h1 {
    font-family:'Space Grotesk',sans-serif;font-size:1.3rem;font-weight:700;
    color:#fff;letter-spacing:-.025em;position:relative;z-index:1;
}
.page-header p { color:rgba(255,255,255,.5);font-size:.82rem;position:relative;z-index:1;margin-top:.15rem; }
.btn-back {
    display:inline-flex;align-items:center;gap:.4rem;
    background:rgba(255,255,255,.1);color:rgba(255,255,255,.85);
    padding:.45rem .9rem;border-radius:8px;text-decoration:none;font-size:.84rem;font-weight:600;
    border:1px solid rgba(255,255,255,.18);transition:all .15s;position:relative;z-index:1;white-space:nowrap;
}
.btn-back:hover { background:rgba(255,255,255,.18);color:#fff; }

/* ── Stats cards ── */
.stats-row {
    display:flex;gap:.75rem;flex-wrap:wrap;
    margin-top:1rem;position:relative;z-index:1;
}
.stat-card {
    background:rgba(255,255,255,.10);
    border:1px solid rgba(255,255,255,.15);
    border-radius:10px;padding:.75rem 1rem;flex:1;min-width:110px;
}
.stat-card.stat-mois   { border-top:2px solid #22C55E; }
.stat-card.stat-projet { border-top:2px solid #CC5500; }
.stat-card.stat-duree  { border-top:2px solid rgba(255,255,255,.3); }
.stat-value {
    font-family:'Space Grotesk',sans-serif;font-size:1.55rem;font-weight:800;
    color:#fff;line-height:1;
}
.stat-label { font-size:.68rem;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.07em;margin-top:.2rem; }
.stat-sub   { font-size:.72rem;color:rgba(255,255,255,.65);margin-top:.15rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }

/* ── Filtres ── */
.filters-bar {
    background:#fff;border-radius:12px;border:1px solid var(--slate-200);
    padding:1rem;margin-bottom:1.25rem;display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end;
}
.filter-group { display:flex;flex-direction:column;gap:.3rem; }
.filter-label { font-size:.75rem;font-weight:600;color:var(--slate-500);text-transform:uppercase;letter-spacing:.04em; }
.filter-input {
    padding:.45rem .75rem;border:1.5px solid var(--slate-200);border-radius:7px;
    font-size:.85rem;color:var(--slate-700);background:var(--slate-50);outline:none;
}
.filter-input:focus { border-color:var(--kt-navy); }
.btn { display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;border-radius:7px;font-size:.875rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;transition:all .15s; }
.btn-primary { background:var(--kt-navy);color:#fff; }
.btn-primary:hover { background:#002244; }
.btn-ghost { background:none;color:var(--slate-600);border:1px solid var(--slate-200); }
.btn-ghost:hover { background:var(--slate-50); }

/* ── Onglets ── */
.hist-tabs { display:flex;gap:0;border-bottom:2px solid var(--slate-200);margin-bottom:1.5rem;overflow-x:auto; }
.hist-tab {
    padding:.7rem 1.25rem;font-family:'Space Grotesk',sans-serif;font-size:.84rem;font-weight:700;
    color:var(--slate-500);border-bottom:2px solid transparent;margin-bottom:-2px;
    cursor:pointer;transition:all .15s;display:flex;align-items:center;gap:.4rem;white-space:nowrap;
    background:none;border-top:none;border-left:none;border-right:none;
}
.hist-tab:hover { color:var(--kt-navy); }
.hist-tab.active { color:var(--kt-navy);border-bottom-color:var(--kt-navy);background:linear-gradient(to bottom,transparent,rgba(0,51,102,.04)); }
.hist-tab .tab-count { background:var(--slate-100);color:var(--slate-500);border-radius:999px;font-size:.68rem;font-weight:800;padding:.1rem .45rem; }
.hist-tab.active .tab-count { background:#DBEAFE;color:#1D4ED8; }
.hist-panel { display:none; }
.hist-panel.active { display:block; }

/* ── Badges état ── */
.badge-termine {
    background:#DCFCE7;color:#15803D;border:1px solid #BBF7D0;
    font-size:.65rem;font-weight:700;padding:.2rem .6rem;border-radius:999px;white-space:nowrap;
}
.badge-archive {
    background:#F1F5F9;color:#475569;border:1px solid #CBD5E1;
    font-size:.65rem;font-weight:700;padding:.2rem .6rem;border-radius:999px;white-space:nowrap;
}

/* ── Row historique ── */
.hist-row {
    background:#FAFAFA;border-radius:10px;border:1px solid var(--slate-200);
    border-left:4px solid var(--proj-color,var(--slate-300));
    padding:.85rem 1rem;display:flex;align-items:flex-start;gap:.85rem;
    transition:box-shadow .15s;margin-bottom:.5rem;
}
.hist-row:hover { box-shadow:0 4px 16px rgba(0,0,0,.08);background:#fff; }
.hist-row-body { flex:1;min-width:0; }
.hist-row-title {
    font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:.9rem;
    color:var(--kt-navy);
    display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
}
.hist-row-meta { display:flex;gap:.75rem;flex-wrap:wrap;font-size:.75rem;color:var(--slate-500);margin-top:.3rem;align-items:center; }
.hist-row-actions { display:flex;gap:.35rem;align-items:center;flex-shrink:0; }
.btn-sm { padding:.3rem .65rem;font-size:.78rem;border-radius:7px; }
.btn-view { background:var(--slate-100);color:var(--slate-700);border:1px solid var(--slate-200); }
.btn-view:hover { background:#DBEAFE;color:#1D4ED8;border-color:#BFDBFE; }
.btn-restore { background:var(--slate-100);color:var(--slate-700);border:1px solid var(--slate-200); }
.btn-restore:hover { background:#FEF9C3;color:#854D0E;border-color:#FDE047; }

/* ── Accordion projets ── */
.proj-group { margin-bottom:.85rem; }
.proj-group-header {
    display:flex;align-items:center;gap:.85rem;padding:.9rem 1.1rem;
    background:#fff;border-radius:12px;border:1px solid var(--slate-200);
    cursor:pointer;transition:all .15s;box-shadow:0 1px 4px rgba(0,0,0,.05);
    margin-bottom:0;
}
.proj-group-header:hover { box-shadow:0 4px 16px rgba(0,0,0,.09); }
.proj-icon {
    width:36px;height:36px;border-radius:9px;
    background:linear-gradient(135deg,#001f3f,#003366);
    display:flex;align-items:center;justify-content:center;
    font-size:.95rem;flex-shrink:0;
}
.proj-name { font-family:'Space Grotesk',sans-serif;font-weight:700;color:var(--kt-navy);font-size:.93rem;flex:1;min-width:0; }
.proj-repartition { display:flex;gap:.35rem;align-items:center;flex-shrink:0; }
.proj-repartition .r-termine { background:#DCFCE7;color:#15803D;border-radius:999px;font-size:.68rem;font-weight:700;padding:.15rem .55rem;white-space:nowrap; }
.proj-repartition .r-archive { background:#F1F5F9;color:#475569;border-radius:999px;font-size:.68rem;font-weight:700;padding:.15rem .55rem;white-space:nowrap; }
.proj-toggle { color:var(--slate-400);font-size:.72rem;flex-shrink:0; }
.proj-body { padding:.6rem 0 0 0;display:none; }
.proj-body.open { display:block; }

/* ── Timeline ── */
.timeline-wrapper { position:relative;padding-left:28px; }
.timeline-wrapper::before {
    content:'';position:absolute;left:10px;top:12px;bottom:0;
    width:2px;background:linear-gradient(to bottom,#003366,rgba(148,163,184,.3));
}
.timeline-month { margin-bottom:1.5rem;position:relative; }
.timeline-dot {
    position:absolute;left:-24px;top:4px;
    width:14px;height:14px;border-radius:50%;
    background:var(--kt-navy);border:2px solid #fff;
    box-shadow:0 0 0 3px rgba(0,51,102,.15);flex-shrink:0;
}
.timeline-month-header {
    display:flex;align-items:center;gap:.75rem;
    margin-bottom:.75rem;cursor:pointer;user-select:none;
}
.timeline-month-label {
    font-family:'Space Grotesk',sans-serif;font-weight:700;color:var(--kt-navy);font-size:.9rem;
}
.timeline-month-count {
    background:var(--slate-100);color:var(--slate-600);
    padding:.15rem .5rem;border-radius:999px;font-size:.72rem;font-weight:700;
}
.timeline-month-toggle { color:var(--slate-400);font-size:.72rem; }
.timeline-month-body { display:block; }
.timeline-month-body.closed { display:none; }

/* ── Fil d'activité ── */
.activity-week { margin-bottom:1.5rem; }
.activity-week-header {
    font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:.78rem;
    text-transform:uppercase;letter-spacing:.07em;color:var(--slate-500);
    margin-bottom:.6rem;padding-bottom:.4rem;border-bottom:1.5px solid var(--slate-100);
}
.activity-item {
    background:#fff;border-radius:10px;border:1px solid var(--slate-200);
    padding:.75rem 1rem;margin-bottom:.45rem;display:flex;gap:.75rem;align-items:flex-start;
}
.activity-avatar {
    width:30px;height:30px;border-radius:50%;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;
    font-size:.8rem;font-weight:700;color:#fff;background:var(--kt-navy);
}
.activity-avatar.is-agent { background:#5B21B6; }
.activity-body { flex:1;min-width:0; }
.activity-who { font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:.82rem;color:var(--kt-navy); }
.activity-when { font-size:.72rem;color:var(--slate-400);margin-left:.5rem; }
.activity-text { font-size:.82rem;color:var(--slate-600);margin-top:.15rem; }
.activity-link { font-size:.75rem;color:var(--slate-400);margin-top:.1rem; }

/* ── Empty states ── */
.empty-hist {
    text-align:center;padding:3rem 1.5rem;background:#fff;
    border-radius:12px;border:1px solid var(--slate-200);
}
.empty-hist .ei { font-size:2.5rem;margin-bottom:.6rem; }
.empty-hist h3 { font-family:'Space Grotesk',sans-serif;font-weight:700;color:var(--slate-600);font-size:.95rem;margin-bottom:.35rem; }
.empty-hist p  { font-size:.82rem;color:var(--slate-400); }

@media (max-width:640px) {
    .stats-row { gap:.5rem; }
    .stat-card { min-width:calc(50% - .25rem);flex:none; }
    .stat-value { font-size:1.25rem; }
    .timeline-wrapper { padding-left:20px; }
    .timeline-dot { width:10px;height:10px;left:-17px; }
    .hist-row-actions { flex-direction:column; }
    .hist-tabs { gap:0; }
    .hist-tab { padding:.55rem .85rem;font-size:.78rem; }
}
</style>
@endpush

@section('content')

{{-- ── Header ── --}}
<div class="page-header">
    <div style="flex:1;min-width:0;position:relative;z-index:1">
        <h1><i class="bi bi-clock-history" style="margin-right:.5rem;opacity:.8"></i>Historique &amp; Archives</h1>
        <p>Tâches terminées et archivées — {{ $total }} entrée(s) au total</p>
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-value">{{ $total }}</div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-card stat-mois">
                <div class="stat-value">{{ $ceMois }}</div>
                <div class="stat-label">Ce mois</div>
            </div>
            <div class="stat-card stat-projet">
                <div class="stat-value">{{ $topProjetNb }}</div>
                <div class="stat-label">Top projet</div>
                @if($topProjet)
                <div class="stat-sub" title="{{ $topProjet }}">{{ Str::limit($topProjet, 18) }}</div>
                @endif
            </div>
        </div>
    </div>
    <a href="{{ route('taches.index') }}" class="btn-back">
        <i class="bi bi-arrow-left"></i> Tâches actives
    </a>
</div>

{{-- ── Filtres ── --}}
<form method="GET" action="{{ route('taches.archives') }}" class="filters-bar">
    <input type="hidden" name="vue" value="{{ $vue }}">
    <div class="filter-group" style="flex:1;min-width:150px">
        <label class="filter-label">Recherche</label>
        <input type="text" name="q" class="filter-input" placeholder="Titre ou description..." value="{{ request('q') }}">
    </div>
    <div class="filter-group">
        <label class="filter-label">Projet</label>
        <select name="projet" class="filter-input">
            <option value="">Tous</option>
            @foreach($projets as $p)
            <option value="{{ $p }}" {{ request('projet') === $p ? 'selected' : '' }}>{{ $p }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <label class="filter-label">Période</label>
        <select name="periode" class="filter-input">
            <option value="">Tout</option>
            <option value="semaine"   {{ request('periode') === 'semaine'   ? 'selected' : '' }}>Cette semaine</option>
            <option value="mois"      {{ request('periode') === 'mois'      ? 'selected' : '' }}>Ce mois</option>
            <option value="trimestre" {{ request('periode') === 'trimestre' ? 'selected' : '' }}>Ce trimestre</option>
            <option value="annee"     {{ request('periode') === 'annee'     ? 'selected' : '' }}>Cette année</option>
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
        @if(request()->hasAny(['q','projet','periode','responsable_id']))
        <a href="{{ route('taches.archives', ['vue' => $vue]) }}" class="btn btn-ghost">Réinitialiser</a>
        @endif
    </div>
</form>

{{-- ── Onglets ── --}}
@if($taches->isEmpty())
<div class="empty-hist">
    <div class="ei">📁</div>
    <h3>Aucune tâche dans l'historique</h3>
    <p>Les tâches terminées et archivées apparaîtront ici.</p>
</div>
@else

<div class="hist-tabs" id="histTabs">
    @php
        $countProjet  = $taches->whereNotNull('projet')->count() ?: $taches->count();
        $nbTerminees  = $taches->filter(fn($t) => $t->statut === 'termine' && ! $t->archived_at)->count();
        $nbArchivees  = $taches->filter(fn($t) => $t->archived_at)->count();
    @endphp
    <button type="button" class="hist-tab {{ $vue === 'projet'  ? 'active' : '' }}" data-panel="panel-projet">
        📂 Par Projet <span class="tab-count">{{ $taches->groupBy('projet')->count() }}</span>
    </button>
    <button type="button" class="hist-tab {{ $vue === 'periode' ? 'active' : '' }}" data-panel="panel-periode">
        📅 Par Période <span class="tab-count">{{ $taches->count() }}</span>
    </button>
    <button type="button" class="hist-tab {{ $vue === 'actions' ? 'active' : '' }}" data-panel="panel-actions">
        ⚡ Par Actions <span class="tab-count">{{ $taches->sum(fn($t) => $t->actionsSuivi->count()) }}</span>
    </button>
</div>

{{-- ════════ PANNEAU : Par Projet ════════ --}}
<div class="hist-panel {{ $vue === 'projet' ? 'active' : '' }}" id="panel-projet">
@php
    $parProjet = $taches->groupBy(fn($t) => $t->projet ?: '(Sans projet)')->sortKeys();
@endphp

@forelse($parProjet as $nomProjet => $groupTaches)
@php
    $couleurProjet = \App\Models\Tache::couleurProjet($nomProjet === '(Sans projet)' ? null : $nomProjet);
    $nbT = $groupTaches->filter(fn($t) => $t->statut === 'termine' && ! $t->archived_at)->count();
    $nbA = $groupTaches->filter(fn($t) => $t->archived_at)->count();
@endphp
<div class="proj-group">
    <div class="proj-group-header" onclick="toggleProj(this)" style="--proj-color:{{ $couleurProjet }};border-left:4px solid {{ $couleurProjet }}">
        <div class="proj-icon">📂</div>
        <span class="proj-name">{{ $nomProjet }}</span>
        <div class="proj-repartition">
            @if($nbT) <span class="r-termine">✅ {{ $nbT }} terminée{{ $nbT > 1 ? 's' : '' }}</span> @endif
            @if($nbA) <span class="r-archive">📦 {{ $nbA }} archivée{{ $nbA > 1 ? 's' : '' }}</span> @endif
        </div>
        <span class="proj-toggle">▶</span>
    </div>
    <div class="proj-body">
        @foreach($groupTaches->sortByDesc('updated_at') as $tache)
        @include('taches._hist_row', ['tache' => $tache, 'couleurProjet' => $couleurProjet])
        @endforeach
    </div>
</div>
@empty
<div class="empty-hist"><div class="ei">📂</div><h3>Aucune tâche</h3><p>Essayez de modifier les filtres.</p></div>
@endforelse
</div>

{{-- ════════ PANNEAU : Par Période ════════ --}}
<div class="hist-panel {{ $vue === 'periode' ? 'active' : '' }}" id="panel-periode">
@php
    $parMois = $taches->groupBy(fn($t) => $t->updated_at?->format('Y-m') ?? '0000-00')
                      ->sortKeysDesc();
@endphp

@if($parMois->isEmpty())
<div class="empty-hist"><div class="ei">📅</div><h3>Aucune tâche</h3><p>Essayez de modifier les filtres.</p></div>
@else
<div class="timeline-wrapper">
@foreach($parMois as $moisKey => $groupTaches)
@php
    $moisLabel = $moisKey !== '0000-00'
        ? \Carbon\Carbon::createFromFormat('Y-m', $moisKey)->locale('fr')->isoFormat('MMMM YYYY')
        : 'Date inconnue';
    $couleurMois = \App\Models\Tache::couleurProjet($moisKey);
@endphp
<div class="timeline-month">
    <div class="timeline-dot"></div>
    <div class="timeline-month-header" onclick="toggleTimeline(this)">
        <span class="timeline-month-label">{{ ucfirst($moisLabel) }}</span>
        <span class="timeline-month-count">{{ $groupTaches->count() }} tâche{{ $groupTaches->count() > 1 ? 's' : '' }}</span>
        <span class="timeline-month-toggle">▼</span>
    </div>
    <div class="timeline-month-body">
        @foreach($groupTaches->sortByDesc('updated_at') as $tache)
        @include('taches._hist_row', ['tache' => $tache, 'couleurProjet' => \App\Models\Tache::couleurProjet($tache->projet)])
        @endforeach
    </div>
</div>
@endforeach
</div>
@endif
</div>

{{-- ════════ PANNEAU : Par Actions ════════ --}}
<div class="hist-panel {{ $vue === 'actions' ? 'active' : '' }}" id="panel-actions">
@php
    $touteActions = $taches->flatMap(fn($t) => $t->actionsSuivi->map(fn($a) => ['action' => $a, 'tache' => $t]))
                           ->sortByDesc(fn($x) => $x['action']->created_at);
    $parSemaine = $touteActions->groupBy(fn($x) => $x['action']->created_at?->startOfWeek()->format('Y-m-d'));
@endphp

@if($touteActions->isEmpty())
<div class="empty-hist">
    <div class="ei">⚡</div>
    <h3>Aucune action de suivi enregistrée</h3>
    <p>Les actions apparaissent dès qu'un collaborateur ou agent IA ajoute<br>un commentaire ou une mise à jour sur une tâche.</p>
</div>
@else
@foreach($parSemaine as $semaineDeb => $actions)
@php
    $dateLabel = $semaineDeb
        ? 'Semaine du ' . \Carbon\Carbon::parse($semaineDeb)->locale('fr')->isoFormat('D MMMM YYYY')
        : 'Date inconnue';
@endphp
<div class="activity-week">
    <div class="activity-week-header">{{ $dateLabel }}</div>
    @foreach($actions as $x)
    @php
        $action  = $x['action'];
        $tache   = $x['tache'];
        $auteur  = $action->user;
        $isAgent = optional($auteur)->type_compte === 'agent_ia';
        $initiales = $auteur ? strtoupper(mb_substr($auteur->prenom ?? '', 0, 1) . mb_substr($auteur->nom ?? '', 0, 1)) : '?';
    @endphp
    <div class="activity-item">
        <div class="activity-avatar {{ $isAgent ? 'is-agent' : '' }}">
            {{ $isAgent ? '🤖' : $initiales }}
        </div>
        <div class="activity-body">
            <div>
                <span class="activity-who">{{ optional($auteur)->nom_complet ?? 'Inconnu' }}</span>
                <span class="activity-when">{{ $action->created_at?->format('d/m/Y H\hi') }}</span>
            </div>
            <div class="activity-text">{{ $action->contenu ?? $action->description ?? '(action enregistrée)' }}</div>
            <div class="activity-link">
                → <a href="{{ route('taches.show', $tache) }}" style="color:var(--slate-400);text-decoration:none;font-size:.74rem">{{ $tache->titre }}</a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endforeach
@endif
</div>

@endif

@push('scripts')
<script>
// ── Onglets
document.querySelectorAll('.hist-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.hist-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.hist-panel').forEach(p => p.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById(tab.dataset.panel)?.classList.add('active');
        // Mettre à jour l'URL sans recharger
        const url = new URL(window.location);
        url.searchParams.set('vue', tab.dataset.panel.replace('panel-', ''));
        window.history.replaceState({}, '', url);
    });
});

// ── Accordion projets
function toggleProj(header) {
    const body   = header.nextElementSibling;
    const toggle = header.querySelector('.proj-toggle');
    const isOpen = body.classList.contains('open');
    body.classList.toggle('open', !isOpen);
    toggle.textContent = isOpen ? '▶' : '▼';
}

// ── Timeline mois
function toggleTimeline(header) {
    const body   = header.nextElementSibling;
    const toggle = header.querySelector('.timeline-month-toggle');
    const isOpen = !body.classList.contains('closed');
    body.classList.toggle('closed', isOpen);
    toggle.textContent = isOpen ? '▶' : '▼';
}

// Replier les mois > 2 par défaut
document.addEventListener('DOMContentLoaded', () => {
    const mois = document.querySelectorAll('.timeline-month');
    mois.forEach((m, i) => {
        if (i >= 2) {
            const body   = m.querySelector('.timeline-month-body');
            const toggle = m.querySelector('.timeline-month-toggle');
            if (body && toggle) { body.classList.add('closed'); toggle.textContent = '▶'; }
        }
    });
});
</script>
@endpush
@endsection

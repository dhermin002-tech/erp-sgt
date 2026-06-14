@extends('layouts.app')
@section('title', 'Rapport général')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
.page-header {
    display:flex;align-items:center;justify-content:space-between;
    gap:1rem;flex-wrap:wrap;
    background:linear-gradient(135deg,#001f3f 0%,#003366 60%,#002244 100%);
    border-radius:14px;padding:1.25rem 1.5rem;
    box-shadow:0 4px 20px rgba(0,0,0,.15);
    position:relative;overflow:hidden;margin-bottom:1.25rem;
}
.page-header::before {
    content:'';position:absolute;inset:0;
    background-image:radial-gradient(circle,rgba(255,255,255,.04) 1px,transparent 1px);
    background-size:20px 20px;pointer-events:none;
}
.page-header h1 {
    font-family:'Space Grotesk',sans-serif;font-size:1.3rem;font-weight:700;
    color:#fff;letter-spacing:-.025em;position:relative;z-index:1;
}
.page-header p { color:rgba(255,255,255,.5);font-size:.82rem;position:relative;z-index:1; }

.btn { display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;border-radius:7px;font-size:.875rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;transition:all .15s; }
.btn-primary { background:var(--kt-navy);color:#fff; }
.btn-orange  { background:#CC5500;color:#fff;box-shadow:0 4px 14px rgba(204,85,0,.35);position:relative;z-index:1; }
.btn-orange:hover { background:#E06010;color:#fff; }
.btn-ghost   { background:none;color:var(--slate-600);border:1.5px solid var(--slate-200); }
.btn-ghost:hover { background:var(--slate-50); }
.btn-sm { padding:.3rem .65rem;font-size:.8rem; }

.filters-bar {
    background:var(--white);border-radius:12px;border:1px solid var(--slate-200);
    padding:1rem;margin-bottom:1.25rem;display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end;
}
.filter-group { display:flex;flex-direction:column;gap:.3rem; }
.filter-label { font-size:.75rem;font-weight:600;color:var(--slate-500);text-transform:uppercase;letter-spacing:.04em; }
.filter-input { padding:.45rem .75rem;border:1.5px solid var(--slate-200);border-radius:7px;font-size:.85rem;color:var(--slate-700);background:var(--slate-50);outline:none; }
.filter-input:focus { border-color:var(--kt-navy); }

.section-sep { display:flex;align-items:center;gap:.75rem;margin:1.5rem 0 .85rem; }
.section-sep-line { flex:1;height:1.5px;background:linear-gradient(90deg,transparent,var(--slate-200),var(--slate-200),transparent); }
.section-sep-label {
    display:flex;align-items:center;gap:.5rem;
    font-family:'Space Grotesk',sans-serif;font-size:.72rem;font-weight:700;
    letter-spacing:.08em;text-transform:uppercase;white-space:nowrap;
    padding:.3rem .85rem;border-radius:999px;
    background:var(--slate-50);color:var(--slate-600);border:1.5px solid var(--slate-200);
}
.section-sep.navy .section-sep-line { background:linear-gradient(90deg,transparent,#BFDBFE,#BFDBFE,transparent); }
.section-sep.navy .section-sep-label { background:#EFF6FF;color:#1D4ED8;border-color:#BFDBFE; }

.kpi-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(175px,1fr));gap:.85rem;margin-bottom:1.5rem; }
.kpi-card {
    background:var(--white);border-radius:12px;border:1px solid var(--slate-200);
    padding:1rem 1.1rem;box-shadow:0 1px 4px rgba(0,0,0,.05);
    position:relative;overflow:hidden;
}
.kpi-card::before {
    content:'';position:absolute;top:0;left:0;right:0;height:3px;
    background:var(--kpi-color,var(--kt-navy));
}
.kpi-avatar {
    width:36px;height:36px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-family:'Space Grotesk',sans-serif;font-size:.78rem;font-weight:700;
    color:#fff;margin-bottom:.6rem;background:var(--kpi-color,var(--kt-navy));
}
.kpi-name { font-family:'Space Grotesk',sans-serif;font-size:.85rem;font-weight:700;color:var(--kt-navy);margin-bottom:.1rem; }
.kpi-role { font-size:.72rem;color:var(--slate-400);margin-bottom:.6rem; }
.kpi-stats { display:flex;gap:.75rem;margin-bottom:.5rem; }
.kpi-stat { display:flex;flex-direction:column;align-items:center; }
.kpi-stat-val { font-family:'Space Grotesk',sans-serif;font-size:1.1rem;font-weight:700;color:var(--kt-navy); }
.kpi-stat-lbl { font-size:.65rem;color:var(--slate-400);text-align:center; }
.kpi-bar { height:5px;border-radius:999px;background:var(--slate-100);overflow:hidden; }
.kpi-bar-fill { height:100%;border-radius:999px;background:var(--kpi-color,var(--kt-navy)); }
.kpi-taux { font-size:.72rem;font-weight:700;color:var(--slate-500);margin-top:.3rem; }

.table-wrap { background:var(--white);border-radius:12px;border:1px solid var(--slate-200);overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.05);margin-bottom:1.5rem; }
.table-responsive { overflow-x:auto; }
table.rapport-table { width:100%;border-collapse:collapse; }
table.rapport-table thead {
    position:sticky;top:0;z-index:2;
}
table.rapport-table thead th {
    padding:.75rem .9rem;text-align:left;
    background:#003366;
    color:rgba(255,255,255,.9);
    font-family:'Space Grotesk',sans-serif;
    font-size:.72rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;
    white-space:nowrap;border-bottom:none;
}
table.rapport-table thead th:first-child { border-radius:0; }
table.rapport-table tbody td {
    padding:.75rem .9rem;font-size:.855rem;color:var(--slate-700);
    border-bottom:1px solid var(--slate-100);vertical-align:middle;
}
table.rapport-table tbody tr:nth-child(even) { background:#F8FAFF; }
table.rapport-table tbody tr:last-child td { border-bottom:none; }
table.rapport-table tbody tr:hover { background:#EFF6FF; }
.cell-titre { font-weight:600;color:var(--kt-navy);max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;text-decoration:none; }
.cell-titre:hover { text-decoration:underline; }
.cell-prog { display:flex;align-items:center;gap:.5rem; }
.prog-track { flex:1;min-width:60px;height:5px;border-radius:999px;background:var(--slate-100); }
.prog-fill  { height:100%;border-radius:999px;background:var(--kt-navy); }
.prog-val   { font-size:.72rem;font-weight:700;color:var(--slate-500); }
.empty-row td { text-align:center;padding:2rem;color:var(--slate-400);font-size:.875rem; }

.print-header { display:none; }
.print-footer { text-align:center;font-size:.75rem;color:#888;padding-top:1rem;border-top:1px solid #eee;margin-top:1.5rem; }

@media print {
    .no-print,.filters-bar { display:none !important; }
    .page-header { background:#003366 !important;-webkit-print-color-adjust:exact;print-color-adjust:exact;border-radius:0;margin:0 0 1rem; }
    .print-header { display:block;padding:.5rem 0 1rem;border-bottom:2px solid #003366;margin-bottom:1rem; }
    .print-header h2 { font-family:'Space Grotesk',sans-serif;font-size:1.1rem;color:#003366; }
    .print-header p  { font-size:.8rem;color:#666; }
    .kpi-card::before,.kpi-bar-fill,.prog-fill { -webkit-print-color-adjust:exact;print-color-adjust:exact; }
    .section-sep-line { display:none; }
    body,.app-content { background:#fff !important; }
    .table-wrap { box-shadow:none;border:1px solid #ddd; }
    @page { margin:1.5cm; }
}
</style>
@endpush

@section('content')
<div class="print-header">
    <h2>KAY TECHNOLOGIE GABON — SGT v1.0</h2>
    <p>Rapport généré le {{ now()->format('d/m/Y à H:i') }} par {{ auth()->user()->nom_complet }}</p>
</div>

<div class="page-header">
    <div>
        <h1><i class="bi bi-file-earmark-bar-graph" style="margin-right:.5rem"></i>Rapport général</h1>
        <p>{{ $tachesActives->count() }} tâche(s) active(s) · {{ $tachesTerminees->count() }} terminée(s)/archivée(s)</p>
    </div>
    <button onclick="window.print()" class="btn btn-orange no-print">
        <i class="bi bi-printer"></i> Imprimer / PDF
    </button>
</div>

<form method="GET" action="{{ route('rapports.index') }}" class="filters-bar no-print">
    <div class="filter-group">
        <label class="filter-label">Statut</label>
        <select name="statut" class="filter-input">
            <option value="">Tous les statuts</option>
            @foreach($statuts as $s)
            <option value="{{ $s }}" @selected(request('statut')===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <label class="filter-label">Site</label>
        <select name="site_id" class="filter-input">
            <option value="">Tous les sites</option>
            @foreach($sites as $site)
            <option value="{{ $site->id }}" @selected(request('site_id')==$site->id)>{{ $site->nom }}</option>
            @endforeach
        </select>
    </div>
    @if(auth()->user()->isManager())
    <div class="filter-group">
        <label class="filter-label">Responsable</label>
        <select name="responsable_id" class="filter-input">
            <option value="">Tous</option>
            @foreach($responsables as $r)
            <option value="{{ $r->id }}" @selected(request('responsable_id')==$r->id)>{{ $r->nom_complet }}</option>
            @endforeach
        </select>
    </div>
    @endif
    <button type="submit" class="btn btn-primary btn-sm" style="align-self:flex-end">
        <i class="bi bi-funnel"></i> Filtrer
    </button>
    @if(request()->hasAny(['statut','site_id','responsable_id']))
    <a href="{{ route('rapports.index') }}" class="btn btn-ghost btn-sm" style="align-self:flex-end">
        <i class="bi bi-x"></i> Réinitialiser
    </a>
    @endif
</form>

@php
$kpiColors = ['#003366','#CC5500','#7C3AED','#059669','#DC2626','#D97706','#0891B2','#BE185D'];
[$kpiHumains, $kpiAgents] = $kpiResponsables->partition(fn($k) => ($k['user']->type_compte ?? 'humain') !== 'agent_ia');
@endphp

<style>
.perf-chips { display:flex; gap:.5rem; flex-wrap:wrap; margin:1.1rem 0 .9rem; }
.perf-chip { display:inline-flex; align-items:center; gap:.4rem; padding:.5rem .95rem; border-radius:999px; border:1.5px solid #e2e8f0; background:#fff; font-family:'Space Grotesk',sans-serif; font-size:.82rem; font-weight:700; color:#64748b; cursor:pointer; transition:all .15s; }
.perf-chip:hover { border-color:#003366; color:#003366; }
.perf-chip.active { background:#003366; color:#fff; border-color:#003366; }
.perf-chip span { background:rgba(0,0,0,.08); border-radius:999px; padding:0 .4rem; font-size:.7rem; }
.perf-chip.active span { background:rgba(255,255,255,.25); }
.perf-chip[data-target="agents"] { border-color:#ddd6fe; color:#6d28d9; }
.perf-chip[data-target="agents"].active { background:#6d28d9; border-color:#6d28d9; color:#fff; }
.perf-section.is-hidden { display:none; }
</style>

{{-- Chips de filtre Performance --}}
<div class="perf-chips">
    <button type="button" class="perf-chip active" data-target="all" onclick="filtrePerf('all',this)">📊 Tous</button>
    <button type="button" class="perf-chip" data-target="collab" onclick="filtrePerf('collab',this)">👥 Collaborateurs <span>{{ $kpiHumains->count() }}</span></button>
    <button type="button" class="perf-chip" data-target="agents" onclick="filtrePerf('agents',this)">🤖 Agents IA <span>{{ $kpiAgents->count() }}</span></button>
</div>

{{-- Catégorie Collaborateurs --}}
<div class="perf-section" data-perf="collab">
    <div class="section-sep navy">
        <div class="section-sep-line"></div>
        <div class="section-sep-label"><i class="bi bi-people"></i>&nbsp;Collaborateurs ({{ $kpiHumains->count() }})</div>
        <div class="section-sep-line"></div>
    </div>
    <div class="kpi-grid">
        @foreach($kpiHumains->values() as $i => $kpi)
        @php $color = $kpiColors[$i % count($kpiColors)]; @endphp
        <div class="kpi-card" style="--kpi-color:{{ $color }}">
            <div class="kpi-avatar">{{ strtoupper(mb_substr($kpi['user']->prenom,0,1).mb_substr($kpi['user']->nom,0,1)) }}</div>
            <div class="kpi-name">{{ $kpi['user']->nom_complet }}</div>
            <div class="kpi-role">{{ ucfirst($kpi['user']->role) }}</div>
            <div class="kpi-stats">
                <div class="kpi-stat"><div class="kpi-stat-val">{{ $kpi['actives'] }}</div><div class="kpi-stat-lbl">Actives</div></div>
                <div class="kpi-stat"><div class="kpi-stat-val">{{ $kpi['terminees'] }}</div><div class="kpi-stat-lbl">Terminées</div></div>
                <div class="kpi-stat"><div class="kpi-stat-val">{{ $kpi['total'] }}</div><div class="kpi-stat-lbl">Total</div></div>
            </div>
            <div class="kpi-bar"><div class="kpi-bar-fill" style="width:{{ $kpi['taux'] }}%"></div></div>
            <div class="kpi-taux">{{ $kpi['taux'] }}% complété</div>
        </div>
        @endforeach
    </div>
</div>

{{-- Catégorie Agents IA --}}
@if($kpiAgents->isNotEmpty())
<div class="perf-section" data-perf="agents">
    <div class="section-sep" style="margin-top:1.5rem">
        <div class="section-sep-line" style="background:#ddd6fe"></div>
        <div class="section-sep-label" style="color:#6d28d9">🤖&nbsp;Agents IA ({{ $kpiAgents->count() }})</div>
        <div class="section-sep-line" style="background:#ddd6fe"></div>
    </div>
    <div class="kpi-grid">
        @foreach($kpiAgents->values() as $kpi)
        @php $color = $kpi['user']->agent_couleur ?? '#6d28d9'; @endphp
        <div class="kpi-card" style="--kpi-color:{{ $color }}">
            <div class="kpi-avatar" style="background:{{ $color }}">🤖</div>
            <div class="kpi-name">{{ $kpi['user']->nom_complet }}</div>
            <div class="kpi-role" style="color:#6d28d9">{{ $kpi['user']->agent_code }}</div>
            <div class="kpi-stats">
                <div class="kpi-stat"><div class="kpi-stat-val">{{ $kpi['actives'] }}</div><div class="kpi-stat-lbl">Actives</div></div>
                <div class="kpi-stat"><div class="kpi-stat-val">{{ $kpi['terminees'] }}</div><div class="kpi-stat-lbl">Terminées</div></div>
                <div class="kpi-stat"><div class="kpi-stat-val">{{ $kpi['total'] }}</div><div class="kpi-stat-lbl">Total</div></div>
            </div>
            <div class="kpi-bar"><div class="kpi-bar-fill" style="width:{{ $kpi['taux'] }}%"></div></div>
            <div class="kpi-taux">{{ $kpi['taux'] }}% complété</div>
        </div>
        @endforeach
    </div>
</div>
@endif

<script>
function filtrePerf(target, btn) {
    document.querySelectorAll('.perf-chip').forEach(c => c.classList.toggle('active', c === btn));
    document.querySelectorAll('.perf-section').forEach(s => {
        s.classList.toggle('is-hidden', !(target === 'all' || s.dataset.perf === target));
    });
}
</script>

<div class="section-sep">
    <div class="section-sep-line"></div>
    <div class="section-sep-label"><i class="bi bi-activity"></i>&nbsp;Tâches actives ({{ $tachesActives->count() }})</div>
    <div class="section-sep-line"></div>
</div>

<div class="table-wrap">
    <div class="table-responsive">
        <table class="rapport-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Responsable(s)</th>
                    <th>Site</th>
                    <th>Échéance</th>
                    <th>Statut</th>
                    <th>Progression</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tachesActives as $t)
                <tr>
                    <td><a href="{{ route('taches.show', $t) }}" class="cell-titre no-print">{{ $t->titre }}</a><span class="cell-titre" style="display:none">{{ $t->titre }}</span></td>
                    <td>{{ $t->responsables->map(fn($r) => $r->nom_complet)->implode(', ') ?: '—' }}</td>
                    <td>{{ $t->site?->nom ?? '—' }}</td>
                    <td>
                        @if($t->date_echeance)
                        <span style="{{ $t->estEnRetard() ? 'color:#DC2626;font-weight:600' : '' }}">{{ $t->date_echeance->format('d/m/Y') }}</span>
                        @else —
                        @endif
                    </td>
                    <td>@include('partials.badge_statut',['statut'=>$t->statut])</td>
                    <td>
                        <div class="cell-prog">
                            <div class="prog-track"><div class="prog-fill" style="width:{{ $t->progression }}%"></div></div>
                            <span class="prog-val">{{ $t->progression }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr class="empty-row"><td colspan="6"><i class="bi bi-inbox"></i> Aucune tâche active</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="section-sep">
    <div class="section-sep-line"></div>
    <div class="section-sep-label"><i class="bi bi-archive"></i>&nbsp;Terminées &amp; archivées ({{ $tachesTerminees->count() }})</div>
    <div class="section-sep-line"></div>
</div>

<div class="table-wrap">
    <div class="table-responsive">
        <table class="rapport-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Responsable(s)</th>
                    <th>Site</th>
                    <th>Statut</th>
                    <th>Clôturé le</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tachesTerminees as $t)
                <tr>
                    <td><a href="{{ route('taches.show', $t) }}" class="cell-titre no-print">{{ $t->titre }}</a><span class="cell-titre" style="display:none">{{ $t->titre }}</span></td>
                    <td>{{ $t->responsables->map(fn($r) => $r->nom_complet)->implode(', ') ?: '—' }}</td>
                    <td>{{ $t->site?->nom ?? '—' }}</td>
                    <td>@include('partials.badge_statut',['statut'=>$t->statut])</td>
                    <td>{{ ($t->archived_at ?? $t->updated_at)?->format('d/m/Y') ?? '—' }}</td>
                </tr>
                @empty
                <tr class="empty-row"><td colspan="5"><i class="bi bi-inbox"></i> Aucune tâche terminée ou archivée</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="print-footer">
    KayTechnologie Gabon — SGT v1.0 — Rapport généré le {{ now()->format('d/m/Y à H:i') }}
</div>
@endsection

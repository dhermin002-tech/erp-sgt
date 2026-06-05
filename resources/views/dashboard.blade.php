@extends('layouts.app')
@section('title', 'Tableau de bord')

@push('styles')
<style>
.kpi-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem }
.kpi-card { background:var(--white);border-radius:12px;padding:1.25rem;border:1px solid var(--slate-200);box-shadow:0 1px 4px rgba(0,0,0,.06) }
.kpi-label { font-size:.75rem;font-weight:700;color:var(--slate-500);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.35rem }
.kpi-value { font-size:2.2rem;font-weight:800;font-family:var(--font-display);line-height:1 }
.kpi-sub { font-size:.78rem;color:var(--slate-400);margin-top:.3rem }
.charts-grid { display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem }
.charts-grid-3 { display:grid;grid-template-columns:1fr;gap:1rem;margin-bottom:1.5rem }
.chart-card { background:var(--white);border-radius:12px;border:1px solid var(--slate-200);overflow:hidden }
.chart-header { padding:.85rem 1.25rem;border-bottom:1px solid var(--slate-100);display:flex;align-items:center;justify-content:space-between }
.chart-title { font-family:var(--font-display);font-size:.9rem;font-weight:700;color:var(--kt-navy) }
.chart-body { padding:1.25rem;position:relative }
.filters-bar { background:var(--white);border-radius:12px;border:1px solid var(--slate-200);padding:.875rem 1.25rem;margin-bottom:1.25rem;display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end }
.filter-group { display:flex;flex-direction:column;gap:.25rem }
.filter-label { font-size:.72rem;font-weight:700;color:var(--slate-500);text-transform:uppercase;letter-spacing:.04em }
.filter-select { padding:.4rem .75rem;border:1.5px solid var(--slate-200);border-radius:7px;font-size:.82rem;color:var(--slate-700);background:var(--slate-50);outline:none;min-width:140px }
.filter-select:focus { border-color:var(--kt-navy) }
.btn-filter { background:var(--kt-navy);color:#fff;padding:.45rem .9rem;border-radius:7px;border:none;font-size:.82rem;font-weight:600;cursor:pointer }
.quick-links { display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.75rem;margin-bottom:1.5rem }
.quick-link { border-radius:10px;padding:1rem 1.25rem;text-decoration:none;display:flex;align-items:center;gap:.75rem;transition:opacity .15s }
.quick-link:hover { opacity:.85 }
@media (max-width:768px) { .charts-grid { grid-template-columns:1fr } }
</style>
@endpush

@section('content')

{{-- En-tête --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.5rem">
    <div>
        <h1 style="font-family:var(--font-display);font-size:1.4rem;font-weight:800;color:var(--kt-navy)">Tableau de bord</h1>
        <p style="color:var(--slate-500);font-size:.82rem;margin-top:.15rem">
            Bonjour <strong>{{ auth()->user()->nom_complet }}</strong> · {{ ucfirst(auth()->user()->role) }}
            · {{ now()->isoFormat('dddd D MMMM YYYY') }}
        </p>
    </div>
    <a href="{{ route('taches.create') }}" style="background:var(--kt-navy);color:#fff;padding:.5rem 1rem;border-radius:8px;text-decoration:none;font-size:.875rem;font-weight:700;display:flex;align-items:center;gap:.4rem">
        ＋ Nouvelle tâche
    </a>
</div>

{{-- Filtres --}}
<form method="GET" action="{{ route('dashboard') }}" class="filters-bar" id="filterForm">
    <div class="filter-group">
        <label class="filter-label">Période</label>
        <select name="periode" class="filter-select" onchange="this.form.submit()">
            @foreach(['7'=>'7 derniers jours','30'=>'30 derniers jours','90'=>'3 derniers mois','tout'=>'Tout'] as $val => $lib)
            <option value="{{ $val }}" {{ $periode == $val ? 'selected' : '' }}>{{ $lib }}</option>
            @endforeach
        </select>
    </div>
    @if(auth()->user()->isManager())
    <div class="filter-group">
        <label class="filter-label">Responsable</label>
        <select name="responsable_id" class="filter-select" onchange="this.form.submit()">
            <option value="">Toute l'équipe</option>
            @foreach($membres as $m)
            <option value="{{ $m->id }}" {{ $responsableId == $m->id ? 'selected' : '' }}>{{ $m->nom_complet }}</option>
            @endforeach
        </select>
    </div>
    @endif
    <div class="filter-group">
        <label class="filter-label">Site</label>
        <select name="site_id" class="filter-select" onchange="this.form.submit()">
            <option value="">Tous les sites</option>
            @foreach($sites as $s)
            <option value="{{ $s->id }}" {{ $siteId == $s->id ? 'selected' : '' }}>{{ $s->nom }}</option>
            @endforeach
        </select>
    </div>
    @if(request()->hasAny(['responsable_id','site_id']) || $periode !== '30')
    <a href="{{ route('dashboard') }}" style="align-self:flex-end;font-size:.8rem;color:var(--slate-500);text-decoration:none;padding:.4rem .5rem">↺ Réinitialiser</a>
    @endif
</form>

{{-- KPI Cards --}}
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-label">Tâches actives</div>
        <div class="kpi-value" style="color:var(--kt-navy)">{{ $stats['total_actives'] }}</div>
        <div class="kpi-sub">en cours de traitement</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">En cours</div>
        <div class="kpi-value" style="color:#2563EB">{{ $stats['en_cours'] }}</div>
        <div class="kpi-sub">travail actif</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Taux de complétion</div>
        <div class="kpi-value" style="color:#15885A">{{ $stats['taux_completion'] }}%</div>
        <div style="background:var(--slate-100);border-radius:999px;height:5px;margin:.4rem 0">
            <div style="width:{{ $stats['taux_completion'] }}%;height:5px;border-radius:999px;background:#15885A;transition:width .5s"></div>
        </div>
        <div class="kpi-sub">tâches terminées</div>
    </div>
    <div class="kpi-card" style="{{ $stats['en_retard'] > 0 ? 'background:#FEF2F2;border-color:#FCA5A5' : '' }}">
        <div class="kpi-label" style="{{ $stats['en_retard'] > 0 ? 'color:#991B1B' : '' }}">En retard</div>
        <div class="kpi-value" style="color:{{ $stats['en_retard'] > 0 ? '#B0202E' : 'var(--slate-300)' }}">{{ $stats['en_retard'] }}</div>
        <div class="kpi-sub" style="{{ $stats['en_retard'] > 0 ? 'color:#991B1B;font-weight:600' : '' }}">
            {{ $stats['en_retard'] > 0 ? '⚠ Action requise' : 'Aucun retard' }}
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Archivées ce mois</div>
        <div class="kpi-value" style="color:var(--slate-600)">{{ $stats['archivees_mois'] }}</div>
        <div class="kpi-sub"><a href="{{ route('taches.archives') }}" style="color:var(--kt-navy);text-decoration:none">Voir les archives →</a></div>
    </div>
</div>

{{-- Graphiques --}}
<div class="charts-grid">

    {{-- Donut : répartition par statut --}}
    <div class="chart-card">
        <div class="chart-header">
            <span class="chart-title">Répartition par statut</span>
            <span style="font-size:.75rem;color:var(--slate-400)">toutes périodes</span>
        </div>
        <div class="chart-body" style="height:260px;display:flex;align-items:center;justify-content:center">
            <canvas id="chartDonut" style="max-height:240px;max-width:240px"></canvas>
        </div>
    </div>

    {{-- Barres : charge par responsable --}}
    <div class="chart-card">
        <div class="chart-header">
            <span class="chart-title">Charge par responsable</span>
            <span style="font-size:.75rem;color:var(--slate-400)">tâches actives</span>
        </div>
        <div class="chart-body" style="height:260px">
            <canvas id="chartBarres" style="height:240px"></canvas>
        </div>
    </div>

</div>

{{-- Courbe pleine largeur --}}
<div class="chart-card" style="margin-bottom:1.5rem">
    <div class="chart-header">
        <span class="chart-title">Avancement dans le temps</span>
        <span style="font-size:.75rem;color:var(--slate-400)">tâches créées vs terminées</span>
    </div>
    <div class="chart-body" style="height:220px">
        <canvas id="chartCourbe" style="height:200px"></canvas>
    </div>
</div>

{{-- Accès rapide --}}
<div class="quick-links">
    <a href="{{ route('taches.index') }}" class="quick-link" style="background:var(--kt-navy);color:#fff">
        <span style="font-size:1.4rem">📋</span>
        <div><div style="font-weight:700;font-size:.9rem">Mes tâches</div><div style="font-size:.75rem;opacity:.75">{{ $stats['total_actives'] }} active(s)</div></div>
    </a>
    <a href="{{ route('taches.create') }}" class="quick-link" style="background:var(--white);border:1px solid var(--slate-200);color:var(--slate-700)">
        <span style="font-size:1.4rem">➕</span>
        <div><div style="font-weight:700;font-size:.9rem">Nouvelle tâche</div><div style="font-size:.75rem;color:var(--slate-400)">Créer et assigner</div></div>
    </a>
    @if($stats['en_retard'] > 0)
    <a href="{{ route('taches.index') }}" class="quick-link" style="background:#FEE2E2;border:1px solid #FCA5A5;color:#991B1B">
        <span style="font-size:1.4rem">⚠️</span>
        <div><div style="font-weight:700;font-size:.9rem">{{ $stats['en_retard'] }} en retard</div><div style="font-size:.75rem;opacity:.8">Action requise</div></div>
    </a>
    @endif
    <a href="{{ route('taches.archives') }}" class="quick-link" style="background:var(--white);border:1px solid var(--slate-200);color:var(--slate-700)">
        <span style="font-size:1.4rem">🗄</span>
        <div><div style="font-weight:700;font-size:.9rem">Archives</div><div style="font-size:.75rem;color:var(--slate-400)">{{ $stats['archivees_mois'] }} ce mois</div></div>
    </a>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Couleurs de la charte KayTechnologie
const KT = {
    navy:    '#173A7A',
    orange:  '#F47A1F',
    maroon:  '#8C1622',
    slate:   '#64748B',
};

// Paramètres des filtres courants pour l'API
const apiParams = new URLSearchParams({
    periode:        '{{ $periode }}',
    responsable_id: '{{ $responsableId ?? '' }}',
    site_id:        '{{ $siteId ?? '' }}',
});

// Fetch des données graphiques
fetch(`/dashboard/data?${apiParams}`)
    .then(r => r.json())
    .then(data => {
        renderDonut(data.donut);
        renderBarres(data.barres);
        renderCourbe(data.courbe);
    });

// ── Donut : répartition par statut ───────────────────────────────────────────
function renderDonut(d) {
    new Chart(document.getElementById('chartDonut'), {
        type: 'doughnut',
        data: {
            labels: d.labels,
            datasets: [{
                data: d.data,
                backgroundColor: d.backgroundColor,
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '62%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { family: "'IBM Plex Sans', sans-serif", size: 11 }, padding: 12 }
                },
                tooltip: { callbacks: {
                    label: ctx => ` ${ctx.label} : ${ctx.raw} tâche(s)`
                }}
            }
        }
    });
}

// ── Barres : charge par responsable ──────────────────────────────────────────
function renderBarres(d) {
    if (! d.labels.length) return;
    new Chart(document.getElementById('chartBarres'), {
        type: 'bar',
        data: {
            labels: d.labels,
            datasets: [{
                label: 'Tâches actives',
                data: d.data,
                backgroundColor: KT.navy,
                borderRadius: 5,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: d.labels.length > 4 ? 'y' : 'x',
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: { beginAtZero: true, ticks: { precision: 0, font: { size: 11 } } }
            }
        }
    });
}

// ── Courbe : créées vs terminées ──────────────────────────────────────────────
function renderCourbe(d) {
    new Chart(document.getElementById('chartCourbe'), {
        type: 'line',
        data: {
            labels: d.labels,
            datasets: [
                {
                    label: 'Créées',
                    data: d.dataC,
                    borderColor: KT.navy,
                    backgroundColor: 'rgba(23,58,122,.08)',
                    fill: true,
                    tension: .35,
                    pointRadius: 3,
                    pointBackgroundColor: KT.navy,
                },
                {
                    label: 'Terminées',
                    data: d.dataT,
                    borderColor: '#15885A',
                    backgroundColor: 'rgba(21,136,90,.06)',
                    fill: true,
                    tension: .35,
                    pointRadius: 3,
                    pointBackgroundColor: '#15885A',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { font: { size: 11 }, padding: 12 } }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, maxTicksLimit: 15 } },
                y: { beginAtZero: true, ticks: { precision: 0, font: { size: 11 } } }
            }
        }
    });
}
</script>
@endpush
@endsection

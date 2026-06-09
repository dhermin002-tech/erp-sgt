@extends('layouts.app')
@section('title', 'Tableau de bord')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
<style>
/* ── Page hero header ── */
.dash-hero {
    background: linear-gradient(135deg, #001f3f 0%, #003366 60%, #002244 100%);
    border-radius: 16px;
    padding: 1.4rem 1.75rem;
    margin-bottom: 1.35rem;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: .75rem;
    position: relative; overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,.18);
}
.dash-hero::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; bottom: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.04) 1px, transparent 1px);
    background-size: 22px 22px;
    pointer-events: none;
}
.dash-hero::after {
    content: '';
    position: absolute; bottom: -40px; right: -40px;
    width: 200px; height: 200px; border-radius: 50%;
    background: radial-gradient(circle, rgba(204,85,0,.12) 0%, transparent 70%);
    pointer-events: none;
}
.dash-hero-left { position: relative; z-index: 1; }
.dash-hero-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.35rem; font-weight: 700; color: #fff;
    letter-spacing: -.03em; line-height: 1.1; margin-bottom: .2rem;
}
.dash-hero-sub {
    font-size: .82rem; color: rgba(255,255,255,.5); line-height: 1.5;
}
.dash-hero-sub strong { color: rgba(255,255,255,.85); font-weight: 600; }
.dash-hero-right { position: relative; z-index: 1; }
.btn-new-task {
    display: inline-flex; align-items: center; gap: .45rem;
    background: #CC5500; color: #fff;
    padding: .55rem 1.1rem;
    border-radius: 9px; text-decoration: none;
    font-family: 'Space Grotesk', sans-serif;
    font-size: .875rem; font-weight: 600;
    transition: all .2s;
    box-shadow: 0 4px 14px rgba(204,85,0,.35);
    white-space: nowrap;
}
.btn-new-task:hover {
    background: #E06010; color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(204,85,0,.45);
}

/* ── KPI Grid — Bento style ── */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: .875rem;
    margin-bottom: 1.35rem;
}
.kpi-card {
    background: var(--white);
    border-radius: 14px;
    padding: 1.2rem 1.25rem 1rem;
    border: 1px solid var(--slate-200);
    box-shadow: 0 2px 8px rgba(15,23,42,.06);
    position: relative; overflow: hidden;
    transition: transform .18s ease, box-shadow .18s ease;
    display: flex; flex-direction: column; gap: .1rem;
}
.kpi-card:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(15,23,42,.11); }

/* Barre supérieure colorée */
.kpi-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0;
    height: 3px; background: var(--kc, #94a3b8);
    border-radius: 14px 14px 0 0;
}
/* Pastille icône en haut à droite */
.kpi-icon {
    position: absolute; top: 1rem; right: 1rem;
    width: 38px; height: 38px; border-radius: 10px;
    background: var(--kc-soft, #f1f5f9);
    color: var(--kc, #64748b);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem;
}
.kpi-label {
    font-size: .69rem; font-weight: 700;
    color: var(--slate-500);
    text-transform: uppercase; letter-spacing: .07em;
    margin-bottom: .3rem; padding-right: 2.8rem;
}
.kpi-value {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 2.4rem; font-weight: 800;
    line-height: 1; color: var(--kc, var(--kt-navy));
    letter-spacing: -.04em;
}
.kpi-sub {
    font-size: .76rem; color: var(--slate-400); margin-top: .3rem;
}

/* Variants KPI */
.kpi-card--actives   { --kc: #173A7A;  --kc-soft: #EAF0FB; }
.kpi-card--cours     { --kc: #2563EB;  --kc-soft: #EBF1FE; }
.kpi-card--attente   { --kc: #C97A0A;  --kc-soft: #FEF3C7; }
.kpi-card--terminees { --kc: #15885A;  --kc-soft: #D1FAE5; }
.kpi-card--completion{ --kc: #15885A;  --kc-soft: #D1FAE5; }
.kpi-card--retard    { --kc: #B0202E;  --kc-soft: #FEE2E2; }
.kpi-card--retard.is-zero { --kc: #94a3b8; --kc-soft: #f1f5f9; }
.kpi-card--archives  { --kc: #64748B;  --kc-soft: #F1F5F9; }

/* Barre de progression complétion */
.kpi-progress-track {
    height: 4px; border-radius: 999px;
    background: var(--slate-100);
    margin-top: .45rem; overflow: hidden;
}
.kpi-progress-fill {
    height: 100%; border-radius: 999px;
    background: linear-gradient(90deg, #15885A, #22c55e);
    transition: width .6s cubic-bezier(.16,1,.3,1);
}

/* ── Filtres ── */
.filters-bar {
    background: var(--white); border-radius: 12px;
    border: 1px solid var(--slate-200);
    padding: .8rem 1.1rem; margin-bottom: 1.25rem;
    display: flex; flex-wrap: wrap; gap: .65rem; align-items: flex-end;
}
.filter-group { display: flex; flex-direction: column; gap: .2rem; }
.filter-label { font-size: .7rem; font-weight: 700; color: var(--slate-500); text-transform: uppercase; letter-spacing: .05em; }
.filter-select {
    padding: .4rem .75rem;
    border: 1.5px solid var(--slate-200);
    border-radius: 7px;
    font-size: .82rem; color: var(--slate-700);
    background: var(--slate-50); outline: none; min-width: 140px;
}
.filter-select:focus { border-color: var(--kt-navy); }

/* ── Charts ── */
.charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.1rem; }
.chart-card {
    background: var(--white); border-radius: 14px;
    border: 1px solid var(--slate-200); overflow: hidden;
    box-shadow: 0 2px 8px rgba(15,23,42,.05);
    transition: box-shadow .2s ease;
}
.chart-card:hover { box-shadow: 0 6px 22px rgba(15,23,42,.09); }
.chart-header {
    padding: .9rem 1.25rem;
    border-bottom: 1px solid var(--slate-100);
    display: flex; align-items: center; justify-content: space-between;
    background: linear-gradient(180deg, #fafafa, var(--white));
}
.chart-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: .88rem; font-weight: 700; color: var(--kt-navy);
    letter-spacing: -.015em;
    display: flex; align-items: center; gap: .5rem;
}
.chart-title-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: linear-gradient(135deg, #CC5500, #FF8C42);
    flex-shrink: 0;
}
.chart-tag {
    font-size: .7rem; font-weight: 600; color: var(--slate-500);
    background: var(--slate-100); padding: .22rem .6rem; border-radius: 999px;
}
.chart-body { padding: 1.25rem; position: relative; }
.chart-empty {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; gap: .4rem; height: 100%;
    color: var(--slate-400); font-size: .82rem; font-weight: 600;
}

/* ── Accès rapide — Quick links premium ── */
.quick-links-section { margin-bottom: 1.5rem; }
.section-title-bar {
    display: flex; align-items: center; gap: .75rem; margin-bottom: .85rem;
}
.section-title-bar-label {
    font-family: 'Space Grotesk', sans-serif;
    font-size: .78rem; font-weight: 700; color: var(--slate-500);
    text-transform: uppercase; letter-spacing: .08em;
    white-space: nowrap;
}
.section-title-bar-line { flex: 1; height: 1px; background: var(--slate-200); }

.quick-links { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: .75rem; }
.quick-link {
    border-radius: 12px; padding: .95rem 1.1rem;
    text-decoration: none;
    display: flex; align-items: center; gap: .75rem;
    border: 1px solid transparent;
    transition: all .18s;
    box-shadow: 0 2px 6px rgba(0,0,0,.06);
}
.quick-link:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.12); }
.ql-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; flex-shrink: 0;
    background: rgba(255,255,255,.15);
}
.ql-body div:first-child { font-weight: 700; font-size: .875rem; line-height: 1.2; }
.ql-body div:last-child  { font-size: .74rem; opacity: .7; margin-top: .1rem; }

@media (max-width: 1024px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 768px)  {
    .kpi-grid   { grid-template-columns: 1fr 1fr; }
    .charts-grid { grid-template-columns: 1fr; }
    .dash-hero-title { font-size: 1.1rem; }
}
@media (max-width: 480px)  { .kpi-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

{{-- Hero header ──────────────────────────────────────── --}}
<div class="dash-hero">
    <div class="dash-hero-left">
        <div class="dash-hero-title">Tableau de bord</div>
        <div class="dash-hero-sub">
            Bonjour <strong>{{ auth()->user()->nom_complet }}</strong>
            &nbsp;·&nbsp; {{ ucfirst(auth()->user()->role) }}
            &nbsp;·&nbsp; {{ now()->isoFormat('dddd D MMMM YYYY') }}
        </div>
    </div>
    <div class="dash-hero-right">
        <a href="{{ route('taches.create') }}" class="btn-new-task">
            <i class="bi bi-plus-lg"></i> Nouvelle tâche
        </a>
    </div>
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

{{-- KPI Cards — Bento Premium ─────────────────────────── --}}
<div class="kpi-grid">

    <div class="kpi-card kpi-card--actives">
        <div class="kpi-icon"><i class="bi bi-list-task"></i></div>
        <div class="kpi-label">Tâches actives</div>
        <div class="kpi-value">{{ $stats['total_actives'] }}</div>
        <div class="kpi-sub">en cours de traitement</div>
    </div>

    <div class="kpi-card kpi-card--cours">
        <div class="kpi-icon"><i class="bi bi-activity"></i></div>
        <div class="kpi-label">En cours</div>
        <div class="kpi-value">{{ $stats['en_cours'] }}</div>
        <div class="kpi-sub">travail actif</div>
    </div>

    <div class="kpi-card kpi-card--attente">
        <div class="kpi-icon"><i class="bi bi-hourglass-split"></i></div>
        <div class="kpi-label">En attente</div>
        <div class="kpi-value">{{ $stats['en_attente'] }}</div>
        <div class="kpi-sub">en pause / blocage</div>
    </div>

    <div class="kpi-card kpi-card--terminees">
        <div class="kpi-icon"><i class="bi bi-check2-all"></i></div>
        <div class="kpi-label">Terminées</div>
        <div class="kpi-value">{{ $stats['terminees'] }}</div>
        <div class="kpi-sub">sur la période</div>
    </div>

    <div class="kpi-card kpi-card--completion">
        <div class="kpi-icon"><i class="bi bi-pie-chart-fill"></i></div>
        <div class="kpi-label">Taux de complétion</div>
        <div class="kpi-value">{{ $stats['taux_completion'] }}%</div>
        <div class="kpi-progress-track">
            <div class="kpi-progress-fill" style="width:{{ $stats['taux_completion'] }}%"></div>
        </div>
        <div class="kpi-sub">tâches terminées</div>
    </div>

    <div class="kpi-card kpi-card--retard {{ $stats['en_retard'] == 0 ? 'is-zero' : '' }}">
        <div class="kpi-icon"><i class="bi bi-exclamation-triangle{{ $stats['en_retard'] > 0 ? '-fill' : '' }}"></i></div>
        <div class="kpi-label">En retard</div>
        <div class="kpi-value">{{ $stats['en_retard'] }}</div>
        <div class="kpi-sub" style="{{ $stats['en_retard'] > 0 ? 'color:#991B1B;font-weight:600' : '' }}">
            {{ $stats['en_retard'] > 0 ? 'Action requise' : 'Aucun retard' }}
        </div>
    </div>

    <div class="kpi-card kpi-card--archives">
        <div class="kpi-icon"><i class="bi bi-archive"></i></div>
        <div class="kpi-label">Archivées ce mois</div>
        <div class="kpi-value">{{ $stats['archivees_mois'] }}</div>
        <div class="kpi-sub">
            <a href="{{ route('taches.archives') }}" style="color:var(--kt-navy);text-decoration:none;font-weight:600">
                Voir les archives →
            </a>
        </div>
    </div>

</div>

{{-- Graphiques --}}
<div class="charts-grid">

    {{-- Donut : répartition par statut --}}
    <div class="chart-card">
        <div class="chart-header">
            <span class="chart-title">
                <span class="chart-title-dot"></span>
                Répartition par statut
            </span>
            <span class="chart-tag">toutes périodes</span>
        </div>
        <div class="chart-body" style="height:260px;display:flex;align-items:center;justify-content:center">
            <canvas id="chartDonut" style="max-height:240px;max-width:240px"></canvas>
        </div>
    </div>

    {{-- Barres : charge par responsable --}}
    <div class="chart-card">
        <div class="chart-header">
            <span class="chart-title">
                <span class="chart-title-dot"></span>
                Charge par responsable
            </span>
            <span class="chart-tag">tâches actives</span>
        </div>
        <div class="chart-body" style="height:260px">
            <canvas id="chartBarres" style="height:240px"></canvas>
        </div>
    </div>

</div>

{{-- Courbe pleine largeur --}}
<div class="chart-card" style="margin-bottom:1.35rem">
    <div class="chart-header">
        <span class="chart-title">
            <span class="chart-title-dot"></span>
            Avancement dans le temps
        </span>
        <span class="chart-tag">tâches créées vs terminées</span>
    </div>
    <div class="chart-body" style="height:220px">
        <canvas id="chartCourbe" style="height:200px"></canvas>
    </div>
</div>

{{-- Accès rapide --}}
<div class="quick-links-section">
    <div class="section-title-bar">
        <div class="section-title-bar-line"></div>
        <span class="section-title-bar-label">Accès rapide</span>
        <div class="section-title-bar-line"></div>
    </div>
    <div class="quick-links">

        <a href="{{ route('taches.index') }}" class="quick-link" style="background:#003366;color:#fff">
            <div class="ql-icon"><i class="bi bi-clipboard-check"></i></div>
            <div class="ql-body">
                <div>Mes tâches</div>
                <div>{{ $stats['total_actives'] }} active(s)</div>
            </div>
        </a>

        <a href="{{ route('taches.create') }}" class="quick-link" style="background:var(--white);border:1px solid var(--slate-200);color:var(--slate-700)">
            <div class="ql-icon" style="background:#EAF0FB;color:#003366"><i class="bi bi-plus-circle"></i></div>
            <div class="ql-body">
                <div>Nouvelle tâche</div>
                <div style="color:var(--slate-400)">Créer et assigner</div>
            </div>
        </a>

        @if($stats['en_retard'] > 0)
        <a href="{{ route('taches.index') }}?statut=en_cours" class="quick-link" style="background:#FEF2F2;border:1px solid #FECACA;color:#991B1B">
            <div class="ql-icon" style="background:#FEE2E2;color:#B0202E"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="ql-body">
                <div>{{ $stats['en_retard'] }} en retard</div>
                <div>Action requise</div>
            </div>
        </a>
        @endif

        <a href="{{ route('taches.archives') }}" class="quick-link" style="background:var(--white);border:1px solid var(--slate-200);color:var(--slate-700)">
            <div class="ql-icon" style="background:#F1F5F9;color:#64748B"><i class="bi bi-archive"></i></div>
            <div class="ql-body">
                <div>Archives</div>
                <div style="color:var(--slate-400)">{{ $stats['archivees_mois'] }} ce mois</div>
            </div>
        </a>

    </div>
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
    done:    '#15885A',
};

// Apparence globale Chart.js — alignée sur la charte KT
Chart.defaults.font.family = "'IBM Plex Sans', system-ui, sans-serif";
Chart.defaults.color = '#64748B';
Chart.defaults.plugins.tooltip.backgroundColor = '#0E2350';
Chart.defaults.plugins.tooltip.titleFont = { family: "'Archivo', sans-serif", size: 12, weight: '700' };
Chart.defaults.plugins.tooltip.bodyFont  = { size: 12 };
Chart.defaults.plugins.tooltip.padding   = 10;
Chart.defaults.plugins.tooltip.cornerRadius = 8;
Chart.defaults.plugins.tooltip.displayColors = true;
Chart.defaults.plugins.tooltip.boxPadding = 4;

// Affiche un message « pas de donnée » à la place d'un graphique vide
function chartVide(canvas, message) {
    const card = canvas.closest('.chart-body');
    card.innerHTML = `<div class="chart-empty"><span>📭</span><span>${message}</span></div>`;
}

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
    const canvas = document.getElementById('chartDonut');
    if (! d.data.length || d.data.reduce((a, b) => a + b, 0) === 0) {
        return chartVide(canvas, 'Aucune tâche sur cette période');
    }
    const total = d.data.reduce((a, b) => a + b, 0);

    // Plugin maison : affiche le total au centre du donut
    const centreTotal = {
        id: 'centreTotal',
        beforeDraw(chart) {
            const { ctx, chartArea: { left, right, top, bottom } } = chart;
            const x = (left + right) / 2;
            const y = (top + bottom) / 2;
            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.font = "800 22px 'Archivo', sans-serif";
            ctx.fillStyle = '#173A7A';
            ctx.fillText(total, x, y - 9);
            ctx.font = "600 10px 'IBM Plex Sans', sans-serif";
            ctx.fillStyle = '#94A3B8';
            ctx.fillText('TÂCHE' + (total > 1 ? 'S' : ''), x, y + 11);
            ctx.restore();
        }
    };

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: d.labels,
            datasets: [{
                data: d.data,
                backgroundColor: d.backgroundColor,
                borderWidth: 3,
                borderColor: '#fff',
                hoverOffset: 8,
                hoverBorderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: { size: 11.5, weight: '600' },
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 8, boxHeight: 8,
                        padding: 16,
                        color: '#334155',
                    }
                },
                tooltip: { callbacks: {
                    label: ctx => ` ${ctx.label} : ${ctx.raw} tâche(s) · ${Math.round(ctx.raw / total * 100)}%`
                }}
            }
        },
        plugins: [centreTotal]
    });
}

// ── Barres : charge par responsable ──────────────────────────────────────────
function renderBarres(d) {
    const canvas = document.getElementById('chartBarres');
    if (! d.labels.length) return chartVide(canvas, 'Aucune charge à afficher');

    const ctx = canvas.getContext('2d');
    const degrade = ctx.createLinearGradient(0, 0, 0, 240);
    degrade.addColorStop(0, KT.orange);
    degrade.addColorStop(1, KT.navy);

    const horizontal = d.labels.length > 4;
    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: d.labels,
            datasets: [{
                label: 'Tâches actives',
                data: d.data,
                backgroundColor: degrade,
                borderRadius: 7,
                borderSkipped: false,
                maxBarThickness: 34,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: horizontal ? 'y' : 'x',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ` ${ctx.raw} tâche(s) active(s)` } }
            },
            scales: {
                x: {
                    grid: { display: !horizontal, color: '#EEF2F7', drawTicks: false },
                    border: { display: false },
                    ticks: { font: { size: 11, weight: '600' }, precision: horizontal ? 0 : undefined }
                },
                y: {
                    beginAtZero: true,
                    grid: { display: horizontal, color: '#EEF2F7', drawTicks: false },
                    border: { display: false },
                    ticks: { precision: horizontal ? undefined : 0, font: { size: 11, weight: '600' } }
                }
            }
        }
    });
}

// ── Courbe : créées vs terminées ──────────────────────────────────────────────
function renderCourbe(d) {
    const canvas = document.getElementById('chartCourbe');
    if (! d.labels.length) return chartVide(canvas, 'Pas encore de données sur cette période');

    const ctx = canvas.getContext('2d');
    const fondCreees = ctx.createLinearGradient(0, 0, 0, 200);
    fondCreees.addColorStop(0, 'rgba(23,58,122,.20)');
    fondCreees.addColorStop(1, 'rgba(23,58,122,0)');
    const fondTerminees = ctx.createLinearGradient(0, 0, 0, 200);
    fondTerminees.addColorStop(0, 'rgba(21,136,90,.18)');
    fondTerminees.addColorStop(1, 'rgba(21,136,90,0)');

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: d.labels,
            datasets: [
                {
                    label: 'Créées',
                    data: d.dataC,
                    borderColor: KT.navy,
                    backgroundColor: fondCreees,
                    fill: true,
                    tension: .4,
                    borderWidth: 2.5,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: KT.navy,
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                },
                {
                    label: 'Terminées',
                    data: d.dataT,
                    borderColor: KT.done,
                    backgroundColor: fondTerminees,
                    fill: true,
                    tension: .4,
                    borderWidth: 2.5,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: KT.done,
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top', align: 'end',
                    labels: {
                        font: { size: 11.5, weight: '600' },
                        usePointStyle: true, pointStyle: 'circle',
                        boxWidth: 8, boxHeight: 8, padding: 18,
                        color: '#334155',
                    }
                },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: {
                    grid: { display: false }, border: { display: false },
                    ticks: { font: { size: 10.5 }, maxTicksLimit: 12, color: '#94A3B8' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#EEF2F7', drawTicks: false }, border: { display: false },
                    ticks: { precision: 0, font: { size: 11 }, color: '#94A3B8' }
                }
            }
        }
    });
}
</script>
@endpush
@endsection

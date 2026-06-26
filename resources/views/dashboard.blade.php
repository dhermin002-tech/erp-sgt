@extends('layouts.app')
@section('title', 'Tableau de bord')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
/* ── Page hero header — bandeau compact ── */
.dash-hero {
    background: linear-gradient(135deg, #001f3f 0%, #003366 60%, #002244 100%);
    border-radius: 12px;
    padding: .85rem 1.4rem;
    margin-bottom: 1.2rem;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: .75rem;
    position: relative; overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,.15);
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

/* ── KPI Grid — compact ── */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: .5rem;
    margin-bottom: 1rem;
}
@media (max-width: 900px)  { .kpi-grid { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 600px)  { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }

.kpi-card {
    background: #fff !important;
    border-radius: 10px;
    padding: .65rem .85rem .6rem;
    border: 1px solid #E2E8F0;
    border-top: 3px solid var(--kc, #94a3b8);
    box-shadow: 0 1px 3px rgba(15,23,42,.05);
    display: flex; flex-direction: column; gap: .15rem;
    transition: box-shadow .15s ease;
}
.kpi-card:hover { box-shadow: 0 4px 14px rgba(15,23,42,.09); }

/* En-tête : icône inline + label */
.kpi-header {
    display: flex; align-items: center; gap: .35rem;
    margin-bottom: .25rem;
}
.kpi-icon {
    width: 18px; height: 18px; border-radius: 4px;
    background: var(--kc-soft, #f1f5f9);
    color: var(--kc, #64748b);
    display: flex; align-items: center; justify-content: center;
    font-size: .62rem; flex-shrink: 0;
}
.kpi-label {
    font-size: .63rem; font-weight: 700;
    color: #64748B;
    text-transform: uppercase; letter-spacing: .06em;
    line-height: 1;
}
.kpi-value {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.75rem; font-weight: 800;
    line-height: 1; color: #0F172A;
    letter-spacing: -.03em;
}
.kpi-sub {
    font-size: .68rem; color: #94A3B8; line-height: 1.2;
    margin-top: .1rem;
}

/* Variants — couleurs uniquement sur l'icône et le trait, jamais sur le fond */
.kpi-card--actives   { --kc: #1E3A8A;  --kc-soft: #DBEAFE; }
.kpi-card--cours     { --kc: #2563EB;  --kc-soft: #EFF6FF; }
.kpi-card--attente   { --kc: #B45309;  --kc-soft: #FEF3C7; }
.kpi-card--terminees { --kc: #15803D;  --kc-soft: #DCFCE7; }
.kpi-card--completion{ --kc: #15803D;  --kc-soft: #DCFCE7; }
.kpi-card--retard    { --kc: #B91C1C;  --kc-soft: #FEE2E2; }
.kpi-card--retard.is-zero { --kc: #94A3B8; --kc-soft: #F1F5F9; }
.kpi-card--archives  { --kc: #475569;  --kc-soft: #F1F5F9; }

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
        <div class="kpi-header">
            <div class="kpi-icon"><i class="bi bi-list-task"></i></div>
            <div class="kpi-label">Tâches actives</div>
        </div>
        <div class="kpi-value" data-target="{{ $stats['total_actives'] }}">{{ $stats['total_actives'] }}</div>
        <div class="kpi-sub">en cours de traitement</div>
    </div>

    <div class="kpi-card kpi-card--cours">
        <div class="kpi-header">
            <div class="kpi-icon"><i class="bi bi-arrow-repeat"></i></div>
            <div class="kpi-label">En cours</div>
        </div>
        <div class="kpi-value" data-target="{{ $stats['en_cours'] }}">{{ $stats['en_cours'] }}</div>
        <div class="kpi-sub">travail actif</div>
    </div>

    <div class="kpi-card kpi-card--attente">
        <div class="kpi-header">
            <div class="kpi-icon"><i class="bi bi-pause-circle"></i></div>
            <div class="kpi-label">En attente</div>
        </div>
        <div class="kpi-value" data-target="{{ $stats['en_attente'] }}">{{ $stats['en_attente'] }}</div>
        <div class="kpi-sub">en pause / blocage</div>
    </div>

    <div class="kpi-card kpi-card--terminees">
        <div class="kpi-header">
            <div class="kpi-icon"><i class="bi bi-check2"></i></div>
            <div class="kpi-label">Terminées</div>
        </div>
        <div class="kpi-value" data-target="{{ $stats['terminees'] }}">{{ $stats['terminees'] }}</div>
        <div class="kpi-sub">sur la période</div>
    </div>

    <div class="kpi-card kpi-card--completion">
        <div class="kpi-header">
            <div class="kpi-icon"><i class="bi bi-bar-chart"></i></div>
            <div class="kpi-label">Taux de complétion</div>
        </div>
        <div class="kpi-value" data-target="{{ $stats['taux_completion'] }}">{{ $stats['taux_completion'] }}%</div>
        <div class="kpi-progress-track">
            <div class="kpi-progress-fill" style="width:{{ $stats['taux_completion'] }}%"></div>
        </div>
        <div class="kpi-sub">tâches terminées</div>
    </div>

    <div class="kpi-card kpi-card--retard {{ $stats['en_retard'] == 0 ? 'is-zero' : '' }}">
        <div class="kpi-header">
            <div class="kpi-icon"><i class="bi bi-clock-history"></i></div>
            <div class="kpi-label">En retard</div>
        </div>
        <div class="kpi-value" data-target="{{ $stats['en_retard'] }}">{{ $stats['en_retard'] }}</div>
        <div class="kpi-sub" @if($stats['en_retard'] > 0) style="color:#B91C1C;font-weight:600" @endif>
            {{ $stats['en_retard'] > 0 ? 'Action requise' : 'Aucun retard' }}
        </div>
    </div>

    <div class="kpi-card kpi-card--archives">
        <div class="kpi-header">
            <div class="kpi-icon"><i class="bi bi-archive"></i></div>
            <div class="kpi-label">Archivées ce mois</div>
        </div>
        <div class="kpi-value" data-target="{{ $stats['archivees_mois'] }}">{{ $stats['archivees_mois'] }}</div>
        <div class="kpi-sub">
            <a href="{{ route('taches.archives') }}" style="color:#1E3A8A;text-decoration:none;font-weight:600;font-size:.74rem">
                Voir l'historique →
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

{{-- ══ Panneaux secondaires (maquette premium) ══ --}}
@php
$rangPrioColor = ['urgente'=>'#B0202E','haute'=>'#C97A0A','normale'=>'#2563EB','basse'=>'#64748B'];
$avatarBgDash  = ['var(--kt-navy)','var(--kt-orange)','var(--kt-purple)','var(--kt-maroon)','#15885A'];
@endphp
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:1.35rem;margin-bottom:1.35rem">

    {{-- Tâches critiques --}}
    <div class="chart-card">
        <div class="chart-header">
            <span class="chart-title"><span class="chart-title-dot" style="background:#B0202E"></span> Tâches critiques</span>
            <a href="{{ route('taches.index', ['statut' => '']) }}" class="chart-tag" style="text-decoration:none">Voir toutes</a>
        </div>
        <div style="padding:.35rem 0">
            @forelse($panneaux['critiques'] as $t)
            <a href="{{ route('taches.show', $t) }}" style="display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.6rem .25rem;border-bottom:1px solid var(--slate-100);text-decoration:none">
                <div style="min-width:0">
                    <div style="font-weight:600;color:var(--slate-800);font-size:.86rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:{{ $rangPrioColor[$t->priorite] ?? '#64748B' }};margin-right:.4rem"></span>{{ $t->titre }}
                    </div>
                    <div style="font-size:.74rem;color:var(--slate-400);margin-top:.1rem">{{ $t->site?->nom ?? '—' }}</div>
                </div>
                <div style="text-align:right;white-space:nowrap">
                    @if($t->estEnRetard())
                    <span style="font-size:.7rem;font-weight:700;color:#B0202E">{{ (int) now()->startOfDay()->diffInDays($t->date_echeance) }} j en retard</span>
                    @else
                    <span style="font-size:.7rem;font-weight:600;color:{{ $rangPrioColor[$t->priorite] ?? '#64748B' }};text-transform:capitalize">{{ $t->priorite }}</span>
                    @endif
                </div>
            </a>
            @empty
            <div style="text-align:center;padding:1.5rem;color:var(--slate-400);font-size:.85rem">Aucune tâche critique 🎉</div>
            @endforelse
        </div>
    </div>

    {{-- Échéances à venir --}}
    <div class="chart-card">
        <div class="chart-header">
            <span class="chart-title"><span class="chart-title-dot" style="background:#C97A0A"></span> Échéances à venir</span>
            <span class="chart-tag">14 jours</span>
        </div>
        <div style="padding:.35rem 0">
            @forelse($panneaux['echeances'] as $t)
            <a href="{{ route('taches.show', $t) }}" style="display:flex;align-items:center;gap:.75rem;padding:.6rem .25rem;border-bottom:1px solid var(--slate-100);text-decoration:none">
                <div style="flex-shrink:0;width:42px;text-align:center;background:var(--slate-50);border-radius:8px;padding:.25rem 0">
                    <div style="font-size:.62rem;font-weight:700;color:var(--slate-400);text-transform:uppercase">{{ $t->date_echeance->translatedFormat('M') }}</div>
                    <div style="font-family:var(--font-display);font-weight:800;color:var(--kt-navy);font-size:1rem;line-height:1">{{ $t->date_echeance->format('d') }}</div>
                </div>
                <div style="min-width:0;flex:1">
                    <div style="font-weight:600;color:var(--slate-800);font-size:.86rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $t->titre }}</div>
                    <div style="font-size:.74rem;color:var(--slate-400)">{{ $t->site?->nom ?? '—' }}</div>
                </div>
                <span style="font-size:.68rem;font-weight:700;color:#fff;background:{{ $rangPrioColor[$t->priorite] ?? '#64748B' }};padding:.15rem .5rem;border-radius:20px;text-transform:capitalize;flex-shrink:0">{{ $t->priorite }}</span>
            </a>
            @empty
            <div style="text-align:center;padding:1.5rem;color:var(--slate-400);font-size:.85rem">Aucune échéance dans les 14 jours</div>
            @endforelse
        </div>
    </div>

    {{-- Activités récentes --}}
    <div class="chart-card">
        <div class="chart-header">
            <span class="chart-title"><span class="chart-title-dot" style="background:#2563EB"></span> Activités récentes</span>
        </div>
        <div style="padding:.35rem 0">
            @forelse($panneaux['activites'] as $i => $t)
            @php $init = strtoupper(mb_substr($t->createur->prenom ?? '', 0, 1) . mb_substr($t->createur->nom ?? '?', 0, 1)); @endphp
            <a href="{{ route('taches.show', $t) }}" style="display:flex;align-items:center;gap:.7rem;padding:.6rem .25rem;border-bottom:1px solid var(--slate-100);text-decoration:none">
                <div style="flex-shrink:0;width:32px;height:32px;border-radius:50%;background:{{ $avatarBgDash[$i % count($avatarBgDash)] }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700">{{ $init }}</div>
                <div style="min-width:0;flex:1">
                    <div style="font-size:.82rem;color:var(--slate-700);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        <strong>{{ $t->createur->nom_complet ?? 'Système' }}</strong> · {{ \App\Models\Tache::libelleStatut($t->statut) }}
                    </div>
                    <div style="font-size:.74rem;color:var(--slate-400);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $t->titre }} · {{ $t->updated_at->diffForHumans() }}</div>
                </div>
            </a>
            @empty
            <div style="text-align:center;padding:1.5rem;color:var(--slate-400);font-size:.85rem">Aucune activité récente</div>
            @endforelse
        </div>
    </div>

    {{-- Recommandations (règles dérivées des KPIs, pas de l'IA) --}}
    <div class="chart-card" style="background:linear-gradient(135deg,#faf9ff,#fff)">
        <div class="chart-header">
            <span class="chart-title"><span class="chart-title-dot" style="background:#7C3FBF"></span> Recommandations</span>
            <span class="chart-tag" style="background:#7C3FBF;color:#fff">Auto</span>
        </div>
        <div style="padding:.35rem 0">
            @foreach($panneaux['actionsIA'] as $a)
            <div style="display:flex;align-items:flex-start;gap:.7rem;padding:.65rem .25rem;border-bottom:1px solid var(--slate-100)">
                <div style="flex-shrink:0;width:34px;height:34px;border-radius:9px;background:{{ $a['couleur'] }}18;color:{{ $a['couleur'] }};display:flex;align-items:center;justify-content:center;font-size:1rem"><i class="bi {{ $a['icone'] }}"></i></div>
                <div style="min-width:0">
                    <div style="font-weight:600;color:var(--slate-800);font-size:.84rem">{{ $a['titre'] }}</div>
                    <div style="font-size:.76rem;color:var(--slate-500);margin-top:.1rem">{{ $a['texte'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>

{{-- Bloc supervision Agents IA (Managers uniquement) ──── --}}
@if(auth()->user()->isManager() && $agentsSupervision)
<div style="margin-bottom:1.35rem">
    <div class="section-title-bar">
        <div class="section-title-bar-line"></div>
        <span class="section-title-bar-label">🤖 Activité Agents IA — aujourd'hui</span>
        <div class="section-title-bar-line"></div>
    </div>

    {{-- KPIs agents ── --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.75rem;margin-bottom:1rem">
        <div style="background:#f5f3ff;border-radius:12px;padding:1rem 1.1rem;border:1px solid #ede9fe">
            <div style="font-size:.68rem;font-weight:700;color:#7c3aed;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem">Sessions actives</div>
            <div style="font-family:'Space Grotesk',sans-serif;font-size:2rem;font-weight:800;color:#5b21b6;line-height:1">{{ $agentsSupervision['sessions_actives'] }}</div>
            <div style="font-size:.75rem;color:#8b5cf6;margin-top:.25rem">en ce moment</div>
        </div>
        <div style="background:#faf5ff;border-radius:12px;padding:1rem 1.1rem;border:1px solid #ede9fe">
            <div style="font-size:.68rem;font-weight:700;color:#7c3aed;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem">Rapports publiés</div>
            <div style="font-family:'Space Grotesk',sans-serif;font-size:2rem;font-weight:800;color:#5b21b6;line-height:1">{{ $agentsSupervision['rapports_jour'] }}</div>
            <div style="font-size:.75rem;color:#8b5cf6;margin-top:.25rem">aujourd'hui</div>
        </div>
        <div style="background:#f5f3ff;border-radius:12px;padding:1rem 1.1rem;border:1px solid #ede9fe;display:flex;align-items:center;gap:.75rem">
            <div>
                <div style="font-size:.68rem;font-weight:700;color:#7c3aed;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem">Agents enregistrés</div>
                <div style="font-family:'Space Grotesk',sans-serif;font-size:2rem;font-weight:800;color:#5b21b6;line-height:1">{{ $agentsSupervision['agents']->count() }}</div>
                <div style="font-size:.75rem;color:#8b5cf6;margin-top:.25rem">comptes actifs</div>
            </div>
        </div>
        <div style="background:#faf5ff;border-radius:12px;padding:1rem 1.1rem;border:1px solid #ede9fe;display:flex;align-items:center;gap:.75rem">
            <div style="flex:1">
                <a href="{{ route('agents.rapports') }}" style="display:flex;align-items:center;gap:.4rem;text-decoration:none;color:#7c3aed;font-weight:700;font-size:.85rem;margin-bottom:.4rem">
                    <i class="bi bi-file-earmark-text"></i> Rapports agents →
                </a>
                <a href="{{ route('agents.sessions') }}" style="display:flex;align-items:center;gap:.4rem;text-decoration:none;color:#7c3aed;font-weight:700;font-size:.85rem">
                    <i class="bi bi-play-circle"></i> Sessions agents →
                </a>
            </div>
        </div>
    </div>

    {{-- Ligne par agent ── --}}
    @if($agentsSupervision['agents']->isNotEmpty())
    <div style="background:#fff;border-radius:12px;border:1px solid #ede9fe;overflow:hidden">
        @foreach($agentsSupervision['agents'] as $item)
        @php $agent = $item['agent']; $session = $item['session_active']; @endphp
        <div style="display:flex;align-items:center;gap:1rem;padding:.75rem 1.1rem;border-bottom:1px solid #f5f3ff;{{ $loop->last ? 'border-bottom:none' : '' }}">
            {{-- Avatar ── --}}
            <div style="width:34px;height:34px;border-radius:50%;background:{{ $agent->agent_couleur ?? '#7c3aed' }};color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.8rem;flex-shrink:0">
                {{ strtoupper(substr($agent->agent_code ?? 'A', 0, 1)) }}
            </div>
            {{-- Nom + code ── --}}
            <div style="flex:1;min-width:0">
                <div style="font-weight:700;font-size:.875rem;color:#1e1b4b">{{ $agent->nom_complet }}</div>
                <div style="font-size:.75rem;color:#7c3aed;font-family:'IBM Plex Mono',monospace">{{ $agent->agent_code }}</div>
            </div>
            {{-- Session ── --}}
            <div style="min-width:180px">
                @if($session)
                <div style="display:inline-flex;align-items:center;gap:.35rem;background:#f0fdf4;color:#15803d;padding:.2rem .65rem;border-radius:20px;font-size:.75rem;font-weight:700;border:1px solid #bbf7d0">
                    <span style="width:6px;height:6px;border-radius:50%;background:#15803d;animation:pulse-ag 1.5s ease infinite;display:inline-block"></span>
                    Session active — {{ $session->projet }}
                </div>
                @else
                <span style="font-size:.75rem;color:#94a3b8">Hors session</span>
                @endif
            </div>
            {{-- Rapports aujourd'hui ── --}}
            <div style="text-align:right;min-width:80px">
                @if($item['rapports_jour'] > 0)
                <span style="background:#f5f3ff;color:#7c3aed;font-size:.75rem;font-weight:700;padding:.18rem .55rem;border-radius:20px">
                    {{ $item['rapports_jour'] }} rapport(s)
                </span>
                @else
                <span style="font-size:.75rem;color:#94a3b8">0 rapport</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
<style>
@keyframes pulse-ag {
    0%, 100% { opacity:1; transform:scale(1); }
    50%       { opacity:.3; transform:scale(1.4); }
}
</style>
@endif

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

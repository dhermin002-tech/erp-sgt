@extends('layouts.app')
@section('title', 'Sessions agents IA')

@push('styles')
<style>
/* ── Hero ─── */
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

/* ── Sessions actives ─── */
.actives-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: .85rem; margin-bottom: 1.35rem;
}
.session-active-card {
    background: #fff; border-radius: 14px;
    border: 2px solid #8b5cf6; padding: 1.1rem 1.2rem;
    box-shadow: 0 2px 12px rgba(139,92,246,.15);
    position: relative; overflow: hidden;
}
.session-active-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, #7c3aed, #a78bfa);
}
.session-active-pulse {
    display: inline-flex; align-items: center; gap: .4rem;
    font-size: .72rem; font-weight: 700; color: #7c3aed;
    background: #f5f3ff; padding: .2rem .6rem; border-radius: 20px; margin-bottom: .6rem;
}
.session-active-pulse::before {
    content: ''; width: 6px; height: 6px; border-radius: 50%;
    background: #7c3aed; animation: pulse-agent 1.5s ease infinite;
    display: inline-block;
}
@keyframes pulse-agent {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: .4; transform: scale(1.3); }
}
.session-active-agent { font-family: 'Space Grotesk', sans-serif; font-weight: 700; color: #1e1b4b; font-size: .95rem; }
.session-active-projet { font-size: .82rem; color: #7c3aed; font-weight: 600; margin-top: .2rem; }
.session-active-contexte { font-size: .79rem; color: #64748b; margin-top: .3rem; line-height: 1.4; }
.session-active-time { font-size: .75rem; color: #94a3b8; margin-top: .5rem; }

/* ── Filtres ─── */
.filters-bar {
    background: #fff; border-radius: 12px; border: 1px solid #e2e8f0;
    padding: .8rem 1.1rem; margin-bottom: 1.25rem;
    display: flex; flex-wrap: wrap; gap: .65rem; align-items: flex-end;
}
.filter-group { display: flex; flex-direction: column; gap: .2rem; }
.filter-label { font-size: .7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .05em; }
.filter-select, .filter-input {
    padding: .4rem .75rem; border: 1.5px solid #e2e8f0; border-radius: 7px;
    font-size: .82rem; color: #334155; background: #f8fafc; outline: none; min-width: 140px;
}
.filter-select:focus, .filter-input:focus { border-color: #7c3aed; }
.btn-filter {
    padding: .42rem 1rem; background: #7c3aed; color: #fff; border: none;
    border-radius: 7px; font-size: .82rem; font-weight: 600; cursor: pointer;
    align-self: flex-end; transition: background .15s;
}
.btn-filter:hover { background: #6d28d9; }

/* ── Table ─── */
.table-wrapper {
    background: #fff; border-radius: 16px; border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0,0,0,.05); overflow: hidden;
}
.table-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9;
    flex-wrap: wrap; gap: .5rem;
}
.table-header-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: .95rem; font-weight: 700; color: #1e1b4b;
    display: flex; align-items: center; gap: .5rem;
}
.table-kt { width: 100%; border-collapse: collapse; }
.table-kt th {
    padding: .75rem 1rem; text-align: left;
    font-size: .72rem; font-weight: 700; color: #94a3b8;
    text-transform: uppercase; letter-spacing: .06em;
    background: #fafafa; border-bottom: 1px solid #f1f5f9;
}
.table-kt td {
    padding: .85rem 1rem; font-size: .875rem; color: #334155;
    border-bottom: 1px solid #f8fafc; vertical-align: middle;
}
.table-kt tbody tr:hover { background: #faf5ff; }
.table-kt tbody tr:last-child td { border-bottom: none; }

.badge-session-statut {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .2rem .6rem; border-radius: 20px; font-size: .72rem; font-weight: 600;
}
.badge-session-statut--en_cours    { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.badge-session-statut--terminee    { background: #f5f3ff; color: #5b21b6; border: 1px solid #ddd6fe; }
.badge-session-statut--interrompue { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

.agent-pill {
    display: inline-flex; align-items: center; gap: .4rem;
    background: #f5f3ff; border-radius: 20px;
    padding: .2rem .7rem .2rem .3rem; font-size: .8rem;
}
.agent-dot {
    width: 22px; height: 22px; border-radius: 50%;
    background: #7c3aed; color: #fff; font-size: .65rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
}
.duree-chip {
    background: #f1f5f9; color: #475569; font-size: .78rem; font-weight: 600;
    padding: .18rem .55rem; border-radius: 20px; font-family: 'IBM Plex Mono', monospace;
}
.empty-state {
    text-align: center; padding: 3rem 1rem;
    color: #94a3b8; font-size: .9rem;
}
.empty-state .empty-icon { font-size: 2.5rem; display: block; margin-bottom: .75rem; }

/* ── Section titre ─── */
.section-title-bar { display: flex; align-items: center; gap: .75rem; margin-bottom: .85rem; }
.section-title-bar-label {
    font-family: 'Space Grotesk', sans-serif;
    font-size: .78rem; font-weight: 700; color: #7c3aed;
    text-transform: uppercase; letter-spacing: .08em; white-space: nowrap;
}
.section-title-bar-line { flex: 1; height: 1px; background: #ede9fe; }
</style>
@endpush

@section('content')

{{-- Hero ─────────────────────────────────────────────── --}}
<div class="agents-hero">
    <div style="position:relative;z-index:1">
        <div class="agents-hero-title">⚡ Sessions Agents IA</div>
        <div class="agents-hero-sub">Suivi des sessions de travail des agents dans le SGT</div>
    </div>
    <div style="position:relative;z-index:1">
        <a href="{{ route('agents.rapports') }}" style="display:inline-flex;align-items:center;gap:.45rem;background:rgba(255,255,255,.12);color:#fff;padding:.5rem 1rem;border-radius:9px;text-decoration:none;font-size:.85rem;font-weight:600;border:1px solid rgba(255,255,255,.2)">
            <i class="bi bi-file-earmark-text"></i> Rapports
        </a>
    </div>
</div>

{{-- Sessions actives en temps réel ─────────────────────── --}}
@if($actives->isNotEmpty())
<div class="section-title-bar">
    <div class="section-title-bar-line"></div>
    <span class="section-title-bar-label">🟢 {{ $actives->count() }} session(s) active(s) en ce moment</span>
    <div class="section-title-bar-line"></div>
</div>
<div class="actives-grid" style="margin-bottom:1.5rem">
    @foreach($actives as $session)
    <div class="session-active-card">
        <div class="session-active-pulse">En cours</div>
        <div class="session-active-agent">
            🤖 {{ $session->user?->agent_code ?? $session->user?->nom_complet ?? '—' }}
        </div>
        <div class="session-active-projet">📁 {{ $session->projet }}</div>
        @if($session->contexte)
        <div class="session-active-contexte">{{ $session->contexte }}</div>
        @endif
        <div class="session-active-time">
            Démarrée {{ $session->demarree_a?->diffForHumans() }}
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Filtres ─────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('agents.sessions') }}" class="filters-bar">
    <div class="filter-group">
        <label class="filter-label">Agent</label>
        <select name="agent_id" class="filter-select">
            <option value="">Tous les agents</option>
            @foreach($agents as $a)
            <option value="{{ $a->id }}" {{ request('agent_id') == $a->id ? 'selected' : '' }}>{{ $a->nom_complet }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <label class="filter-label">Statut</label>
        <select name="statut" class="filter-select">
            <option value="">Tous</option>
            <option value="en_cours"    {{ request('statut') == 'en_cours'    ? 'selected' : '' }}>En cours</option>
            <option value="terminee"    {{ request('statut') == 'terminee'    ? 'selected' : '' }}>Terminée</option>
            <option value="interrompue" {{ request('statut') == 'interrompue' ? 'selected' : '' }}>Interrompue</option>
        </select>
    </div>
    <div class="filter-group">
        <label class="filter-label">Projet</label>
        <input type="text" name="projet" class="filter-input" placeholder="Nom du projet…" value="{{ request('projet') }}">
    </div>
    <button type="submit" class="btn-filter"><i class="bi bi-funnel"></i> Filtrer</button>
    @if(request()->hasAny(['agent_id','statut','projet']))
    <a href="{{ route('agents.sessions') }}" style="align-self:flex-end;font-size:.8rem;color:#94a3b8;text-decoration:none;padding:.42rem .5rem">↺ Réinitialiser</a>
    @endif
</form>

{{-- Table historique ─────────────────────────────────────── --}}
<div class="table-wrapper">
    <div class="table-header">
        <div class="table-header-title">
            <span style="background:#f5f3ff;color:#7c3aed;border-radius:8px;padding:.25rem .55rem;font-size:.85rem">⚡</span>
            {{ $sessions->total() }} session(s)
        </div>
    </div>

    @if($sessions->isEmpty())
    <div class="empty-state">
        <span class="empty-icon">🔌</span>
        Aucune session enregistrée. Les agents démarrent leurs sessions via l'API SGT.
    </div>
    @else
    <div class="table-responsive">
        <table class="table-kt">
            <thead>
                <tr>
                    <th>Agent</th>
                    <th>Projet</th>
                    <th>Contexte</th>
                    <th>Démarrée</th>
                    <th>Durée</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sessions as $session)
                <tr>
                    <td>
                        @if($session->user)
                        <span class="agent-pill">
                            <span class="agent-dot" style="background:{{ $session->user->agent_couleur ?? '#7c3aed' }}">
                                {{ strtoupper(substr($session->user->agent_code ?? 'A', 0, 1)) }}
                            </span>
                            <span>{{ $session->user->agent_code ?? $session->user->nom_complet }}</span>
                        </span>
                        @else <span style="color:#94a3b8">—</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-weight:600;color:#1e1b4b;font-size:.85rem">{{ $session->projet }}</span>
                    </td>
                    <td>
                        <span style="color:#64748b;font-size:.82rem;max-width:220px;display:block;overflow:hidden;white-space:nowrap;text-overflow:ellipsis">
                            {{ $session->contexte ?? '—' }}
                        </span>
                    </td>
                    <td style="font-size:.8rem;color:#94a3b8;white-space:nowrap">
                        {{ $session->demarree_a?->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        @if($session->statut === 'en_cours')
                        <span class="duree-chip">⏱ en cours</span>
                        @else
                        <span class="duree-chip">{{ $session->duree ?? '—' }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge-session-statut badge-session-statut--{{ $session->statut }}">
                            @if($session->statut === 'en_cours') 🟢
                            @elseif($session->statut === 'terminee') ✅
                            @else ⚠️
                            @endif
                            {{ match($session->statut) {
                                'en_cours'    => 'En cours',
                                'terminee'    => 'Terminée',
                                'interrompue' => 'Interrompue',
                                default       => $session->statut,
                            } }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($sessions->hasPages())
    <div style="padding:1rem 1.25rem;border-top:1px solid #f1f5f9">
        {{ $sessions->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>

@endsection

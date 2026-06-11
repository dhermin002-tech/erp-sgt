@extends('layouts.app')
@section('title', 'Rapports agents IA')

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

/* ── Badges ─── */
.badge-type {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .22rem .65rem; border-radius: 20px; font-size: .72rem; font-weight: 700;
}
.badge-type--session     { background: #ede9fe; color: #5b21b6; }
.badge-type--sprint      { background: #dbeafe; color: #1d4ed8; }
.badge-type--bug         { background: #fee2e2; color: #b91c1c; }
.badge-type--deploiement { background: #d1fae5; color: #065f46; }
.badge-type--audit       { background: #fef3c7; color: #92400e; }
.badge-type--quotidien   { background: #f1f5f9; color: #475569; }

.badge-statut-ia {
    display: inline-flex; align-items: center; gap: .25rem;
    padding: .18rem .55rem; border-radius: 20px; font-size: .72rem; font-weight: 600;
}
.badge-statut-ia--info    { background: #dbeafe; color: #1d4ed8; }
.badge-statut-ia--warning { background: #fef3c7; color: #92400e; }
.badge-statut-ia--erreur  { background: #fee2e2; color: #b91c1c; }

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

/* ── Rapport modal preview ─── */
.contenu-preview {
    max-width: 340px; overflow: hidden;
    white-space: nowrap; text-overflow: ellipsis;
    color: #64748b; font-size: .82rem;
}
.btn-voir {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .3rem .7rem; border-radius: 7px;
    background: #f5f3ff; color: #7c3aed;
    border: 1px solid #ede9fe; font-size: .8rem; font-weight: 600;
    text-decoration: none; transition: all .15s; cursor: pointer;
}
.btn-voir:hover { background: #ede9fe; color: #5b21b6; }

/* ── Modal ─── */
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.55); z-index: 1000;
    align-items: center; justify-content: center;
}
.modal-overlay.open { display: flex; }
.modal-box {
    background: #fff; border-radius: 16px; width: min(680px, 96vw);
    max-height: 85vh; display: flex; flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,.25);
}
.modal-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9;
}
.modal-title { font-family: 'Space Grotesk', sans-serif; font-size: 1rem; font-weight: 700; color: #1e1b4b; }
.modal-close {
    background: none; border: none; font-size: 1.3rem; cursor: pointer;
    color: #94a3b8; line-height: 1; transition: color .15s;
}
.modal-close:hover { color: #334155; }
.modal-body { padding: 1.25rem 1.5rem; overflow-y: auto; flex: 1; }
.modal-body pre {
    white-space: pre-wrap; word-break: break-word;
    font-family: 'IBM Plex Mono', monospace; font-size: .82rem; line-height: 1.6;
    background: #f8fafc; padding: 1rem; border-radius: 10px;
    border: 1px solid #e2e8f0; color: #334155;
}
.modal-meta {
    display: flex; flex-wrap: wrap; gap: .5rem;
    margin-bottom: .75rem; font-size: .8rem; color: #64748b;
}
.modal-meta span { background: #f1f5f9; padding: .2rem .6rem; border-radius: 20px; }

/* ── Vide ─── */
.empty-state {
    text-align: center; padding: 3rem 1rem;
    color: #94a3b8; font-size: .9rem;
}
.empty-state .empty-icon { font-size: 2.5rem; display: block; margin-bottom: .75rem; }
</style>
@endpush

@section('content')

{{-- Hero ─────────────────────────────────────────────── --}}
<div class="agents-hero">
    <div style="position:relative;z-index:1">
        <div class="agents-hero-title">🤖 Rapports Agents IA</div>
        <div class="agents-hero-sub">Historique des rapports publiés par les agents IA dans le SGT</div>
    </div>
    <div style="position:relative;z-index:1">
        <a href="{{ route('agents.sessions') }}" style="display:inline-flex;align-items:center;gap:.45rem;background:rgba(255,255,255,.12);color:#fff;padding:.5rem 1rem;border-radius:9px;text-decoration:none;font-size:.85rem;font-weight:600;border:1px solid rgba(255,255,255,.2)">
            <i class="bi bi-play-circle"></i> Sessions agents
        </a>
    </div>
</div>

{{-- Filtres ─────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('agents.rapports') }}" class="filters-bar">
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
        <label class="filter-label">Projet</label>
        <select name="projet" class="filter-select">
            <option value="">Tous les projets</option>
            @foreach($projets as $p)
            <option value="{{ $p }}" {{ request('projet') == $p ? 'selected' : '' }}>{{ $p }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <label class="filter-label">Type</label>
        <select name="type" class="filter-select">
            <option value="">Tous</option>
            @foreach(['session','sprint','bug','deploiement','audit','quotidien'] as $t)
            <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <label class="filter-label">Statut</label>
        <select name="statut" class="filter-select">
            <option value="">Tous</option>
            <option value="info"    {{ request('statut') == 'info'    ? 'selected' : '' }}>Info</option>
            <option value="warning" {{ request('statut') == 'warning' ? 'selected' : '' }}>Warning</option>
            <option value="erreur"  {{ request('statut') == 'erreur'  ? 'selected' : '' }}>Erreur</option>
        </select>
    </div>
    <button type="submit" class="btn-filter"><i class="bi bi-funnel"></i> Filtrer</button>
    @if(request()->hasAny(['agent_id','projet','type','statut']))
    <a href="{{ route('agents.rapports') }}" style="align-self:flex-end;font-size:.8rem;color:#94a3b8;text-decoration:none;padding:.42rem .5rem">↺ Réinitialiser</a>
    @endif
</form>

{{-- Table ─────────────────────────────────────────────── --}}
<div class="table-wrapper">
    <div class="table-header">
        <div class="table-header-title">
            <span style="background:#f5f3ff;color:#7c3aed;border-radius:8px;padding:.25rem .55rem;font-size:.85rem">📄</span>
            {{ $rapports->total() }} rapport(s)
        </div>
        <div style="font-size:.78rem;color:#94a3b8">{{ now()->isoFormat('D MMMM YYYY') }}</div>
    </div>

    @if($rapports->isEmpty())
    <div class="empty-state">
        <span class="empty-icon">📭</span>
        Aucun rapport trouvé. Les agents IA publient leurs rapports via l'API SGT.
    </div>
    @else
    <div class="table-responsive">
        <table class="table-kt">
            <thead>
                <tr>
                    <th>Agent</th>
                    <th>Projet</th>
                    <th>Type</th>
                    <th>Titre</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($rapports as $rapport)
                <tr>
                    <td>
                        @if($rapport->user)
                        <span class="agent-pill">
                            <span class="agent-dot" style="background:{{ $rapport->user->agent_couleur ?? '#7c3aed' }}">
                                {{ strtoupper(substr($rapport->user->agent_code ?? 'A', 0, 1)) }}
                            </span>
                            <span>{{ $rapport->user->agent_code ?? $rapport->user->nom_complet }}</span>
                        </span>
                        @else
                        <span style="color:#94a3b8;font-size:.8rem">—</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:.85rem;color:#1e1b4b">{{ $rapport->projet }}</span>
                    </td>
                    <td>
                        <span class="badge-type badge-type--{{ $rapport->type }}">{{ ucfirst($rapport->type) }}</span>
                    </td>
                    <td>
                        <div style="max-width:260px;font-weight:600;color:#1e1b4b;font-size:.85rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis">
                            {{ $rapport->titre }}
                        </div>
                    </td>
                    <td>
                        <span class="badge-statut-ia badge-statut-ia--{{ $rapport->statut }}">
                            @if($rapport->statut === 'info') ℹ️
                            @elseif($rapport->statut === 'warning') ⚠️
                            @else 🔴
                            @endif
                            {{ ucfirst($rapport->statut) }}
                        </span>
                    </td>
                    <td style="color:#94a3b8;font-size:.8rem;white-space:nowrap">
                        {{ $rapport->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        <button class="btn-voir" onclick="ouvrirRapport({{ $rapport->id }})">
                            <i class="bi bi-eye"></i> Voir
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($rapports->hasPages())
    <div style="padding:1rem 1.25rem;border-top:1px solid #f1f5f9">
        {{ $rapports->links() }}
    </div>
    @endif
</div>

{{-- Modal lecture rapport ─────────────────────────────── --}}
<div class="modal-overlay" id="modalRapport" onclick="fermerModal(event)">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <div class="modal-title" id="modalTitre">—</div>
                <div class="modal-meta" id="modalMeta"></div>
            </div>
            <button class="modal-close" onclick="fermerModalDirect()">✕</button>
        </div>
        <div class="modal-body">
            <pre id="modalContenu" style="margin:0"></pre>
        </div>
    </div>
</div>

{{-- Données rapports en JSON pour le modal ─────────────── --}}
<script>
const rapportsData = @json($rapports->keyBy('id')->map(fn($r) => [
    'titre'   => $r->titre,
    'contenu' => $r->contenu,
    'type'    => $r->type,
    'statut'  => $r->statut,
    'projet'  => $r->projet,
    'agent'   => $r->user?->agent_code ?? $r->user?->nom_complet ?? '—',
    'date'    => $r->created_at->format('d/m/Y H:i'),
    'meta'    => $r->meta,
]));

function ouvrirRapport(id) {
    const r = rapportsData[id];
    if (!r) return;
    document.getElementById('modalTitre').textContent = r.titre;
    document.getElementById('modalContenu').textContent = r.contenu;
    document.getElementById('modalMeta').innerHTML =
        `<span>🤖 ${r.agent}</span><span>📁 ${r.projet}</span><span>${r.type}</span><span>${r.date}</span>`;
    document.getElementById('modalRapport').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function fermerModalDirect() {
    document.getElementById('modalRapport').classList.remove('open');
    document.body.style.overflow = '';
}
function fermerModal(e) {
    if (e.target === document.getElementById('modalRapport')) fermerModalDirect();
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') fermerModalDirect(); });
</script>

@endsection

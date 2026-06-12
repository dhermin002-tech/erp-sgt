@extends('layouts.app')
@section('title', 'Membres')

@push('styles')
<style>
.page-header-simple {
    display:flex;align-items:center;justify-content:space-between;
    gap:1rem;flex-wrap:wrap;margin-bottom:1.25rem;
}
.page-header-simple h1 {
    font-family:'Space Grotesk',sans-serif;font-size:1.35rem;font-weight:700;color:var(--kt-navy);
    letter-spacing:-.02em;
}
.page-header-simple p { color:var(--slate-400);font-size:.84rem;margin-top:.2rem; }

.btn-new {
    display:inline-flex;align-items:center;gap:.45rem;
    background:#003366;color:#fff;padding:.55rem 1.1rem;
    border-radius:8px;text-decoration:none;font-size:.875rem;font-weight:600;
    box-shadow:0 4px 14px rgba(0,51,102,.25);transition:all .15s;white-space:nowrap;
}
.btn-new:hover { background:#004080;color:#fff; }
.btn-new-agent {
    display:inline-flex;align-items:center;gap:.45rem;
    background:#7c3aed;color:#fff;padding:.55rem 1.1rem;
    border-radius:8px;text-decoration:none;font-size:.875rem;font-weight:600;
    box-shadow:0 4px 14px rgba(124,58,237,.25);transition:all .15s;white-space:nowrap;
    border:none;cursor:pointer;
}
.btn-new-agent:hover { background:#6d28d9; }

/* ── Chips de filtre Membres ── */
.membres-chips { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1.5rem; }
.mchip {
    display:inline-flex; align-items:center; gap:.45rem;
    padding:.5rem .95rem; border-radius:999px;
    border:1.5px solid var(--slate-200); background:#fff;
    font-family:'Space Grotesk',sans-serif; font-size:.82rem; font-weight:700;
    color:var(--slate-600); cursor:pointer; transition:all .15s; white-space:nowrap;
}
.mchip:hover { border-color:#003366; color:#003366; }
.mchip.active { background:#003366; color:#fff; border-color:#003366; box-shadow:0 4px 12px rgba(0,51,102,.22); }
.mchip-count { display:inline-flex; align-items:center; justify-content:center; min-width:1.35rem; height:1.35rem; padding:0 .35rem; border-radius:999px; font-size:.7rem; font-weight:800; background:var(--slate-100); color:var(--slate-600); }
.mchip.active .mchip-count { background:rgba(255,255,255,.25); color:#fff; }
.mchip-agents { border-color:#DDD6FE; color:#6D28D9; }
.mchip-agents:hover { border-color:#6D28D9; color:#6D28D9; }
.mchip-agents.active { background:#6D28D9; border-color:#6D28D9; color:#fff; box-shadow:0 4px 12px rgba(109,40,217,.25); }
.membres-section.is-hidden { display:none; }

/* ── Section titre ── */
.section-sep {
    display:flex;align-items:center;gap:.75rem;margin:1.5rem 0 .9rem;
}
.section-sep-label {
    font-family:'Space Grotesk',sans-serif;font-size:.78rem;font-weight:700;
    text-transform:uppercase;letter-spacing:.08em;white-space:nowrap;
}
.section-sep-line { flex:1;height:1px; }

.section-sep.collab .section-sep-label { color:#003366; }
.section-sep.collab .section-sep-line  { background:#dbeafe; }
.section-sep.agents-ia .section-sep-label { color:#7c3aed; }
.section-sep.agents-ia .section-sep-line  { background:#ede9fe; }

/* ── Table ── */
.table-card {
    background:#fff;border-radius:14px;border:1px solid var(--slate-200);
    overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06);
}
table.kt-table { width:100%;border-collapse:collapse; }
table.kt-table thead th {
    padding:.8rem 1rem;text-align:left;
    background:#003366;color:rgba(255,255,255,.9);
    font-family:'Space Grotesk',sans-serif;font-size:.7rem;font-weight:700;
    letter-spacing:.07em;text-transform:uppercase;white-space:nowrap;
}
table.kt-table.agents-table thead th { background:#4c1d95; }
table.kt-table thead th:first-child { padding-left:1.25rem; }
table.kt-table tbody td {
    padding:.85rem 1rem;font-size:.875rem;color:var(--slate-700);
    border-bottom:1px solid var(--slate-100);vertical-align:middle;
}
table.kt-table tbody td:first-child { padding-left:1.25rem; }
table.kt-table tbody tr:nth-child(even) { background:#F8FAFF; }
table.kt-table.agents-table tbody tr:nth-child(even) { background:#faf5ff; }
table.kt-table tbody tr:hover { background:#EFF6FF; }
table.kt-table.agents-table tbody tr:hover { background:#f5f3ff; }
table.kt-table tbody tr:last-child td { border-bottom:none; }
table.kt-table tbody tr.is-me { background:#FFFBEB !important; }

.membre-avatar {
    width:42px;height:42px;border-radius:12px;
    display:flex;align-items:center;justify-content:center;
    font-family:'Space Grotesk',sans-serif;font-size:.85rem;font-weight:700;
    color:#fff;flex-shrink:0;
}
.membre-nom  { font-weight:700;color:var(--kt-navy);font-size:.9rem; }
.membre-vous { font-size:.7rem;color:#CC5500;font-weight:700;letter-spacing:.02em; }

.role-badge {
    display:inline-flex;align-items:center;gap:.3rem;
    font-size:.72rem;font-weight:700;padding:.25rem .7rem;border-radius:999px;
    white-space:nowrap;text-transform:capitalize;
}
.taches-count {
    display:inline-flex;align-items:center;justify-content:center;
    min-width:28px;height:28px;border-radius:8px;
    font-family:'Space Grotesk',sans-serif;font-size:.85rem;font-weight:700;
    background:var(--slate-100);color:var(--slate-600);
}
.taches-count.has-tasks { background:#DBEAFE;color:#1D4ED8; }

.action-link { font-size:.82rem;color:var(--kt-navy);text-decoration:none;font-weight:600;padding:.3rem .5rem;border-radius:6px;transition:background .12s; }
.action-link:hover { background:var(--slate-100); }
.action-del  { font-size:.82rem;color:#991B1B;background:none;border:none;cursor:pointer;font-weight:600;padding:.3rem .5rem;border-radius:6px;transition:background .12s; }
.action-del:hover { background:#FEE2E2; }

/* ── Accordéon agents ── */
.agents-accordeon-toggle {
    width:100%;text-align:left;cursor:pointer;
    background: linear-gradient(135deg, #1e0a3c, #3b0764);
    border:none;border-radius:12px;padding:1rem 1.25rem;
    display:flex;align-items:center;justify-content:space-between;
    font-family:'Space Grotesk',sans-serif;font-size:.9rem;font-weight:700;color:#fff;
    transition:all .2s;
}
.agents-accordeon-toggle:hover { opacity:.92; }
.agents-accordeon-toggle .toggle-chevron {
    transition:transform .3s cubic-bezier(.16,1,.3,1);
    font-size:1rem;
}
.agents-accordeon-toggle.open .toggle-chevron { transform:rotate(180deg); }
.agents-accordeon-body {
    display:none;margin-top:.6rem;
}
.agents-accordeon-body.open { display:block; }

/* Agent code mono */
.agent-code-chip {
    display:inline-flex;align-items:center;gap:.3rem;
    background:#f5f3ff;color:#5b21b6;
    font-family:'IBM Plex Mono',monospace;font-size:.75rem;font-weight:700;
    padding:.2rem .65rem;border-radius:20px;border:1px solid #ddd6fe;
}

/* Session pulse */
.session-live {
    display:inline-flex;align-items:center;gap:.35rem;
    background:#f0fdf4;color:#166534;font-size:.72rem;font-weight:700;
    padding:.2rem .65rem;border-radius:20px;border:1px solid #bbf7d0;
}
.session-live::before {
    content:'';width:6px;height:6px;border-radius:50%;background:#16a34a;
    animation:pulse-m 1.5s ease infinite;display:inline-block;
}
@keyframes pulse-m {
    0%,100%{opacity:1;transform:scale(1)}50%{opacity:.3;transform:scale(1.4)}
}

/* ── Modal agent ── */
.modal-overlay {
    display:none;position:fixed;inset:0;
    background:rgba(0,0,0,.55);z-index:1000;
    align-items:center;justify-content:center;
}
.modal-overlay.open { display:flex; }
.modal-box {
    background:#fff;border-radius:16px;width:min(520px,96vw);
    box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden;
}
.modal-header {
    padding:1.1rem 1.5rem;border-bottom:1px solid #f1f5f9;
    display:flex;align-items:center;justify-content:space-between;
    background:linear-gradient(135deg,#1e0a3c,#3b0764);
}
.modal-header-title {
    font-family:'Space Grotesk',sans-serif;font-size:.95rem;font-weight:700;color:#fff;
    display:flex;align-items:center;gap:.5rem;
}
.modal-close {
    background:rgba(255,255,255,.15);border:none;border-radius:6px;
    font-size:1rem;cursor:pointer;color:#fff;width:28px;height:28px;
    display:flex;align-items:center;justify-content:center;
    transition:background .15s;
}
.modal-close:hover { background:rgba(255,255,255,.25); }
.modal-body { padding:1.5rem; }
.modal-form-group { margin-bottom:1rem; }
.modal-label { font-size:.78rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:.35rem; }
.modal-input {
    width:100%;padding:.6rem .9rem;border:1.5px solid #e2e8f0;border-radius:9px;
    font-size:.9rem;color:#1e293b;outline:none;font-family:inherit;
    transition:border-color .15s;
}
.modal-input:focus { border-color:#7c3aed;box-shadow:0 0 0 3px rgba(124,58,237,.08); }
.modal-input.is-invalid { border-color:#f87171; }
.invalid-msg { font-size:.78rem;color:#dc2626;margin-top:.2rem;display:block; }
.modal-footer {
    padding:1rem 1.5rem;border-top:1px solid #f1f5f9;
    display:flex;justify-content:flex-end;gap:.65rem;
}
.btn-modal-cancel {
    padding:.55rem 1.1rem;background:#f1f5f9;color:#475569;border:none;
    border-radius:8px;font-size:.875rem;font-weight:600;cursor:pointer;font-family:inherit;
}
.btn-modal-submit {
    padding:.55rem 1.25rem;background:#7c3aed;color:#fff;border:none;
    border-radius:8px;font-size:.875rem;font-weight:600;cursor:pointer;font-family:inherit;
    transition:background .15s;
}
.btn-modal-submit:hover { background:#6d28d9; }

/* ── Badge niveau ── */
.niveau-badge {
    display:inline-flex;align-items:center;justify-content:center;
    width:28px;height:28px;border-radius:8px;
    font-family:'Space Grotesk',sans-serif;font-size:.72rem;font-weight:800;
    letter-spacing:-.01em;flex-shrink:0;
}
.niveau-pip {
    display:inline-flex;gap:2px;align-items:center;
}
.niveau-pip span {
    width:6px;height:6px;border-radius:50%;
}
/* séparateur de groupe dans la table */
.group-separator td {
    padding:.3rem 1.25rem .2rem;
    background:linear-gradient(90deg,#f8faff,#fff);
    font-family:'Space Grotesk',sans-serif;font-size:.68rem;font-weight:800;
    text-transform:uppercase;letter-spacing:.09em;color:#94a3b8;
    border-bottom:1px solid #e2e8f0;
}

@media (max-width:640px) {
    table.kt-table thead th.hide-mobile,
    table.kt-table tbody td.hide-mobile { display:none; }
    .membre-avatar { width:36px;height:36px;border-radius:10px;font-size:.78rem; }
}
</style>
@endpush

@section('content')
@php
$roleConfig = [
    'manager'     => ['bg'=>'#EFF6FF','color'=>'#1D4ED8','icon'=>'bi-shield-check',  'niveau'=>1,'label'=>'N1'],
    'developpeur' => ['bg'=>'#F5F3FF','color'=>'#7C3AED','icon'=>'bi-code-slash',    'niveau'=>2,'label'=>'N2'],
    'technicien'  => ['bg'=>'#F0FDF4','color'=>'#15803D','icon'=>'bi-tools',          'niveau'=>3,'label'=>'N3'],
    'agent'       => ['bg'=>'#FFF7ED','color'=>'#C2410C','icon'=>'bi-person-badge',   'niveau'=>4,'label'=>'N4'],
    'stagiaire'   => ['bg'=>'#F1F5F9','color'=>'#475569','icon'=>'bi-mortarboard',    'niveau'=>5,'label'=>'N5'],
];
$avatarColors = ['#003366','#CC5500','#7C3AED','#059669','#DC2626','#D97706','#0891B2','#BE185D'];
@endphp

{{-- En-tête ── --}}
<div class="page-header-simple">
    <div>
        <h1><i class="bi bi-people" style="margin-right:.4rem;color:#CC5500"></i>Équipe</h1>
        <p>{{ $humains->count() }} collaborateur(s) · {{ $agentsIa->count() }} agent(s) IA</p>
    </div>
    <div style="display:flex;gap:.6rem;flex-wrap:wrap">
        <a href="{{ route('membres.create') }}" class="btn-new">
            <i class="bi bi-person-plus"></i> Nouveau membre
        </a>
        <button class="btn-new-agent" onclick="ouvrirModalAgent()">
            🤖 Nouvel agent IA
        </button>
    </div>
</div>

{{-- Flash ── --}}
@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:.75rem 1rem;color:#166534;font-size:.875rem;margin-bottom:1.25rem;display:flex;gap:.5rem;align-items:center">
    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
</div>
@endif

{{-- Chips de filtre Collaborateurs / Agents IA --}}
<div class="membres-chips" id="membresChips">
    <button type="button" class="mchip active" data-target="all">📋 Tous</button>
    <button type="button" class="mchip" data-target="collab">👥 Collaborateurs <span class="mchip-count">{{ $humains->count() }}</span></button>
    <button type="button" class="mchip mchip-agents" data-target="agents">🤖 Agents IA <span class="mchip-count">{{ $agentsIa->count() }}</span></button>
</div>

{{-- ═══ BLOC 1 : Collaborateurs ═══ --}}
<div class="membres-section" data-msection="collab">
<div class="section-sep collab">
    <div class="section-sep-line"></div>
    <span class="section-sep-label"><i class="bi bi-people-fill me-1"></i> Collaborateurs ({{ $humains->count() }})</span>
    <div class="section-sep-line"></div>
</div>

<div class="table-card">
    @if($humains->isEmpty())
    <div style="text-align:center;padding:3rem;color:var(--slate-400)">
        <i class="bi bi-people" style="font-size:2.5rem;display:block;margin-bottom:.5rem;opacity:.3"></i>
        Aucun collaborateur humain.
    </div>
    @else
    <div style="overflow-x:auto">
        <table class="kt-table">
            <thead>
                <tr>
                    <th style="width:36px">Niv.</th>
                    <th>Membre</th>
                    <th class="hide-mobile">Identifiant</th>
                    <th>Rôle</th>
                    <th class="hide-mobile">Tâches actives</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @php $dernierNiveau = null; @endphp
                @foreach($humains as $i => $membre)
                @php
                    $rc       = $roleConfig[$membre->role] ?? $roleConfig['stagiaire'];
                    $aColor   = $avatarColors[$rc['niveau'] % count($avatarColors)];
                    $initiales= strtoupper(mb_substr($membre->prenom??'',0,1).mb_substr($membre->nom,0,1));
                    $nbTaches = $membre->taches()->whereNull('archived_at')->where('statut','!=','termine')->count();
                    $niveauLabel = ['manager'=>'Management','developpeur'=>'Développement','technicien'=>'Technique','agent'=>'Agent','stagiaire'=>'Stagiaire'];
                @endphp

                {{-- Séparateur de groupe quand le niveau change --}}
                @if($dernierNiveau !== $rc['niveau'])
                <tr class="group-separator">
                    <td colspan="6">
                        <span class="niveau-badge" style="background:{{ $rc['bg'] }};color:{{ $rc['color'] }};margin-right:.5rem">{{ $rc['label'] }}</span>
                        {{ $niveauLabel[$membre->role] ?? $membre->role }}
                    </td>
                </tr>
                @php $dernierNiveau = $rc['niveau']; @endphp
                @endif

                <tr class="{{ $membre->id === auth()->id() ? 'is-me' : '' }}">
                    <td>
                        {{-- Pastilles de niveau --}}
                        <div class="niveau-pip">
                            @for($p = 1; $p <= 5; $p++)
                            <span style="background:{{ $p <= $rc['niveau'] ? $rc['color'] : '#e2e8f0' }};opacity:{{ $p <= $rc['niveau'] ? 1 : 0.4 }}"></span>
                            @endfor
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.75rem">
                            <div class="membre-avatar" style="background:{{ $aColor }}">{{ $initiales }}</div>
                            <div>
                                <div class="membre-nom">{{ $membre->nom_complet }}</div>
                                @if($membre->id === auth()->id())
                                <div class="membre-vous"><i class="bi bi-star-fill"></i> Vous</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="hide-mobile" style="font-family:'Space Grotesk',sans-serif;color:var(--slate-500);font-size:.84rem">{{ $membre->username }}</td>
                    <td>
                        <span class="role-badge" style="background:{{ $rc['bg'] }};color:{{ $rc['color'] }}">
                            <i class="bi {{ $rc['icon'] }}"></i> {{ $membre->role }}
                        </span>
                    </td>
                    <td class="hide-mobile">
                        <span class="taches-count {{ $nbTaches > 0 ? 'has-tasks' : '' }}">{{ $nbTaches }}</span>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.25rem">
                            <a href="{{ route('membres.edit', $membre) }}" class="action-link" title="Modifier"><i class="bi bi-pencil"></i></a>
                            @if($membre->id !== auth()->id())
                            <form method="POST" action="{{ route('membres.destroy', $membre) }}" onsubmit="return confirm('Supprimer {{ $membre->nom_complet }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-del" title="Supprimer"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

</div>{{-- /membres-section collab --}}

{{-- ═══ BLOC 2 : Agents IA (accordéon) ═══ --}}
<div class="membres-section" data-msection="agents">
<div class="section-sep agents-ia" style="margin-top:2rem">
    <div class="section-sep-line"></div>
    <span class="section-sep-label">🤖 Agents IA ({{ $agentsIa->count() }})</span>
    <div class="section-sep-line"></div>
</div>

<button class="agents-accordeon-toggle" id="agentsToggle" onclick="toggleAgents()" aria-expanded="false">
    <span style="display:flex;align-items:center;gap:.65rem">
        <span style="background:rgba(255,255,255,.15);border-radius:8px;padding:.25rem .5rem;font-size:.8rem">🤖</span>
        {{ $agentsIa->count() }} agent(s) IA enregistré(s)
        @php $actifs = $agentsIa->filter(fn($a) => $a->sessionActive() !== null)->count(); @endphp
        @if($actifs > 0)
        <span style="background:rgba(21,128,61,.3);color:#86efac;font-size:.72rem;padding:.15rem .55rem;border-radius:20px;font-weight:700">
            {{ $actifs }} en session
        </span>
        @endif
    </span>
    <i class="bi bi-chevron-down toggle-chevron"></i>
</button>

<div class="agents-accordeon-body" id="agentsBody">
    @if($agentsIa->isEmpty())
    <div style="text-align:center;padding:2.5rem;color:#94a3b8;font-size:.9rem">
        Aucun agent IA enregistré. Utilisez le bouton "Nouvel agent IA" ci-dessus.
    </div>
    @else
    <div class="table-card" style="margin-top:0">
        <div style="overflow-x:auto">
            <table class="kt-table agents-table">
                <thead>
                    <tr>
                        <th style="width:32px">Act.</th>
                        <th>Agent</th>
                        <th>Code</th>
                        <th class="hide-mobile">Session</th>
                        <th class="hide-mobile">Rapports aujourd'hui</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agentsIa as $agent)
                    @php
                        // Relations déjà eager-loaded dans le contrôleur (sessions en_cours + rapports du jour)
                        $session  = $agent->sessionsAgents->first();
                        $rapports = $agent->rapportsAgents->count();
                        $estActif = $session !== null;
                        $rang     = $agent->rangHierarchique();
                    @endphp
                    <tr>
                        <td>
                            {{-- Indicateur d'activité --}}
                            @if($estActif)
                            <span title="En session" style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#16a34a;animation:pulse-m 1.5s ease infinite"></span>
                            @else
                            <span title="Inactif" style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#e2e8f0"></span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:.75rem">
                                <div class="membre-avatar" style="background:{{ $agent->agent_couleur ?? '#7c3aed' }};border-radius:50%">
                                    {{ strtoupper(substr($agent->agent_code ?? 'A', 0, 2)) }}
                                </div>
                                <div>
                                    <div class="membre-nom" style="color:#4c1d95;display:flex;align-items:center;gap:.4rem">
                                        <span title="Rang hiérarchique" style="font-size:.6rem;font-weight:800;background:#ede9fe;color:#6d28d9;padding:.1rem .4rem;border-radius:6px">#{{ $rang }}</span>
                                        {{ $agent->nom_complet }}
                                    </div>
                                    <div style="font-size:.72rem;color:#94a3b8">{{ $agent->username }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="agent-code-chip">{{ $agent->agent_code }}</span>
                        </td>
                        <td class="hide-mobile">
                            @if($session)
                            <span class="session-live">En session — {{ $session->projet }}</span>
                            @else
                            <span style="font-size:.8rem;color:#94a3b8">Hors session</span>
                            @endif
                        </td>
                        <td class="hide-mobile">
                            @if($rapports > 0)
                            <span style="background:#f5f3ff;color:#7c3aed;font-size:.78rem;font-weight:700;padding:.2rem .55rem;border-radius:20px">
                                {{ $rapports }} rapport(s)
                            </span>
                            @else
                            <span style="font-size:.8rem;color:#94a3b8">—</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:.25rem">
                                <a href="{{ route('membres.edit', $agent) }}" class="action-link" style="color:#7c3aed" title="Modifier"><i class="bi bi-pencil"></i></a>
                                <a href="{{ route('agents.rapports') }}?agent_id={{ $agent->id }}" class="action-link" style="color:#7c3aed" title="Voir rapports"><i class="bi bi-file-earmark-text"></i></a>
                                <form method="POST" action="{{ route('membres.destroy', $agent) }}" onsubmit="return confirm('Supprimer l\'agent {{ $agent->agent_code }} ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-del" title="Supprimer"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
</div>{{-- /membres-section agents --}}

{{-- Modal création agent IA ── --}}
<div class="modal-overlay" id="modalAgent" onclick="fermerModalAgent(event)">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-header-title">🤖 Nouvel agent IA</div>
            <button class="modal-close" onclick="document.getElementById('modalAgent').classList.remove('open')">✕</button>
        </div>
        <form method="POST" action="{{ route('membres.storeAgent') }}">
            @csrf
            <div class="modal-body">
                <div class="modal-form-group">
                    <label class="modal-label">Nom affiché <span style="color:#dc2626">*</span></label>
                    <input type="text" name="nom" class="modal-input {{ $errors->has('nom') ? 'is-invalid' : '' }}"
                           value="{{ old('nom') }}" placeholder="ex: DevAgent KT" required>
                    @error('nom') <span class="invalid-msg">{{ $message }}</span> @enderror
                </div>
                <div class="modal-form-group">
                    <label class="modal-label">Identifiant (username) <span style="color:#dc2626">*</span></label>
                    <input type="text" name="username" class="modal-input {{ $errors->has('username') ? 'is-invalid' : '' }}"
                           value="{{ old('username') }}" placeholder="ex: agent.nouveau" required>
                    @error('username') <span class="invalid-msg">{{ $message }}</span> @enderror
                </div>
                <div class="modal-form-group">
                    <label class="modal-label">Code agent <span style="color:#dc2626">*</span></label>
                    <input type="text" name="agent_code" class="modal-input {{ $errors->has('agent_code') ? 'is-invalid' : '' }}"
                           value="{{ old('agent_code') }}" placeholder="ex: mon-agent (minuscules, tirets)" required>
                    <span style="font-size:.75rem;color:#94a3b8;margin-top:.2rem;display:block">Utilisé pour générer le token : <code>php artisan sgt:agent-token --agent=mon-agent</code></span>
                    @error('agent_code') <span class="invalid-msg">{{ $message }}</span> @enderror
                </div>
                <div class="modal-form-group">
                    <label class="modal-label">Couleur avatar</label>
                    <div style="display:flex;align-items:center;gap:.75rem">
                        <input type="color" name="agent_couleur" value="{{ old('agent_couleur', '#7c3aed') }}"
                               style="width:48px;height:38px;border-radius:8px;border:1.5px solid #e2e8f0;padding:2px;cursor:pointer">
                        <span style="font-size:.82rem;color:#64748b">Couleur de l'avatar de l'agent dans l'interface</span>
                    </div>
                    @error('agent_couleur') <span class="invalid-msg">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="document.getElementById('modalAgent').classList.remove('open')">Annuler</button>
                <button type="submit" class="btn-modal-submit">🤖 Créer l'agent</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleAgents() {
    const toggle = document.getElementById('agentsToggle');
    const body   = document.getElementById('agentsBody');
    const open   = body.classList.toggle('open');
    toggle.classList.toggle('open', open);
    toggle.setAttribute('aria-expanded', open);
}

// ── Chips de filtre Membres (Tous / Collaborateurs / Agents IA) ──
document.addEventListener('DOMContentLoaded', () => {
    const chips = document.querySelectorAll('.mchip');
    chips.forEach(chip => {
        chip.addEventListener('click', () => {
            chips.forEach(c => c.classList.toggle('active', c === chip));
            const target = chip.dataset.target;
            document.querySelectorAll('.membres-section').forEach(sec => {
                const visible = (target === 'all' || sec.dataset.msection === target);
                sec.classList.toggle('is-hidden', !visible);
            });
            // Si on cible les agents, déplier automatiquement l'accordéon
            if (target === 'agents') {
                const body = document.getElementById('agentsBody');
                const tog  = document.getElementById('agentsToggle');
                if (body && !body.classList.contains('open')) { body.classList.add('open'); tog.classList.add('open'); }
            }
        });
    });
});

function ouvrirModalAgent() {
    document.getElementById('modalAgent').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function fermerModalAgent(e) {
    if (e.target === document.getElementById('modalAgent')) {
        document.getElementById('modalAgent').classList.remove('open');
        document.body.style.overflow = '';
    }
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.getElementById('modalAgent').classList.remove('open');
        document.body.style.overflow = '';
    }
});

// Rouvrir modal si erreur de validation
@if($errors->hasAny(['nom','username','agent_code','agent_couleur']))
document.getElementById('modalAgent').classList.add('open');
@endif
</script>
@endpush

@endsection

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

.table-card { background:#fff;border-radius:14px;border:1px solid var(--slate-200);overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06); }

table.kt-table { width:100%;border-collapse:collapse; }
table.kt-table thead th {
    padding:.8rem 1rem;text-align:left;
    background:#003366;color:rgba(255,255,255,.9);
    font-family:'Space Grotesk',sans-serif;font-size:.7rem;font-weight:700;
    letter-spacing:.07em;text-transform:uppercase;white-space:nowrap;
}
table.kt-table thead th:first-child { padding-left:1.25rem; }
table.kt-table tbody td {
    padding:.85rem 1rem;font-size:.875rem;color:var(--slate-700);
    border-bottom:1px solid var(--slate-100);vertical-align:middle;
}
table.kt-table tbody td:first-child { padding-left:1.25rem; }
table.kt-table tbody tr:nth-child(even) { background:#F8FAFF; }
table.kt-table tbody tr:hover { background:#EFF6FF; }
table.kt-table tbody tr:last-child td { border-bottom:none; }
table.kt-table tbody tr.is-me { background:#FFFBEB !important; }
table.kt-table tbody tr.is-me:hover { background:#FEF9C3 !important; }

.membre-avatar {
    width:42px;height:42px;border-radius:12px;
    display:flex;align-items:center;justify-content:center;
    font-family:'Space Grotesk',sans-serif;font-size:.85rem;font-weight:700;
    color:#fff;flex-shrink:0;
}
.membre-nom { font-weight:700;color:var(--kt-navy);font-size:.9rem; }
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
    'manager'     => ['bg'=>'#EFF6FF','color'=>'#1D4ED8','icon'=>'bi-shield-check'],
    'technicien'  => ['bg'=>'#F0FDF4','color'=>'#15803D','icon'=>'bi-tools'],
    'agent'       => ['bg'=>'#FFF7ED','color'=>'#C2410C','icon'=>'bi-person-badge'],
    'developpeur' => ['bg'=>'#F5F3FF','color'=>'#7C3AED','icon'=>'bi-code-slash'],
    'stagiaire'   => ['bg'=>'#F1F5F9','color'=>'#475569','icon'=>'bi-mortarboard'],
];
$avatarColors = ['#003366','#CC5500','#7C3AED','#059669','#DC2626','#D97706','#0891B2','#BE185D'];
@endphp

<div class="page-header-simple">
    <div>
        <h1><i class="bi bi-people" style="margin-right:.4rem;color:#CC5500"></i>Membres</h1>
        <p>{{ $membres->total() }} membre(s) dans l'équipe</p>
    </div>
    <a href="{{ route('membres.create') }}" class="btn-new">
        <i class="bi bi-plus-lg"></i> Nouveau membre
    </a>
</div>

<div class="table-card">
    @if($membres->isEmpty())
    <div style="text-align:center;padding:3rem;color:var(--slate-400)">
        <i class="bi bi-people" style="font-size:2.5rem;display:block;margin-bottom:.5rem;opacity:.3"></i>
        Aucun membre trouvé.
    </div>
    @else
    <div style="overflow-x:auto">
        <table class="kt-table">
            <thead>
                <tr>
                    <th>Membre</th>
                    <th class="hide-mobile">Identifiant</th>
                    <th>Rôle</th>
                    <th class="hide-mobile">Tâches actives</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($membres as $i => $membre)
                @php
                    $rc     = $roleConfig[$membre->role] ?? $roleConfig['stagiaire'];
                    $aColor = $avatarColors[$i % count($avatarColors)];
                    $initiales = strtoupper(mb_substr($membre->prenom ?? '',0,1).mb_substr($membre->nom,0,1));
                    $nbTaches = $membre->taches()->actives()->count();
                @endphp
                <tr class="{{ $membre->id === auth()->id() ? 'is-me' : '' }}">
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
                    <td class="hide-mobile" style="font-family:'Space Grotesk',sans-serif;color:var(--slate-500);font-size:.84rem">
                        {{ $membre->username }}
                    </td>
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
                            <a href="{{ route('membres.edit', $membre) }}" class="action-link">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if($membre->id !== auth()->id())
                            <form method="POST" action="{{ route('membres.destroy', $membre) }}" onsubmit="return confirm('Supprimer ce membre ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-del"><i class="bi bi-trash"></i></button>
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

@if($membres->hasPages())
<div style="margin-top:1rem">{{ $membres->links('pagination::bootstrap-4') }}</div>
@endif
@endsection

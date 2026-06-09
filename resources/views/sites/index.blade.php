@extends('layouts.app')
@section('title', "Sites d'intervention")

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

.site-icon {
    width:40px;height:40px;border-radius:10px;
    background:linear-gradient(135deg,#001f3f,#003366);
    display:flex;align-items:center;justify-content:center;
    color:#fff;font-size:1.1rem;flex-shrink:0;
}
.site-nom { font-weight:700;color:var(--kt-navy);font-size:.9rem; }
.site-desc { font-size:.76rem;color:var(--slate-400);margin-top:.1rem; }

.taches-pill {
    display:inline-flex;align-items:center;gap:.35rem;
    font-family:'Space Grotesk',sans-serif;font-size:.82rem;font-weight:700;
    padding:.25rem .65rem;border-radius:8px;
}
.taches-pill.zero { background:var(--slate-100);color:var(--slate-400); }
.taches-pill.few  { background:#DBEAFE;color:#1D4ED8; }
.taches-pill.many { background:#FEF3C7;color:#B45309; }

.statut-badge {
    display:inline-flex;align-items:center;gap:.3rem;
    font-size:.72rem;font-weight:700;padding:.25rem .7rem;border-radius:999px;
}
.statut-badge.actif   { background:#D1FAE5;color:#065F46; }
.statut-badge.inactif { background:var(--slate-100);color:var(--slate-500); }

.action-link { font-size:.82rem;color:var(--kt-navy);text-decoration:none;font-weight:600;padding:.3rem .5rem;border-radius:6px;transition:background .12s; }
.action-link:hover { background:var(--slate-100); }
.action-del  { font-size:.82rem;color:#991B1B;background:none;border:none;cursor:pointer;font-weight:600;padding:.3rem .5rem;border-radius:6px;transition:background .12s; }
.action-del:hover { background:#FEE2E2; }

@media (max-width:640px) {
    table.kt-table thead th.hide-mobile,
    table.kt-table tbody td.hide-mobile { display:none; }
}
</style>
@endpush

@section('content')

<div class="page-header-simple">
    <div>
        <h1><i class="bi bi-geo-alt" style="margin-right:.4rem;color:#CC5500"></i>Sites d'intervention</h1>
        <p>{{ $sites->total() }} site(s) enregistré(s)</p>
    </div>
    <a href="{{ route('sites.create') }}" class="btn-new">
        <i class="bi bi-plus-lg"></i> Nouveau site
    </a>
</div>

<div class="table-card">
    @if($sites->isEmpty())
    <div style="text-align:center;padding:3rem;color:var(--slate-400)">
        <i class="bi bi-geo-alt" style="font-size:2.5rem;display:block;margin-bottom:.5rem;opacity:.3"></i>
        <div style="font-weight:600">Aucun site enregistré</div>
        <div style="margin-top:.5rem"><a href="{{ route('sites.create') }}" style="color:var(--kt-navy)">Créer le premier site</a></div>
    </div>
    @else
    <div style="overflow-x:auto">
        <table class="kt-table">
            <thead>
                <tr>
                    <th>Site</th>
                    <th class="hide-mobile">Ville</th>
                    <th>Tâches actives</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($sites as $site)
                @php
                    $n = $site->taches_count ?? 0;
                    $pillClass = $n === 0 ? 'zero' : ($n >= 5 ? 'many' : 'few');
                @endphp
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:.75rem">
                            <div class="site-icon"><i class="bi bi-building"></i></div>
                            <div>
                                <div class="site-nom">{{ $site->nom }}</div>
                                @if($site->adresse ?? null)
                                <div class="site-desc">{{ $site->adresse }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="hide-mobile" style="color:var(--slate-500)">{{ $site->ville ?? '—' }}</td>
                    <td>
                        <span class="taches-pill {{ $pillClass }}">
                            <i class="bi bi-list-task"></i> {{ $n }}
                        </span>
                    </td>
                    <td>
                        @if($site->actif)
                        <span class="statut-badge actif"><i class="bi bi-check-circle-fill"></i> Actif</span>
                        @else
                        <span class="statut-badge inactif"><i class="bi bi-slash-circle"></i> Inactif</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.25rem">
                            <a href="{{ route('sites.edit', $site) }}" class="action-link">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('sites.destroy', $site) }}" onsubmit="return confirm('Supprimer ce site ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-del"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@if($sites->hasPages())
<div style="margin-top:1rem">{{ $sites->links('pagination::bootstrap-4') }}</div>
@endif
@endsection

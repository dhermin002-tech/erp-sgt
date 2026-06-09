@extends('layouts.app')
@section('title', 'Archives')

@push('styles')
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
.btn-back {
    display:inline-flex;align-items:center;gap:.4rem;
    background:rgba(255,255,255,.12);color:rgba(255,255,255,.85);
    padding:.45rem .9rem;border-radius:8px;text-decoration:none;font-size:.84rem;font-weight:600;
    border:1px solid rgba(255,255,255,.18);transition:all .15s;position:relative;z-index:1;
    white-space:nowrap;
}
.btn-back:hover { background:rgba(255,255,255,.2);color:#fff; }

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
    padding:.82rem 1rem;font-size:.875rem;color:var(--slate-700);
    border-bottom:1px solid var(--slate-100);vertical-align:middle;
}
table.kt-table tbody td:first-child { padding-left:1.25rem; }
table.kt-table tbody tr:nth-child(even) { background:#F8FAFF; }
table.kt-table tbody tr:hover { background:#EFF6FF; }
table.kt-table tbody tr:last-child td { border-bottom:none; }

.task-link { font-weight:600;color:var(--kt-navy);text-decoration:none;font-size:.9rem; }
.task-link:hover { text-decoration:underline; }

.btn-restore {
    display:inline-flex;align-items:center;gap:.35rem;
    background:var(--slate-100);color:var(--slate-700);
    border:1px solid var(--slate-200);border-radius:7px;
    padding:.3rem .7rem;font-size:.8rem;font-weight:600;cursor:pointer;
    transition:all .15s;
}
.btn-restore:hover { background:#DBEAFE;color:#1D4ED8;border-color:#BFDBFE; }

.empty-state {
    text-align:center;padding:4rem 2rem;
}
.empty-state .empty-icon {
    width:72px;height:72px;border-radius:20px;
    background:linear-gradient(135deg,#F1F5F9,#E2E8F0);
    display:flex;align-items:center;justify-content:center;
    font-size:2rem;margin:0 auto 1.1rem;color:var(--slate-400);
}
.empty-state h3 { font-family:'Space Grotesk',sans-serif;font-size:1rem;font-weight:700;color:var(--slate-600);margin-bottom:.4rem; }
.empty-state p  { font-size:.84rem;color:var(--slate-400);margin-bottom:1.25rem; }

@media (max-width:640px) {
    table.kt-table thead th.hide-mobile,
    table.kt-table tbody td.hide-mobile { display:none; }
}
</style>
@endpush

@section('content')

<div class="page-header">
    <div>
        <h1><i class="bi bi-archive" style="margin-right:.5rem"></i>Tâches archivées</h1>
        <p>{{ $taches->total() }} tâche(s) terminée(s) ou archivée(s)</p>
    </div>
    <a href="{{ route('taches.index') }}" class="btn-back">
        <i class="bi bi-arrow-left"></i> Tâches actives
    </a>
</div>

<div class="table-card">
    @if($taches->isEmpty())
    <div class="empty-state">
        <div class="empty-icon"><i class="bi bi-archive"></i></div>
        <h3>Aucune tâche archivée pour l'instant</h3>
        <p>Les tâches terminées ou archivées apparaîtront ici.</p>
        <a href="{{ route('taches.index') }}" style="display:inline-flex;align-items:center;gap:.4rem;background:var(--kt-navy);color:#fff;padding:.5rem 1.1rem;border-radius:8px;text-decoration:none;font-size:.875rem;font-weight:600">
            <i class="bi bi-check2-square"></i> Voir les tâches actives
        </a>
    </div>
    @else
    <div style="overflow-x:auto">
        <table class="kt-table">
            <thead>
                <tr>
                    <th>Tâche</th>
                    <th class="hide-mobile">Responsables</th>
                    <th class="hide-mobile">Site</th>
                    <th>Archivée le</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($taches as $tache)
                <tr>
                    <td>
                        <div>
                            <a href="{{ route('taches.show', $tache) }}" class="task-link">{{ $tache->titre }}</a>
                            <div style="margin-top:.25rem">
                                @include('partials.badge_statut', ['statut' => $tache->statut])
                            </div>
                        </div>
                    </td>
                    <td class="hide-mobile" style="color:var(--slate-500);font-size:.84rem">
                        {{ $tache->responsables->pluck('nom')->implode(', ') ?: '—' }}
                    </td>
                    <td class="hide-mobile" style="color:var(--slate-500);font-size:.84rem">
                        {{ $tache->site?->nom ?? '—' }}
                    </td>
                    <td style="color:var(--slate-500);font-size:.84rem;white-space:nowrap">
                        <i class="bi bi-calendar3" style="margin-right:.3rem;opacity:.5"></i>
                        {{ $tache->archived_at?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td>
                        <form method="POST" action="{{ route('taches.restaurer', $tache) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn-restore">
                                <i class="bi bi-arrow-counterclockwise"></i> Restaurer
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@if($taches->hasPages())
<div style="margin-top:1rem">{{ $taches->links('pagination::bootstrap-4') }}</div>
@endif
@endsection

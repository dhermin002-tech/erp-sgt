@extends('layouts.app')
@section('title', 'Tâches')

@push('styles')
<style>
.table-container { background:var(--white);border-radius:12px;border:1px solid var(--slate-200);overflow:hidden; }
.data-table { width:100%;border-collapse:collapse; }
.data-table th { background:var(--slate-50);padding:.65rem 1rem;font-size:.78rem;font-weight:700;color:var(--slate-600);text-align:left;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--slate-200); }
.data-table th a { color:inherit;text-decoration:none;display:flex;align-items:center;gap:.3rem; }
.data-table td { padding:.75rem 1rem;font-size:.875rem;color:var(--slate-700);border-bottom:1px solid var(--slate-100); }
.data-table tr:last-child td { border-bottom:none; }
.data-table tr:hover td { background:var(--slate-50); }
.filters-bar { background:var(--white);border-radius:12px;border:1px solid var(--slate-200);padding:1rem;margin-bottom:1rem;display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end; }
.filter-group { display:flex;flex-direction:column;gap:.3rem; }
.filter-label { font-size:.75rem;font-weight:600;color:var(--slate-500);text-transform:uppercase;letter-spacing:.04em; }
.filter-input { padding:.45rem .75rem;border:1.5px solid var(--slate-200);border-radius:7px;font-size:.85rem;color:var(--slate-700);background:var(--slate-50);outline:none; }
.filter-input:focus { border-color:var(--kt-navy); }
.btn { display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;border-radius:7px;font-size:.875rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;transition:all .15s; }
.btn-primary { background:var(--kt-navy);color:#fff; }
.btn-primary:hover { background:var(--kt-navy-700); }
.btn-sm { padding:.3rem .65rem;font-size:.8rem; }
.btn-ghost { background:none;color:var(--slate-600);border:1px solid var(--slate-200); }
.btn-ghost:hover { background:var(--slate-50); }
.btn-danger { background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5; }
.btn-danger:hover { background:#FCA5A5; }
.priorite-urgente { color:#B0202E;font-weight:700; }
.priorite-haute    { color:#C97A0A;font-weight:700; }
.progress-bar-wrap { width:80px;background:var(--slate-100);border-radius:999px;height:6px; }
.progress-bar-fill { height:6px;border-radius:999px;background:var(--kt-navy);transition:width .3s; }
.empty-state { text-align:center;padding:3rem;color:var(--slate-400); }
.page-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem; }
.retard-row td { background:#FFF5F5 !important; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--kt-navy)">Tâches actives</h1>
        <p style="color:var(--slate-500);font-size:.85rem;margin-top:.15rem">{{ $taches->total() }} tâche(s) trouvée(s)</p>
    </div>
    <a href="{{ route('taches.create') }}" class="btn btn-primary">+ Nouvelle tâche</a>
</div>

{{-- Filtres --}}
<form method="GET" action="{{ route('taches.index') }}" class="filters-bar">
    <div class="filter-group" style="flex:1;min-width:160px">
        <label class="filter-label">Recherche</label>
        <input type="text" name="q" class="filter-input" placeholder="Titre ou description..." value="{{ request('q') }}">
    </div>
    <div class="filter-group">
        <label class="filter-label">Statut</label>
        <select name="statut" class="filter-input">
            <option value="">Tous</option>
            @foreach(['nouveau','en_cours','en_attente','en_arret','termine'] as $s)
            <option value="{{ $s }}" {{ request('statut') === $s ? 'selected' : '' }}>
                {{ ['nouveau'=>'Nouveau','en_cours'=>'En cours','en_attente'=>'En attente','en_arret'=>'En arrêt','termine'=>'Terminé'][$s] }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <label class="filter-label">Site</label>
        <select name="site_id" class="filter-input">
            <option value="">Tous</option>
            @foreach($sites as $site)
            <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>{{ $site->nom }}</option>
            @endforeach
        </select>
    </div>
    @if(auth()->user()->isManager())
    <div class="filter-group">
        <label class="filter-label">Responsable</label>
        <select name="responsable_id" class="filter-input">
            <option value="">Tous</option>
            @foreach($membres as $m)
            <option value="{{ $m->id }}" {{ request('responsable_id') == $m->id ? 'selected' : '' }}>{{ $m->nom_complet }}</option>
            @endforeach
        </select>
    </div>
    @endif
    <div class="filter-group" style="flex-direction:row;align-items:flex-end;gap:.5rem">
        <button type="submit" class="btn btn-primary">Filtrer</button>
        @if(request()->hasAny(['q','statut','site_id','responsable_id']))
        <a href="{{ route('taches.index') }}" class="btn btn-ghost">Réinitialiser</a>
        @endif
    </div>
</form>

{{-- Tableau --}}
<div class="table-container" style="overflow-x:auto;overscroll-behavior-x:contain">
    @if($taches->isEmpty())
    <div class="empty-state">
        <div style="font-size:2.5rem;margin-bottom:.5rem">📋</div>
        <div style="font-weight:600">Aucune tâche trouvée</div>
        <div style="font-size:.875rem;margin-top:.25rem">
            <a href="{{ route('taches.create') }}">Créer la première tâche</a>
        </div>
    </div>
    @else
    <table class="data-table">
        <thead>
            <tr>
                <th><a href="{{ request()->fullUrlWithQuery(['sort'=>'titre','dir'=> request('sort')==='titre' && request('dir')==='asc' ? 'desc' : 'asc']) }}">Titre {{ request('sort')==='titre' ? (request('dir')==='asc' ? '↑' : '↓') : '' }}</a></th>
                <th>Statut</th>
                <th>Priorité</th>
                <th>Responsable(s)</th>
                <th>Site</th>
                <th><a href="{{ request()->fullUrlWithQuery(['sort'=>'date_echeance','dir'=> request('sort')==='date_echeance' && request('dir')==='asc' ? 'desc' : 'asc']) }}">Échéance {{ request('sort')==='date_echeance' ? (request('dir')==='asc' ? '↑' : '↓') : '' }}</a></th>
                <th><a href="{{ request()->fullUrlWithQuery(['sort'=>'progression','dir'=> request('sort')==='progression' && request('dir')==='asc' ? 'desc' : 'asc']) }}">Avancement</a></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($taches as $tache)
        <tr class="{{ $tache->estEnRetard() ? 'retard-row' : '' }}">
            <td>
                <a href="{{ route('taches.show', $tache) }}" style="font-weight:600;color:var(--kt-navy);text-decoration:none">
                    {{ $tache->titre }}
                </a>
                @if($tache->estEnRetard())
                <span style="background:#B0202E;color:#fff;font-size:.65rem;font-weight:700;padding:.1rem .4rem;border-radius:999px;margin-left:.35rem">En retard</span>
                @endif
                @if($tache->sousTaches->count() > 0)
                <span style="font-size:.72rem;color:var(--slate-400);margin-left:.35rem">{{ $tache->sousTaches->where('termine',true)->count() }}/{{ $tache->sousTaches->count() }} sous-tâches</span>
                @endif
            </td>
            <td>@include('partials.badge_statut', ['statut' => $tache->statut])</td>
            <td>
                <span class="priorite-{{ $tache->priorite }}">
                    {{ ucfirst($tache->priorite) }}
                </span>
            </td>
            <td style="max-width:160px">
                @foreach($tache->responsables->take(2) as $r)
                <span style="display:inline-block;background:var(--slate-100);color:var(--slate-600);font-size:.72rem;padding:.1rem .4rem;border-radius:4px;margin:.1rem">{{ $r->prenom }} {{ $r->nom }}</span>
                @endforeach
                @if($tache->responsables->count() > 2)
                <span style="font-size:.72rem;color:var(--slate-400)">+{{ $tache->responsables->count() - 2 }}</span>
                @endif
            </td>
            <td>{{ $tache->site?->nom ?? '—' }}</td>
            <td>
                @if($tache->date_echeance)
                <span style="color:{{ $tache->estEnRetard() ? '#B0202E' : 'var(--slate-600)' }};font-weight:{{ $tache->estEnRetard() ? '700' : '400' }}">
                    {{ $tache->date_echeance->format('d/m/Y') }}
                </span>
                @else —
                @endif
            </td>
            <td>
                <div style="display:flex;align-items:center;gap:.5rem">
                    <div class="progress-bar-wrap">
                        <div class="progress-bar-fill" style="width:{{ $tache->progression }}%"></div>
                    </div>
                    <span style="font-size:.78rem;color:var(--slate-500)">{{ $tache->progression }}%</span>
                </div>
            </td>
            <td>
                <div style="display:flex;gap:.3rem">
                    <a href="{{ route('taches.show', $tache) }}" class="btn btn-ghost btn-sm">Voir</a>
                    <a href="{{ route('taches.edit', $tache) }}" class="btn btn-ghost btn-sm">Éditer</a>
                    @if(auth()->user()->isManager())
                    <form method="POST" action="{{ route('taches.destroy', $tache) }}" onsubmit="return confirm('Supprimer cette tâche ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">✕</button>
                    </form>
                    @endif
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>

@if($taches->hasPages())
<div style="margin-top:1rem">{{ $taches->links('pagination::bootstrap-4') }}</div>
@endif
@endsection

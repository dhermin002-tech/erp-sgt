@extends('layouts.app')
@section('title', 'Nouvelle tâche')

@push('styles')
<style>
.form-card { background:var(--white);border-radius:12px;border:1px solid var(--slate-200);padding:1.75rem;max-width:820px; }
.form-row { display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem; }
.form-group { margin-bottom:1rem; }
.form-label { display:block;font-size:.85rem;font-weight:600;color:var(--slate-700);margin-bottom:.35rem; }
.form-label .req { color:var(--kt-maroon); }
.form-control { width:100%;padding:.55rem .875rem;border:1.5px solid var(--slate-200);border-radius:8px;font-family:var(--font-ui);font-size:.875rem;color:var(--slate-800);background:var(--slate-50);outline:none;box-sizing:border-box;transition:border-color .2s; }
.form-control:focus { border-color:var(--kt-navy);background:#fff; }
.form-control.is-invalid { border-color:var(--kt-maroon); }
.invalid-feedback { color:var(--kt-maroon);font-size:.78rem;margin-top:.2rem; }
.btn { display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.1rem;border-radius:7px;font-size:.875rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;transition:all .15s; }
.btn-primary { background:var(--kt-navy);color:#fff; }
.btn-ghost { background:none;color:var(--slate-600);border:1px solid var(--slate-200); }
.section-title { font-family:var(--font-display);font-size:.95rem;font-weight:700;color:var(--kt-navy);margin-bottom:.75rem;padding-bottom:.5rem;border-bottom:1px solid var(--slate-100); }
/* Multi-select responsables */
.responsables-grid { display:flex;flex-wrap:wrap;gap:.5rem; }
.responsable-chip { display:flex;align-items:center;gap:.35rem;background:var(--slate-100);border:1.5px solid var(--slate-200);border-radius:7px;padding:.35rem .65rem;cursor:pointer;font-size:.8rem;transition:all .15s; }
.responsable-chip:has(input:checked) { background:var(--kt-navy);border-color:var(--kt-navy);color:#fff; }
.responsable-chip input { display:none; }
</style>
@endpush

@section('content')
<div style="margin-bottom:1rem">
    <a href="{{ route('taches.index') }}" style="color:var(--slate-500);font-size:.875rem;text-decoration:none">← Retour aux tâches</a>
    <h1 style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--kt-navy);margin-top:.5rem">Nouvelle tâche</h1>
</div>

<div class="form-card">
    <form method="POST" action="{{ route('taches.store') }}">
        @csrf

        {{-- Titre --}}
        <div class="form-group">
            <label class="form-label" for="titre">Titre <span class="req">*</span></label>
            <input type="text" id="titre" name="titre" class="form-control @error('titre') is-invalid @enderror"
                   value="{{ old('titre') }}" placeholder="Décrivez la tâche en une ligne...">
            @error('titre') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Description --}}
        <div class="form-group">
            <label class="form-label" for="description">Description</label>
            <textarea id="description" name="description" rows="3"
                      class="form-control @error('description') is-invalid @enderror"
                      placeholder="Détails, contexte, exigences...">{{ old('description') }}</textarea>
        </div>

        <div class="form-row">
            {{-- Priorité --}}
            <div class="form-group">
                <label class="form-label" for="priorite">Priorité <span class="req">*</span></label>
                <select id="priorite" name="priorite" class="form-control">
                    @foreach(['basse'=>'Basse','normale'=>'Normale','haute'=>'Haute','urgente'=>'Urgente'] as $val => $lib)
                    <option value="{{ $val }}" {{ old('priorite','normale') === $val ? 'selected' : '' }}>{{ $lib }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Site --}}
            <div class="form-group">
                <label class="form-label" for="site_id">Site d'intervention</label>
                <select id="site_id" name="site_id" class="form-control">
                    <option value="">— Aucun site —</option>
                    @foreach($sites as $site)
                    <option value="{{ $site->id }}" {{ old('site_id') == $site->id ? 'selected' : '' }}>{{ $site->nom }} — {{ $site->ville }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row">
            {{-- Date début --}}
            <div class="form-group">
                <label class="form-label" for="date_debut">Date de début</label>
                <input type="date" id="date_debut" name="date_debut"
                       class="form-control @error('date_debut') is-invalid @enderror"
                       value="{{ old('date_debut') }}">
            </div>

            {{-- Date échéance --}}
            <div class="form-group">
                <label class="form-label" for="date_echeance">Date d'échéance</label>
                <input type="date" id="date_echeance" name="date_echeance"
                       class="form-control @error('date_echeance') is-invalid @enderror"
                       value="{{ old('date_echeance') }}">
                @error('date_echeance') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-row">
            {{-- Statut --}}
            <div class="form-group">
                <label class="form-label" for="statut">Statut initial <span class="req">*</span></label>
                <select id="statut" name="statut" class="form-control">
                    @foreach(['nouveau'=>'Nouveau','en_cours'=>'En cours','en_attente'=>'En attente'] as $val => $lib)
                    <option value="{{ $val }}" {{ old('statut','nouveau') === $val ? 'selected' : '' }}>{{ $lib }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Progression --}}
            <div class="form-group">
                <label class="form-label" for="progression">Progression initiale (%)</label>
                <input type="number" id="progression" name="progression" min="0" max="100"
                       class="form-control" value="{{ old('progression', 0) }}">
            </div>
        </div>

        {{-- Responsables --}}
        <div class="form-group">
            <label class="form-label">Responsable(s) <span class="req">*</span></label>
            @error('responsables') <div class="invalid-feedback" style="display:block;margin-bottom:.5rem">{{ $message }}</div> @enderror
            <div class="responsables-grid">
                @foreach($membres as $m)
                <label class="responsable-chip">
                    <input type="checkbox" name="responsables[]" value="{{ $m->id }}"
                           {{ in_array($m->id, old('responsables', [])) ? 'checked' : '' }}>
                    {{ $m->prenom }} {{ $m->nom }}
                    <span style="font-size:.68rem;opacity:.7">({{ $m->role }})</span>
                </label>
                @endforeach
            </div>
        </div>

        <div style="display:flex;gap:.75rem;margin-top:1.5rem">
            <button type="submit" class="btn btn-primary">Créer la tâche</button>
            <a href="{{ route('taches.index') }}" class="btn btn-ghost">Annuler</a>
        </div>
    </form>
</div>
@endsection

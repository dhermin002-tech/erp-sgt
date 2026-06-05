@extends('layouts.app')
@section('title', $tache->titre)
@php use Illuminate\Support\Facades\Storage; @endphp

@push('styles')
<style>
.tache-header { background:var(--white);border-radius:12px;border:1px solid var(--slate-200);padding:1.5rem;margin-bottom:1rem; }
.tache-body { display:grid;grid-template-columns:1fr 320px;gap:1rem; }
.main-col, .side-col { display:flex;flex-direction:column;gap:1rem; }
.card { background:var(--white);border-radius:12px;border:1px solid var(--slate-200);overflow:hidden; }
.card.overflow-visible { overflow:visible; }
.card-header { padding:.85rem 1.25rem;border-bottom:1px solid var(--slate-100);display:flex;align-items:center;justify-content:space-between; }
.card-title { font-family:var(--font-display);font-size:.9rem;font-weight:700;color:var(--kt-navy); }
.card-body { padding:1.25rem; }
.btn { display:inline-flex;align-items:center;gap:.4rem;padding:.45rem .9rem;border-radius:7px;font-size:.82rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;transition:all .15s; }
.btn-primary { background:var(--kt-navy);color:#fff; }
.btn-ghost { background:none;color:var(--slate-600);border:1px solid var(--slate-200); }
.btn-sm { padding:.3rem .6rem;font-size:.78rem; }
.btn-danger { background:#FEE2E2;color:#991B1B;border:none; }
/* Statut dropdown */
.statut-btn { display:flex;align-items:center;gap:.5rem;background:none;border:1.5px solid var(--slate-200);border-radius:8px;padding:.4rem .75rem;cursor:pointer;font-family:var(--font-ui);font-size:.875rem; }
.statut-dropdown { position:relative; }
.statut-menu { display:none;position:absolute;top:calc(100% + 4px);left:0;background:var(--white);border:1px solid var(--slate-200);border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,.1);z-index:20;min-width:160px;overflow:hidden; }
.statut-menu.open { display:block; }
.statut-menu button { display:flex;align-items:center;gap:.6rem;width:100%;padding:.55rem .9rem;background:none;border:none;cursor:pointer;font-size:.85rem;text-align:left;transition:background .1s; }
.statut-menu button:hover { background:var(--slate-50); }
/* Sous-tâches */
.sous-tache-item { display:flex;align-items:center;gap:.6rem;padding:.5rem 0;border-bottom:1px solid var(--slate-100); }
.sous-tache-item:last-child { border-bottom:none; }
.sous-tache-cb { width:16px;height:16px;accent-color:var(--kt-navy);cursor:pointer; }
.sous-tache-titre { flex:1;font-size:.875rem; }
.sous-tache-titre.termine { text-decoration:line-through;color:var(--slate-400); }
.sous-tache-del { background:none;border:none;cursor:pointer;color:var(--slate-300);font-size:.9rem;padding:.2rem; }
.sous-tache-del:hover { color:var(--kt-maroon); }
.add-input { display:flex;gap:.5rem;margin-top:.75rem; }
.add-input input { flex:1;padding:.4rem .75rem;border:1.5px solid var(--slate-200);border-radius:7px;font-size:.85rem;outline:none; }
.add-input input:focus { border-color:var(--kt-navy); }
/* Progress bar */
.prog-wrap { background:var(--slate-100);border-radius:999px;height:8px;margin:.5rem 0; }
.prog-fill { height:8px;border-radius:999px;background:var(--kt-navy);transition:width .4s; }
/* Méta */
.meta-row { display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--slate-50);font-size:.85rem; }
.meta-row:last-child { border-bottom:none; }
.meta-label { color:var(--slate-500);font-weight:600; }
.meta-value { color:var(--slate-700); }
@media (max-width:768px) { .tache-body { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
{{-- Fil d'ariane --}}
<div style="margin-bottom:.75rem">
    <a href="{{ route('taches.index') }}" style="color:var(--slate-500);font-size:.875rem;text-decoration:none">← Toutes les tâches</a>
</div>

{{-- En-tête tâche --}}
<div class="tache-header">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap">
        <div style="flex:1">
            <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:.5rem">
                @include('partials.badge_statut', ['statut' => $tache->statut])
                @if($tache->estEnRetard())
                <span style="background:#B0202E;color:#fff;font-size:.7rem;font-weight:700;padding:.2rem .6rem;border-radius:999px">⚠ En retard</span>
                @endif
                <span style="font-size:.8rem;color:var(--slate-400)">Priorité : <strong style="color:var(--slate-600)">{{ ucfirst($tache->priorite) }}</strong></span>
            </div>
            <h1 style="font-family:var(--font-display);font-size:1.4rem;font-weight:800;color:var(--kt-navy);margin-bottom:.4rem">{{ $tache->titre }}</h1>
            @if($tache->description)
            <p style="color:var(--slate-600);font-size:.9rem;line-height:1.6">{{ $tache->description }}</p>
            @endif
        </div>
        <div style="display:flex;gap:.5rem;flex-shrink:0">
            <a href="{{ route('taches.edit', $tache) }}" class="btn btn-ghost">Modifier</a>
            @if(auth()->user()->isManager())
            <form method="POST" action="{{ route('taches.destroy', $tache) }}" onsubmit="return confirm('Supprimer cette tâche ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
            </form>
            @endif
        </div>
    </div>
</div>

<div class="tache-body">

    {{-- Colonne principale --}}
    <div class="main-col">

        {{-- Sous-tâches --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Sous-tâches</span>
                <span style="font-size:.8rem;color:var(--slate-500)">{{ $tache->sousTaches->where('termine',true)->count() }} / {{ $tache->sousTaches->count() }}</span>
            </div>
            <div class="card-body">
                @if($tache->progression > 0 || $tache->sousTaches->count() > 0)
                <div style="margin-bottom:.75rem">
                    <div style="display:flex;justify-content:space-between;font-size:.8rem;color:var(--slate-500);margin-bottom:.25rem">
                        <span>Progression</span><span>{{ $tache->progression }}%</span>
                    </div>
                    <div class="prog-wrap"><div class="prog-fill" id="progFill" style="width:{{ $tache->progression }}%"></div></div>
                </div>
                @endif

                <div id="sousTachesList">
                @forelse($tache->sousTaches as $st)
                <div class="sous-tache-item" id="st-{{ $st->id }}">
                    <input type="checkbox" class="sous-tache-cb" data-id="{{ $st->id }}" {{ $st->termine ? 'checked' : '' }}
                           onchange="toggleSousTache({{ $st->id }}, this.checked)">
                    <span class="sous-tache-titre {{ $st->termine ? 'termine' : '' }}" id="st-titre-{{ $st->id }}">{{ $st->titre }}</span>
                    <button class="sous-tache-del" onclick="supprimerSousTache({{ $st->id }})" title="Supprimer">✕</button>
                </div>
                @empty
                <p style="color:var(--slate-400);font-size:.875rem;text-align:center;padding:.5rem">Aucune sous-tâche — ajoutez-en ci-dessous</p>
                @endforelse
                </div>

                {{-- Ajout inline --}}
                <div class="add-input">
                    <input type="text" id="newSousTache" placeholder="Ajouter une sous-tâche..." maxlength="255">
                    <button class="btn btn-primary btn-sm" onclick="ajouterSousTache()">Ajouter</button>
                </div>
            </div>
        </div>

        {{-- Commentaires --}}
        <div class="card" id="commentaires">
            <div class="card-header">
                <span class="card-title">Commentaires</span>
                <span style="font-size:.8rem;color:var(--slate-500)">{{ $tache->commentaires->count() }} commentaire(s)</span>
            </div>
            <div class="card-body" style="padding:0">

                {{-- Fil de commentaires --}}
                <div style="max-height:400px;overflow-y:auto;padding:1rem 1.25rem;display:flex;flex-direction:column;gap:.75rem;min-height:0">
                    @forelse($tache->commentaires as $com)
                    <div style="display:flex;gap:.75rem;align-items:flex-start" id="com-{{ $com->id }}">
                        <div style="width:32px;height:32px;border-radius:50%;background:var(--kt-navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;flex-shrink:0">
                            {{ strtoupper(substr($com->user->prenom??'',0,1)) }}{{ strtoupper(substr($com->user->nom??'',0,1)) }}
                        </div>
                        <div style="flex:1;min-width:0">
                            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.25rem;flex-wrap:wrap">
                                <span style="font-size:.82rem;font-weight:700;color:var(--slate-700)">{{ $com->user->nom_complet }}</span>
                                <span style="font-size:.72rem;color:var(--slate-400)">{{ $com->created_at->diffForHumans() }}</span>
                                @if(auth()->user()->isManager() || $com->user_id === auth()->id())
                                <form method="POST" action="{{ route('commentaires.destroy', $com) }}" style="margin-left:auto">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--slate-300);font-size:.8rem;padding:.1rem .3rem" title="Supprimer">✕</button>
                                </form>
                                @endif
                            </div>
                            <div style="background:var(--slate-50);border-radius:8px;padding:.6rem .85rem;font-size:.875rem;color:var(--slate-700);line-height:1.5;word-break:break-word">
                                {{ $com->contenu }}
                            </div>
                            @if($com->photo_path)
                            <div style="margin-top:.5rem">
                                <a href="{{ Storage::url($com->photo_path) }}" target="_blank" title="Voir la photo en grand">
                                    <img src="{{ Storage::url($com->photo_path) }}" alt="Photo terrain"
                                         style="max-width:200px;max-height:150px;border-radius:8px;object-fit:cover;border:1px solid var(--slate-200);cursor:pointer">
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p style="color:var(--slate-400);font-size:.875rem;text-align:center;padding:.5rem">Aucun commentaire — soyez le premier !</p>
                    @endforelse
                </div>

                {{-- Formulaire ajout commentaire --}}
                <div style="border-top:1px solid var(--slate-100);padding:1rem 1.25rem">
                    <form method="POST" action="{{ route('commentaires.store', $tache) }}" enctype="multipart/form-data">
                        @csrf
                        <textarea name="contenu" rows="2" placeholder="Ajouter un commentaire..."
                            style="width:100%;padding:.55rem .875rem;border:1.5px solid var(--slate-200);border-radius:8px;font-family:var(--font-ui);font-size:.875rem;resize:vertical;box-sizing:border-box;outline:none;transition:border-color .2s"
                            onfocus="this.style.borderColor='var(--kt-navy)'" onblur="this.style.borderColor='var(--slate-200)'">{{ old('contenu') }}</textarea>
                        @error('contenu') <div style="color:var(--kt-maroon);font-size:.78rem;margin:.2rem 0">{{ $message }}</div> @enderror
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:.5rem;flex-wrap:wrap;gap:.5rem">
                            <label style="display:flex;align-items:center;gap:.35rem;cursor:pointer;font-size:.82rem;color:var(--slate-500)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="this.previousElementSibling.previousElementSibling.textContent = this.files[0]?.name || '📷 Photo terrain'">
                                Photo terrain (max 5 Mo)
                            </label>
                            @error('photo') <div style="color:var(--kt-maroon);font-size:.78rem">{{ $message }}</div> @enderror
                            <button type="submit" class="btn btn-primary btn-sm">Commenter</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

        {{-- Rapports d'intervention --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Rapports d'intervention</span>
                <span style="font-size:.8rem;color:var(--slate-500)">{{ $tache->rapports->count() }} rapport(s)</span>
            </div>
            <div class="card-body" style="padding:0">

                {{-- Liste des rapports --}}
                @if($tache->rapports->count() > 0)
                <div style="max-height:300px;overflow-y:auto;padding:1rem 1.25rem;display:flex;flex-direction:column;gap:.75rem;min-height:0">
                    @foreach($tache->rapports as $rapport)
                    <div style="border:1px solid var(--slate-200);border-radius:8px;padding:.85rem 1rem">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.4rem;flex-wrap:wrap;gap:.5rem">
                            <div style="display:flex;align-items:center;gap:.5rem">
                                <span style="font-size:.8rem;font-weight:700;color:var(--slate-700)">{{ $rapport->user->nom_complet }}</span>
                                @if($rapport->date_intervention)
                                <span style="font-size:.75rem;color:var(--slate-400)">— {{ $rapport->date_intervention->format('d/m/Y') }}</span>
                                @endif
                                <span style="font-size:.72rem;color:var(--slate-400)">· {{ $rapport->created_at->diffForHumans() }}</span>
                            </div>
                            @if(auth()->user()->isManager() || $rapport->user_id === auth()->id())
                            <form method="POST" action="{{ route('rapports.destroy', $rapport) }}">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--slate-300);font-size:.8rem">✕</button>
                            </form>
                            @endif
                        </div>
                        <div style="font-size:.875rem;color:var(--slate-700);line-height:1.5;white-space:pre-wrap">{{ $rapport->contenu }}</div>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Formulaire ajout rapport --}}
                <div style="border-top:1px solid var(--slate-100);padding:1rem 1.25rem">
                    <form method="POST" action="{{ route('rapports.store', $tache) }}">
                        @csrf
                        <div style="margin-bottom:.5rem">
                            <textarea name="contenu" rows="3" placeholder="Saisir un compte-rendu d'intervention..."
                                style="width:100%;padding:.55rem .875rem;border:1.5px solid var(--slate-200);border-radius:8px;font-family:var(--font-ui);font-size:.875rem;resize:vertical;box-sizing:border-box;outline:none"
                                onfocus="this.style.borderColor='var(--kt-navy)'" onblur="this.style.borderColor='var(--slate-200)'">{{ old('contenu') }}</textarea>
                            @error('contenu') <div style="color:var(--kt-maroon);font-size:.78rem;margin-top:.2rem">{{ $message }}</div> @enderror
                        </div>
                        <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap">
                            <div>
                                <label style="font-size:.78rem;color:var(--slate-500);margin-right:.35rem">Date intervention :</label>
                                <input type="date" name="date_intervention" value="{{ old('date_intervention', now()->format('Y-m-d')) }}"
                                       style="padding:.35rem .65rem;border:1.5px solid var(--slate-200);border-radius:6px;font-size:.82rem;outline:none">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Ajouter le rapport</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

        {{-- Actions de suivi --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Actions à entreprendre</span>
                @php $totalActions = $tache->actionsSuivi->count(); $faites = $tache->actionsSuivi->where('fait', true)->count(); @endphp
                <span style="font-size:.8rem;color:var(--slate-500)">{{ $faites }}/{{ $totalActions }} faites</span>
            </div>
            <div class="card-body">

                <div id="actionsList">
                @forelse($tache->actionsSuivi as $action)
                <div class="sous-tache-item" id="action-{{ $action->id }}">
                    <input type="checkbox" class="sous-tache-cb" {{ $action->fait ? 'checked' : '' }}
                           onchange="toggleAction({{ $action->id }}, this.checked)">
                    <span class="sous-tache-titre {{ $action->fait ? 'termine' : '' }}" id="action-titre-{{ $action->id }}">{{ $action->description }}</span>
                    <span style="font-size:.72rem;color:var(--slate-400);margin-left:.35rem">{{ $action->user->nom }}</span>
                    <button class="sous-tache-del" onclick="supprimerAction({{ $action->id }})">✕</button>
                </div>
                @empty
                <p style="color:var(--slate-400);font-size:.875rem;text-align:center;padding:.5rem" id="actionsEmpty">Aucune action — ajoutez-en ci-dessous</p>
                @endforelse
                </div>

                <div class="add-input">
                    <input type="text" id="newAction" placeholder="Ajouter une action à entreprendre..." maxlength="500">
                    <button class="btn btn-primary btn-sm" onclick="ajouterAction()">Ajouter</button>
                </div>
            </div>
        </div>

    </div>{{-- /main-col --}}

    {{-- Colonne latérale --}}
    <div class="side-col">

        {{-- Changement de statut --}}
        <div class="card overflow-visible">
            <div class="card-header"><span class="card-title">Statut</span></div>
            <div class="card-body">
                <div class="statut-dropdown" id="statutDD">
                    <button class="statut-btn" onclick="document.getElementById('statutMenu').classList.toggle('open')">
                        @include('partials.badge_statut', ['statut' => $tache->statut])
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="statut-menu" id="statutMenu">
                        @foreach([
                            'nouveau'    => ['label'=>'Nouveau',    'color'=>'#64748B'],
                            'en_cours'   => ['label'=>'En cours',   'color'=>'#2563EB'],
                            'en_attente' => ['label'=>'En attente', 'color'=>'#C97A0A'],
                            'en_arret'   => ['label'=>'En arrêt',   'color'=>'#B0202E'],
                            'termine'    => ['label'=>'Terminé',    'color'=>'#15885A'],
                        ] as $val => $info)
                        <button onclick="changerStatut('{{ $val }}')" {{ $tache->statut === $val ? 'disabled style=opacity:.4' : '' }}>
                            <span style="width:8px;height:8px;border-radius:50%;background:{{ $info['color'] }};flex-shrink:0"></span>
                            {{ $info['label'] }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Méta-données --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Informations</span></div>
            <div class="card-body">
                <div class="meta-row">
                    <span class="meta-label">Créateur</span>
                    <span class="meta-value">{{ $tache->createur->nom_complet }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Site</span>
                    <span class="meta-value">{{ $tache->site?->nom ?? '—' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Début</span>
                    <span class="meta-value">{{ $tache->date_debut?->format('d/m/Y') ?? '—' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Échéance</span>
                    <span class="meta-value" style="color:{{ $tache->estEnRetard() ? '#B0202E' : 'inherit' }};font-weight:{{ $tache->estEnRetard() ? '700' : '400' }}">
                        {{ $tache->date_echeance?->format('d/m/Y') ?? '—' }}
                    </span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Créée le</span>
                    <span class="meta-value">{{ $tache->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        {{-- Responsables --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Responsables</span></div>
            <div class="card-body">
                @foreach($tache->responsables as $r)
                <div style="display:flex;align-items:center;gap:.5rem;padding:.3rem 0;border-bottom:1px solid var(--slate-50)">
                    <div style="width:30px;height:30px;border-radius:50%;background:var(--kt-navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;flex-shrink:0">
                        {{ strtoupper(substr($r->prenom,0,1)) }}{{ strtoupper(substr($r->nom,0,1)) }}
                    </div>
                    <div>
                        <div style="font-size:.85rem;font-weight:600;color:var(--slate-700)">{{ $r->nom_complet }}</div>
                        <div style="font-size:.72rem;color:var(--slate-400)">{{ ucfirst($r->role) }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>{{-- /side-col --}}

</div>

@push('scripts')
<script>
const tacheId = {{ $tache->id }};
const csrfToken = document.querySelector('meta[name=csrf-token]').content;

// ── Changement de statut (AJAX) ───────────────────────────────────────────
function changerStatut(statut) {
    document.getElementById('statutMenu').classList.remove('open');
    fetch(`/taches/${tacheId}/statut`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ statut })
    }).then(r => r.json()).then(d => {
        if (d.ok) location.reload();
    });
}

// ── Sous-tâches (AJAX) ────────────────────────────────────────────────────
function toggleSousTache(id, termine) {
    fetch(`/sous-taches/${id}/toggle`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ termine })
    }).then(r => r.json()).then(d => {
        const titre = document.getElementById(`st-titre-${id}`);
        if (titre) titre.className = `sous-tache-titre ${termine ? 'termine' : ''}`;
        if (d.progression !== undefined) {
            document.getElementById('progFill').style.width = d.progression + '%';
        }
    });
}

function ajouterSousTache() {
    const input = document.getElementById('newSousTache');
    const titre = input.value.trim();
    if (! titre) return;
    fetch(`/taches/${tacheId}/sous-taches`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ titre })
    }).then(r => r.json()).then(d => {
        if (d.id) {
            const list = document.getElementById('sousTachesList');
            const empty = list.querySelector('p');
            if (empty) empty.remove();
            list.insertAdjacentHTML('beforeend', `
                <div class="sous-tache-item" id="st-${d.id}">
                    <input type="checkbox" class="sous-tache-cb" data-id="${d.id}"
                           onchange="toggleSousTache(${d.id}, this.checked)">
                    <span class="sous-tache-titre" id="st-titre-${d.id}">${d.titre}</span>
                    <button class="sous-tache-del" onclick="supprimerSousTache(${d.id})">✕</button>
                </div>
            `);
            input.value = '';
        }
    });
}

function supprimerSousTache(id) {
    if (!confirm('Supprimer cette sous-tâche ?')) return;
    fetch(`/sous-taches/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken }
    }).then(r => r.json()).then(d => {
        if (d.ok) {
            document.getElementById(`st-${id}`)?.remove();
            if (d.progression !== undefined) {
                document.getElementById('progFill').style.width = d.progression + '%';
            }
        }
    });
}

// Fermer dropdown statut au clic extérieur
document.addEventListener('click', e => {
    if (!e.target.closest('#statutDD')) {
        document.getElementById('statutMenu')?.classList.remove('open');
    }
});

// Enter sur sous-tâche
document.getElementById('newSousTache').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); ajouterSousTache(); }
});

// ── Actions de suivi (AJAX) ───────────────────────────────────────────────
function toggleAction(id, fait) {
    fetch(`/actions/${id}/toggle`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ fait })
    }).then(r => r.json()).then(d => {
        const titre = document.getElementById(`action-titre-${id}`);
        if (titre) titre.className = `sous-tache-titre ${fait ? 'termine' : ''}`;
    });
}

function ajouterAction() {
    const input = document.getElementById('newAction');
    const description = input.value.trim();
    if (! description) return;
    fetch(`/taches/${tacheId}/actions`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ description })
    }).then(r => r.json()).then(d => {
        if (d.id) {
            const empty = document.getElementById('actionsEmpty');
            if (empty) empty.remove();
            document.getElementById('actionsList').insertAdjacentHTML('beforeend', `
                <div class="sous-tache-item" id="action-${d.id}">
                    <input type="checkbox" class="sous-tache-cb" onchange="toggleAction(${d.id}, this.checked)">
                    <span class="sous-tache-titre" id="action-titre-${d.id}">${d.description}</span>
                    <button class="sous-tache-del" onclick="supprimerAction(${d.id})">✕</button>
                </div>
            `);
            input.value = '';
        }
    });
}

function supprimerAction(id) {
    if (!confirm('Supprimer cette action ?')) return;
    fetch(`/actions/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken }
    }).then(r => r.json()).then(d => {
        if (d.ok) document.getElementById(`action-${id}`)?.remove();
    });
}

document.getElementById('newAction').addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); ajouterAction(); }
});
</script>
@endpush
@endsection

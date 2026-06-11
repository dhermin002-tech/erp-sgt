@extends('layouts.app')
@section('title', 'Mon profil')

@push('styles')
<style>
/* ── Hero profil ── */
.profil-hero {
    background: linear-gradient(135deg, #001f3f 0%, #003366 60%, #002244 100%);
    border-radius: 16px; padding: 1.75rem 2rem; margin-bottom: 1.5rem;
    display: flex; align-items: center; gap: 1.25rem;
    position: relative; overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,.18);
}
.profil-hero::before {
    content: ''; position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.04) 1px, transparent 1px);
    background-size: 22px 22px; pointer-events: none;
}
.profil-avatar-hero {
    width: 72px; height: 72px; border-radius: 50%;
    background: linear-gradient(135deg, #CC5500, #FF8C42);
    display: flex; align-items: center; justify-content: center;
    font-family: 'Space Grotesk', sans-serif; font-size: 1.6rem; font-weight: 800;
    color: #fff; flex-shrink: 0; border: 3px solid rgba(255,255,255,.2);
    box-shadow: 0 4px 16px rgba(204,85,0,.4); position: relative; z-index: 1;
}
.profil-hero-info { position: relative; z-index: 1; }
.profil-hero-name {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.4rem; font-weight: 700; color: #fff; letter-spacing: -.02em;
}
.profil-hero-meta { font-size: .84rem; color: rgba(255,255,255,.55); margin-top: .3rem; }
.profil-hero-role {
    display: inline-flex; align-items: center; gap: .3rem;
    background: rgba(204,85,0,.25); color: #FFA96A;
    font-size: .75rem; font-weight: 700; padding: .2rem .65rem;
    border-radius: 20px; margin-top: .5rem;
    border: 1px solid rgba(204,85,0,.3);
}

/* ── Onglets ── */
.profil-tabs {
    display: flex; gap: 0; border-bottom: 2px solid #e2e8f0;
    margin-bottom: 1.5rem; overflow-x: auto;
}
.profil-tab {
    display: inline-flex; align-items: center; gap: .45rem;
    padding: .75rem 1.25rem; font-size: .875rem; font-weight: 600;
    color: #64748b; text-decoration: none; border-bottom: 3px solid transparent;
    margin-bottom: -2px; white-space: nowrap; transition: all .15s; cursor: pointer;
    background: none; border-top: none; border-left: none; border-right: none;
}
.profil-tab:hover { color: #003366; }
.profil-tab.active { color: #003366; border-bottom-color: #CC5500; }

/* ── Section onglet ── */
.tab-section { display: none; }
.tab-section.active { display: block; }

/* ── Card formulaire ── */
.form-card {
    background: #fff; border-radius: 16px; border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0,0,0,.05); overflow: hidden;
    max-width: 640px;
}
.form-card-header {
    padding: 1.1rem 1.5rem; border-bottom: 1px solid #f1f5f9;
    display: flex; align-items: center; gap: .6rem;
}
.form-card-header-icon {
    width: 34px; height: 34px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center; font-size: 1rem;
}
.form-card-header-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: .95rem; font-weight: 700; color: #1e293b;
}
.form-card-body { padding: 1.5rem; }

/* ── Champs ── */
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
.form-row.single { grid-template-columns: 1fr; }
@media (max-width: 540px) { .form-row { grid-template-columns: 1fr; } }

.form-group { display: flex; flex-direction: column; gap: .35rem; }
.form-label {
    font-size: .78rem; font-weight: 700; color: #475569;
    text-transform: uppercase; letter-spacing: .05em;
}
.form-input {
    padding: .6rem .9rem; border: 1.5px solid #e2e8f0; border-radius: 9px;
    font-size: .9rem; color: #1e293b; outline: none;
    transition: border-color .15s, box-shadow .15s;
    font-family: inherit;
}
.form-input:focus { border-color: #003366; box-shadow: 0 0 0 3px rgba(0,51,102,.08); }
.form-input:disabled { background: #f8fafc; color: #94a3b8; cursor: not-allowed; }
.form-input.is-invalid { border-color: #f87171; }
.invalid-msg { font-size: .78rem; color: #dc2626; margin-top: .2rem; }

/* ── Bouton submit ── */
.btn-save {
    display: inline-flex; align-items: center; gap: .45rem;
    background: #003366; color: #fff;
    padding: .6rem 1.4rem; border-radius: 9px;
    font-size: .9rem; font-weight: 600; border: none; cursor: pointer;
    transition: all .15s; font-family: inherit;
}
.btn-save:hover { background: #004080; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(0,51,102,.25); }
.btn-save.danger { background: #991b1b; }
.btn-save.danger:hover { background: #7f1d1d; }

/* ── Alertes ── */
.alert-success {
    display: flex; align-items: center; gap: .6rem;
    background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px;
    padding: .75rem 1rem; color: #166534; font-size: .875rem; font-weight: 500;
    margin-bottom: 1.25rem;
}
.alert-success i { font-size: 1rem; flex-shrink: 0; }

/* ── Préférences toggle ── */
.pref-group { margin-bottom: 1.5rem; }
.pref-group-label {
    font-size: .78rem; font-weight: 700; color: #475569;
    text-transform: uppercase; letter-spacing: .05em; margin-bottom: .75rem;
    display: flex; align-items: center; gap: .4rem;
}
.pref-options { display: flex; gap: .65rem; flex-wrap: wrap; }
.pref-option {
    position: relative; cursor: pointer;
}
.pref-option input { position: absolute; opacity: 0; pointer-events: none; }
.pref-option-label {
    display: flex; align-items: center; gap: .5rem;
    padding: .6rem 1.1rem; border-radius: 10px;
    border: 2px solid #e2e8f0; font-size: .875rem; font-weight: 600;
    color: #475569; transition: all .15s; background: #fff;
}
.pref-option input:checked + .pref-option-label {
    border-color: #003366; background: #eff6ff; color: #003366;
}
.pref-option-label:hover { border-color: #94a3b8; }
</style>
@endpush

@section('content')

{{-- Hero ── --}}
<div class="profil-hero">
    <div class="profil-avatar-hero">
        {{ strtoupper(substr($user->prenom ?? '', 0, 1) . substr($user->nom ?? '', 0, 1)) }}
    </div>
    <div class="profil-hero-info">
        <div class="profil-hero-name">{{ $user->nom_complet }}</div>
        <div class="profil-hero-meta">
            <i class="bi bi-person me-1"></i>{{ $user->username }}
            &nbsp;·&nbsp;
            <i class="bi bi-clock me-1"></i>Membre depuis {{ $user->created_at->isoFormat('MMMM YYYY') }}
        </div>
        <div class="profil-hero-role">
            <i class="bi bi-shield-check"></i> {{ ucfirst($user->role) }}
        </div>
    </div>
</div>

{{-- Onglets ── --}}
<div class="profil-tabs" id="profilTabs">
    <button class="profil-tab active" data-tab="infos">
        <i class="bi bi-person-lines-fill"></i> Informations
    </button>
    <button class="profil-tab" data-tab="password">
        <i class="bi bi-lock"></i> Mot de passe
    </button>
    <button class="profil-tab" data-tab="preferences">
        <i class="bi bi-sliders"></i> Préférences
    </button>
</div>

{{-- ═══ Onglet 1 — Informations ═══ --}}
<div class="tab-section active" id="tab-infos">

    @if(session('success_infos'))
    <div class="alert-success"><i class="bi bi-check-circle-fill"></i> {{ session('success_infos') }}</div>
    @endif

    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-header-icon" style="background:#eff6ff;color:#003366"><i class="bi bi-person-fill"></i></div>
            <div class="form-card-header-title">Informations personnelles</div>
        </div>
        <div class="form-card-body">
            <form method="POST" action="{{ route('profil.updateInfos') }}">
                @csrf @method('PATCH')

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="prenom" class="form-input {{ $errors->has('prenom') ? 'is-invalid' : '' }}"
                               value="{{ old('prenom', $user->prenom) }}" placeholder="Votre prénom">
                        @error('prenom') <span class="invalid-msg">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nom <span style="color:#dc2626">*</span></label>
                        <input type="text" name="nom" class="form-input {{ $errors->has('nom') ? 'is-invalid' : '' }}"
                               value="{{ old('nom', $user->nom) }}" required placeholder="Votre nom">
                        @error('nom') <span class="invalid-msg">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-row single">
                    <div class="form-group">
                        <label class="form-label">Identifiant (username)</label>
                        <input type="text" class="form-input" value="{{ $user->username }}" disabled>
                        <span style="font-size:.75rem;color:#94a3b8;margin-top:.2rem">L'identifiant ne peut pas être modifié.</span>
                    </div>
                </div>

                <div class="form-row single">
                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-input {{ $errors->has('telephone') ? 'is-invalid' : '' }}"
                               value="{{ old('telephone', $user->telephone) }}" placeholder="ex: 062-74-08-60">
                        @error('telephone') <span class="invalid-msg">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-row single">
                    <div class="form-group">
                        <label class="form-label">Rôle</label>
                        <input type="text" class="form-input" value="{{ ucfirst($user->role) }}" disabled>
                        <span style="font-size:.75rem;color:#94a3b8;margin-top:.2rem">Le rôle est géré par le manager.</span>
                    </div>
                </div>

                <button type="submit" class="btn-save">
                    <i class="bi bi-check-lg"></i> Enregistrer
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ═══ Onglet 2 — Mot de passe ═══ --}}
<div class="tab-section" id="tab-password">

    @if(session('success_password'))
    <div class="alert-success"><i class="bi bi-check-circle-fill"></i> {{ session('success_password') }}</div>
    @endif

    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-header-icon" style="background:#fef3c7;color:#92400e"><i class="bi bi-lock-fill"></i></div>
            <div class="form-card-header-title">Changer le mot de passe</div>
        </div>
        <div class="form-card-body">
            <form method="POST" action="{{ route('profil.updatePassword') }}">
                @csrf @method('PATCH')

                <div class="form-row single" style="margin-bottom:1rem">
                    <div class="form-group">
                        <label class="form-label">Mot de passe actuel <span style="color:#dc2626">*</span></label>
                        <input type="password" name="password_actuel"
                               class="form-input {{ $errors->has('password_actuel') ? 'is-invalid' : '' }}"
                               placeholder="••••••••" autocomplete="current-password">
                        @error('password_actuel') <span class="invalid-msg">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-row single" style="margin-bottom:1rem">
                    <div class="form-group">
                        <label class="form-label">Nouveau mot de passe <span style="color:#dc2626">*</span></label>
                        <input type="password" name="password"
                               class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                               placeholder="Minimum 8 caractères" autocomplete="new-password">
                        @error('password') <span class="invalid-msg">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="form-row single">
                    <div class="form-group">
                        <label class="form-label">Confirmer le nouveau mot de passe <span style="color:#dc2626">*</span></label>
                        <input type="password" name="password_confirmation"
                               class="form-input" placeholder="Répéter le mot de passe" autocomplete="new-password">
                    </div>
                </div>

                <div style="background:#fef9c3;border:1px solid #fde68a;border-radius:9px;padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.82rem;color:#92400e;display:flex;gap:.5rem;align-items:flex-start">
                    <i class="bi bi-info-circle-fill" style="flex-shrink:0;margin-top:.1rem"></i>
                    <span>Après changement, vous resterez connecté. Le nouveau mot de passe sera actif dès maintenant.</span>
                </div>

                <button type="submit" class="btn-save">
                    <i class="bi bi-lock-fill"></i> Modifier le mot de passe
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ═══ Onglet 3 — Préférences ═══ --}}
<div class="tab-section" id="tab-preferences">

    @if(session('success_prefs'))
    <div class="alert-success"><i class="bi bi-check-circle-fill"></i> {{ session('success_prefs') }}</div>
    @endif

    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-header-icon" style="background:#f5f3ff;color:#7c3aed"><i class="bi bi-sliders"></i></div>
            <div class="form-card-header-title">Préférences d'affichage</div>
        </div>
        <div class="form-card-body">
            <form method="POST" action="{{ route('profil.updatePreferences') }}">
                @csrf @method('PATCH')

                {{-- Direction UI ── --}}
                <div class="pref-group">
                    <div class="pref-group-label"><i class="bi bi-layout-sidebar"></i> Disposition de l'interface</div>
                    <div class="pref-options">
                        <label class="pref-option">
                            <input type="radio" name="direction_ui" value="A" {{ $user->direction_ui === 'A' ? 'checked' : '' }}>
                            <span class="pref-option-label">
                                <i class="bi bi-layout-sidebar"></i> Direction A — Sidebar gauche
                            </span>
                        </label>
                        <label class="pref-option">
                            <input type="radio" name="direction_ui" value="B" {{ $user->direction_ui === 'B' ? 'checked' : '' }}>
                            <span class="pref-option-label">
                                <i class="bi bi-layout-three-columns"></i> Direction B — Navigation haute
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Langue ── --}}
                <div class="pref-group">
                    <div class="pref-group-label"><i class="bi bi-translate"></i> Langue de l'interface</div>
                    <div class="pref-options">
                        <label class="pref-option">
                            <input type="radio" name="locale" value="fr" {{ (session('locale', 'fr') === 'fr') ? 'checked' : '' }}>
                            <span class="pref-option-label">🇫🇷 Français</span>
                        </label>
                        <label class="pref-option">
                            <input type="radio" name="locale" value="en" {{ (session('locale', 'fr') === 'en') ? 'checked' : '' }}>
                            <span class="pref-option-label">🇬🇧 English</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-save">
                    <i class="bi bi-check-lg"></i> Enregistrer les préférences
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Gestion onglets
const tabs     = document.querySelectorAll('.profil-tab');
const sections = document.querySelectorAll('.tab-section');

function activerOnglet(tabId) {
    tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === tabId));
    sections.forEach(s => s.classList.toggle('active', s.id === 'tab-' + tabId));
    history.replaceState(null, '', '#tab-' + tabId);
}

tabs.forEach(tab => tab.addEventListener('click', () => activerOnglet(tab.dataset.tab)));

// Restaurer l'onglet actif depuis l'URL ou session
const hash = location.hash.replace('#tab-', '') || '{{ session('tab', 'infos') }}';
if (['infos','password','preferences'].includes(hash)) activerOnglet(hash);

// Ouvrir l'onglet avec erreur de validation
@if($errors->has('password_actuel') || $errors->has('password'))
activerOnglet('password');
@endif
</script>
@endpush

@endsection

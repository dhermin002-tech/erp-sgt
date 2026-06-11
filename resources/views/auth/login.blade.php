<!DOCTYPE html>
<html lang="fr" data-direction="A">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — SGT KayTechnologie</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            background: #060D1A;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* ── Fond cosmos : mesh + grille de points ── */
        .bg-mesh {
            position: fixed; inset: 0; z-index: 0;
            background:
                radial-gradient(ellipse 80% 60% at 15% 50%, rgba(0,51,102,.65) 0%, transparent 60%),
                radial-gradient(ellipse 60% 70% at 85% 20%, rgba(139,0,0,.28) 0%, transparent 55%),
                radial-gradient(ellipse 45% 45% at 65% 85%, rgba(204,85,0,.14) 0%, transparent 55%);
        }
        .bg-dots {
            position: fixed; inset: 0; z-index: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,.065) 1px, transparent 1px);
            background-size: 28px 28px;
        }
        /* Ligne verticale décorative animée */
        .bg-line {
            position: fixed; top: 0; left: 50%; z-index: 0;
            width: 1px; height: 100vh;
            background: linear-gradient(180deg,
                transparent 0%,
                rgba(204,85,0,.25) 35%,
                rgba(0,51,102,.35) 65%,
                transparent 100%);
            animation: lineFloat 8s ease-in-out infinite alternate;
        }
        @keyframes lineFloat {
            0%   { opacity: .4; transform: scaleY(.95); }
            100% { opacity: .8; transform: scaleY(1.02); }
        }

        /* ── Carte principale ── */
        .login-wrap {
            position: relative; z-index: 1;
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            max-width: 860px;
            width: calc(100% - 2rem);
            border-radius: 24px;
            overflow: hidden;
            box-shadow:
                0 40px 100px rgba(0,0,0,.65),
                0 0 0 1px rgba(255,255,255,.06);
            animation: cardReveal .55s cubic-bezier(0.16,1,0.3,1) forwards;
            opacity: 0;
        }
        @keyframes cardReveal {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Panneau gauche — Identité SGT ── */
        .panel-id {
            background: linear-gradient(155deg, #091524 0%, #001F3F 45%, #0D0508 100%);
            padding: 2.75rem 2.25rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-right: 1px solid rgba(255,255,255,.05);
        }
        /* Lueur orange en bas à droite */
        .panel-id::after {
            content: '';
            position: absolute;
            width: 280px; height: 280px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(204,85,0,.12) 0%, transparent 70%);
            bottom: -80px; right: -60px;
            pointer-events: none;
        }
        /* Texte déco géant en fond */
        .deco-bg-text {
            position: absolute;
            top: -1.5rem; right: 0.5rem;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 9rem; font-weight: 800;
            color: rgba(255,255,255,.025);
            letter-spacing: -0.06em;
            user-select: none; pointer-events: none;
            line-height: 1;
        }

        .id-top { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; }

        /* Logo badge — compact, discret */
        .logo-badge {
            background: #fff;
            border-radius: 8px;
            padding: .35rem .65rem;
            display: inline-flex; align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,.25);
            margin-bottom: 1.5rem;
        }
        .logo-badge img { width: 100px; height: auto; display: block; }

        .id-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.9rem; font-weight: 800;
            color: #fff;
            line-height: 1.18;
            letter-spacing: -0.04em;
            margin-bottom: .7rem;
            text-align: center;
        }
        .id-title em {
            font-style: normal;
            color: #fff;
            -webkit-text-fill-color: #fff;
        }
        /* Séparateur décoratif orange sous le titre */
        .id-title-sep {
            width: 48px; height: 3px;
            background: linear-gradient(90deg, #CC5500, #FF8C42);
            border-radius: 999px;
            margin: 0 auto .85rem;
        }
        .id-desc {
            font-size: .875rem;
            color: rgba(255,255,255,.62);
            line-height: 1.72;
            max-width: 250px;
            margin-bottom: .2rem;
            text-align: center;
        }

        .id-bottom { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; width: 100%; }

        /* Stats KPI mini */
        .id-stats { display: flex; gap: 1.4rem; margin-bottom: 1.4rem; justify-content: center; }
        .id-stat {
            border-left: 2px solid rgba(204,85,0,.4);
            padding-left: .65rem;
            text-align: left;
        }
        .id-stat-val {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.25rem; font-weight: 700; color: #fff; line-height: 1;
        }
        .id-stat-lbl {
            font-size: .68rem; font-weight: 600;
            color: rgba(255,255,255,.3);
            letter-spacing: .06em; text-transform: uppercase;
            margin-top: .2rem;
        }

        .badges { display: flex; gap: .45rem; flex-wrap: wrap; justify-content: center; }
        .badge-pill {
            min-width: 110px;
            padding: .3rem .7rem;
            border-radius: 999px;
            font-size: .67rem; font-weight: 700;
            letter-spacing: .06em; text-transform: uppercase;
            display: inline-flex; align-items: center; justify-content: center; gap: .35rem;
            text-align: center;
        }
        .bp-lan  { background: rgba(0,51,102,.5);  color: #7EB8FF; border: 1px solid rgba(0,80,180,.3); }
        .bp-soft { background: rgba(204,85,0,.3);  color: #FFAA6A; border: 1px solid rgba(204,85,0,.3); }
        .bp-hard { background: rgba(139,0,0,.3);   color: #FF9090; border: 1px solid rgba(139,0,0,.3); }

        /* ── Panneau droit — Formulaire ── */
        .panel-form {
            background: #F5F4F0;
            padding: 2.5rem 2.25rem;
            display: flex; align-items: center; justify-content: center;
        }
        .form-inner { width: 100%; max-width: 310px; }

        /* Tag "accès sécurisé" */
        .form-tag {
            display: inline-flex; align-items: center; gap: .35rem;
            background: rgba(0,51,102,.09);
            color: #003366;
            font-size: .7rem; font-weight: 700;
            letter-spacing: .08em; text-transform: uppercase;
            padding: .28rem .7rem;
            border-radius: 999px;
            border: 1px solid rgba(0,51,102,.14);
            margin-bottom: 1.1rem;
        }
        .form-tag::before {
            content: '';
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #CC5500;
            flex-shrink: 0;
        }

        .form-heading {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.1rem; font-weight: 700;
            color: #0D1B2A;
            letter-spacing: -0.045em;
            line-height: 1;
            margin-bottom: .35rem;
        }
        .form-sub {
            font-size: .84rem;
            color: #9AA3AF;
            margin-bottom: 1.75rem;
            line-height: 1.55;
        }

        /* Alert erreur */
        .alert-error {
            background: #FFF5F5;
            border: 1px solid #FEBCBC;
            border-left: 3px solid #8B0000;
            border-radius: 8px;
            padding: .65rem .9rem;
            font-size: .82rem;
            color: #742A2A;
            margin-bottom: 1.1rem;
        }

        /* Champs */
        .field { margin-bottom: .9rem; }
        .field label {
            display: block;
            font-size: .77rem; font-weight: 600;
            color: #4A5568; letter-spacing: .03em;
            margin-bottom: .3rem;
        }
        .field input {
            width: 100%;
            padding: .72rem 1rem;
            background: #fff;
            border: 1.5px solid #E2E0DA;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem; color: #1A202C;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .field input:focus {
            border-color: #003366;
            box-shadow: 0 0 0 3px rgba(0,51,102,.09);
        }
        .field input.is-invalid { border-color: #8B0000; }
        .invalid-msg { color: #8B0000; font-size: .75rem; margin-top: .25rem; }

        .remember-row {
            display: flex; align-items: center; gap: .45rem;
            margin-bottom: 1.4rem;
            font-size: .82rem; color: #718096;
        }
        .remember-row input[type=checkbox] { accent-color: #003366; width:15px; height:15px; }

        /* Bouton submit */
        .btn-submit {
            width: 100%;
            padding: .82rem;
            background: #003366;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'Space Grotesk', sans-serif;
            font-size: .95rem; font-weight: 600;
            letter-spacing: .01em;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: .5rem;
            position: relative; overflow: hidden;
            transition: all .2s;
        }
        .btn-submit::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,.1) 0%, transparent 50%);
        }
        .btn-submit:hover {
            background: #00244F;
            box-shadow: 0 8px 24px rgba(0,51,102,.4);
            transform: translateY(-1px);
        }
        .btn-submit:active { transform: scale(.98); }
        .btn-submit svg { flex-shrink: 0; }

        /* Pied de formulaire */
        .form-divider {
            display: flex; align-items: center; gap: .6rem;
            margin: 1.4rem 0 .9rem;
        }
        .form-divider::before, .form-divider::after {
            content: ''; flex: 1; height: 1px; background: #E5E2DC;
        }
        .form-divider span { font-size: .7rem; color: #C4C0B8; font-weight: 600; white-space: nowrap; }

        .form-footer {
            text-align: center;
            font-size: .7rem; color: #C4C0B8; line-height: 1.6;
        }
        .form-footer strong { color: #8B0000; }

        /* ── Responsive mobile ── */
        @media (max-width: 680px) {
            body { align-items: flex-start; overflow-y: auto; }
            .login-wrap {
                grid-template-columns: 1fr;
                width: 100%; min-height: 100vh;
                border-radius: 0;
            }
            .panel-id { padding: 1.5rem 1.5rem 1rem; }
            .id-title  { font-size: 1.3rem; }
            .id-stats  { display: none; }
            .panel-form { padding: 1.5rem 1.5rem 2rem; }
        }
    </style>
</head>
<body>
    <div class="bg-mesh"></div>
    <div class="bg-dots"></div>
    <div class="bg-line"></div>

    <div class="login-wrap">

        {{-- Panneau identité --}}
        <div class="panel-id">
            <div class="deco-bg-text">SGT</div>

            <div class="id-top">
                <div class="logo-badge">
                    <img src="{{ asset('images/logo-kt.jpg') }}" alt="KayTechnologie Gabon"
                         onerror="this.style.display='none';document.getElementById('logoFallback').style.display='flex'">
                    <span id="logoFallback" style="display:none;align-items:center;gap:.4rem">
                        <span style="background:#8B0000;color:#fff;border-radius:6px;padding:.25rem .5rem;font-family:'Space Grotesk',sans-serif;font-weight:800">KT</span>
                        <span style="font-family:'Space Grotesk',sans-serif;font-weight:700;color:#1a1a1a">Kay<span style="color:#CC5500">Tech</span></span>
                    </span>
                </div>

                <h1 class="id-title">
                    Système de<br>Gestion des Tâches
                </h1>
                <div class="id-title-sep"></div>
                <p class="id-desc">
                    Planifiez, suivez et reportez vos interventions terrain en temps réel depuis un seul outil.
                </p>
            </div>

            <div class="id-bottom">
                <div class="id-stats">
                    <div class="id-stat">
                        <div class="id-stat-val">5</div>
                        <div class="id-stat-lbl">Rôles</div>
                    </div>
                    <div class="id-stat">
                        <div class="id-stat-val">SSL</div>
                        <div class="id-stat-lbl">Sécurisé</div>
                    </div>
                    <div class="id-stat">
                        <div class="id-stat-val">v1.0</div>
                        <div class="id-stat-lbl">Version</div>
                    </div>
                </div>
                <div class="badges">
                    <span class="badge-pill bp-lan"><i class="bi bi-wifi"></i> LAN</span>
                    <span class="badge-pill bp-soft"><i class="bi bi-code-slash"></i> Software</span>
                    <span class="badge-pill bp-hard"><i class="bi bi-cpu"></i> Hardware</span>
                </div>
            </div>
        </div>

        {{-- Panneau formulaire --}}
        <div class="panel-form">
            <div class="form-inner">
                <div class="form-tag">Accès sécurisé</div>
                <h2 class="form-heading">Connexion</h2>
                <p class="form-sub">Entrez vos identifiants pour accéder à votre espace.</p>

                @if ($errors->any())
                <div class="alert-error">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="field">
                        <label for="username">Identifiant</label>
                        <input type="text" id="username" name="username"
                               class="{{ $errors->has('username') ? 'is-invalid' : '' }}"
                               value="{{ old('username') }}" autofocus autocomplete="username"
                               placeholder="Votre identifiant">
                        @error('username')<div class="invalid-msg">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password"
                               class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                               autocomplete="current-password"
                               placeholder="••••••••">
                        @error('password')<div class="invalid-msg">{{ $message }}</div>@enderror
                    </div>

                    <div class="remember-row">
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember" style="cursor:pointer">Se souvenir de moi</label>
                    </div>

                    <button type="submit" class="btn-submit">
                        Se connecter
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </button>
                </form>

                <div class="form-divider"><span>KayTechnologie Gabon</span></div>

                <div class="form-footer">
                    Système de Gestion des Tâches &mdash; <strong>SGT v1.0</strong>
                </div>
            </div>
        </div>

    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="fr" data-direction="A">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — SGT KayTechnologie</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --kt-rouge:  #8B0000;
            --kt-orange: #CC5500;
            --kt-bleu:   #003366;
            --kt-bleu2:  #1a4f99;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
        }

        /* ── Panneau gauche — identité KT ───────────── */
        .panel-left {
            flex: 1;
            background: linear-gradient(145deg, #001f3f 0%, #003366 40%, #8B0000 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 2.5rem;
            position: relative;
            overflow: hidden;
        }

        /* Cercles décoratifs */
        .panel-left::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.06);
            top: -100px; left: -100px;
        }
        .panel-left::after {
            content: '';
            position: absolute;
            width: 350px; height: 350px;
            border-radius: 50%;
            border: 1px solid rgba(204,85,0,0.15);
            bottom: -80px; right: -80px;
        }

        .left-content {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 380px;
        }

        /* Logo KT */
        .logo-wrapper {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 24px;
            padding: 2rem 2.5rem;
            margin-bottom: 2.5rem;
            display: inline-block;
        }
        .logo-wrapper img {
            width: 200px;
            height: auto;
            display: block;
            filter: drop-shadow(0 4px 16px rgba(0,0,0,0.3));
        }

        .left-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.9rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.02em;
            line-height: 1.2;
            margin-bottom: 1rem;
        }
        .left-subtitle {
            font-size: .95rem;
            color: rgba(255,255,255,0.65);
            line-height: 1.7;
            max-width: 300px;
            margin: 0 auto;
        }

        /* Badges LAN / SOFTWARE / HARDWARE */
        .badges {
            display: flex;
            gap: .5rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        .badge-kt {
            padding: .3rem .9rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        .badge-lan  { background: rgba(26,79,153,0.4); color: #7eb8ff; border: 1px solid rgba(100,170,255,0.2); }
        .badge-soft { background: rgba(204,85,0,0.4);  color: #ffb07e; border: 1px solid rgba(255,140,0,0.2); }
        .badge-hard { background: rgba(139,0,0,0.4);   color: #ff8080; border: 1px solid rgba(200,0,0,0.2); }

        /* ── Panneau droit — formulaire ───────────── */
        .panel-right {
            width: 440px;
            flex-shrink: 0;
            background: #f8f7f4;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 2.5rem;
        }

        .form-container { width: 100%; max-width: 340px; }

        .form-eyebrow {
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--kt-rouge);
            margin-bottom: .75rem;
        }
        .form-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.75rem;
            font-weight: 800;
            color: #111;
            margin-bottom: .5rem;
            letter-spacing: -.02em;
        }
        .form-desc {
            font-size: .875rem;
            color: #888;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        /* Erreur */
        .alert-error {
            background: #fff0f0;
            border: 1px solid #fca5a5;
            border-left: 3px solid var(--kt-rouge);
            border-radius: 10px;
            padding: .75rem 1rem;
            margin-bottom: 1.25rem;
            font-size: .85rem;
            color: #991b1b;
        }

        /* Champs */
        .field { margin-bottom: 1.1rem; }
        .field label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: #555;
            margin-bottom: .4rem;
            letter-spacing: .02em;
        }
        .field input {
            width: 100%;
            padding: .7rem 1rem;
            background: #fff;
            border: 1.5px solid #e5e3de;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
            color: #111;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .field input:focus {
            border-color: var(--kt-rouge);
            box-shadow: 0 0 0 3px rgba(139,0,0,.08);
        }
        .field input.is-invalid { border-color: var(--kt-rouge); }
        .invalid-msg { color: var(--kt-rouge); font-size: .78rem; margin-top: .3rem; }

        /* Remember */
        .remember-row {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: 1.5rem;
            font-size: .83rem;
            color: #666;
        }
        .remember-row input[type=checkbox] {
            width: 16px; height: 16px;
            accent-color: var(--kt-rouge);
            cursor: pointer;
        }

        /* Bouton */
        .btn-login {
            width: 100%;
            padding: .8rem;
            background: var(--kt-rouge);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'Syne', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: .02em;
            transition: background .2s, transform .15s, box-shadow .2s;
            position: relative;
            overflow: hidden;
        }
        .btn-login::after {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(circle, rgba(255,255,255,.25) 0%, transparent 70%);
            transform: scale(0); opacity: 0;
            transition: transform .4s, opacity .4s;
        }
        .btn-login:active::after { transform: scale(2.5); opacity: 1; transition: 0s; }
        .btn-login:hover {
            background: #a00000;
            box-shadow: 0 6px 20px rgba(139,0,0,.35);
            transform: translateY(-1px);
        }

        .form-footer {
            text-align: center;
            font-size: .75rem;
            color: #bbb;
            margin-top: 2rem;
        }
        .form-footer strong { color: var(--kt-rouge); }

        /* ── Responsive mobile ───────────────────── */
        @media (max-width: 768px) {
            body { flex-direction: column; }
            .panel-left { padding: 2rem 1.5rem; min-height: 220px; }
            .logo-wrapper { padding: 1.25rem 1.75rem; margin-bottom: 1rem; }
            .logo-wrapper img { width: 140px; }
            .left-title { font-size: 1.3rem; }
            .left-subtitle { display: none; }
            .panel-right { width: 100%; padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>

    {{-- Panneau gauche --}}
    <div class="panel-left">
        <div class="left-content">
            <div class="logo-wrapper">
                <img src="{{ asset('images/logo-kt.png') }}" alt="KayTechnologie Gabon">
            </div>
            <h1 class="left-title">Système de Gestion<br>des Tâches</h1>
            <p class="left-subtitle">Planifiez, suivez et reportez vos interventions terrain en temps réel.</p>
            <div class="badges">
                <span class="badge-kt badge-lan">LAN</span>
                <span class="badge-kt badge-soft">Software</span>
                <span class="badge-kt badge-hard">Hardware</span>
            </div>
        </div>
    </div>

    {{-- Panneau droit — formulaire --}}
    <div class="panel-right">
        <div class="form-container">
            <div class="form-eyebrow">Bienvenue</div>
            <h2 class="form-title">Connexion</h2>
            <p class="form-desc">Entrez vos identifiants pour accéder à votre espace.</p>

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

                <button type="submit" class="btn-login">Se connecter →</button>
            </form>

            <div class="form-footer">
                KayTechnologie Gabon &mdash; <strong>SGT v1.0</strong>
            </div>
        </div>
    </div>

</body>
</html>

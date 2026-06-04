<!DOCTYPE html>
<html lang="fr" data-direction="A">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — SGT KayTechnologie</title>
    <link rel="stylesheet" href="{{ asset('charte-graphique.css') }}">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--page-bg, #F6F8FB);
        }
        .login-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(23,58,122,.12);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 400px;
        }
        .login-logo {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: 2rem;
        }
        .login-logo-badge {
            background: var(--kt-navy);
            border-radius: 10px;
            padding: .5rem .75rem;
        }
        .login-logo-badge span {
            color: var(--white);
            font-family: var(--font-display);
            font-weight: 800;
            font-size: 1.1rem;
            letter-spacing: .02em;
        }
        .login-title {
            font-family: var(--font-display);
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--kt-navy);
            margin-bottom: .25rem;
        }
        .login-subtitle {
            font-size: .875rem;
            color: var(--slate-500);
            margin-bottom: 2rem;
        }
        .form-label {
            font-size: .85rem;
            font-weight: 600;
            color: var(--slate-700);
            margin-bottom: .35rem;
            display: block;
        }
        .form-control {
            width: 100%;
            padding: .6rem .875rem;
            border: 1.5px solid var(--slate-200);
            border-radius: 8px;
            font-family: var(--font-ui);
            font-size: .9rem;
            color: var(--slate-800);
            background: var(--slate-50);
            outline: none;
            box-sizing: border-box;
            transition: border-color .2s;
        }
        .form-control:focus { border-color: var(--kt-navy); background: var(--white); }
        .form-control.is-invalid { border-color: var(--kt-maroon); }
        .invalid-feedback { color: var(--kt-maroon); font-size: .8rem; margin-top: .25rem; }
        .form-group { margin-bottom: 1.25rem; }
        .btn-login {
            width: 100%;
            padding: .75rem;
            background: var(--kt-navy);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: background .2s;
            margin-top: .5rem;
        }
        .btn-login:hover { background: var(--kt-navy-700); }
        .remember-row {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: 1rem;
            font-size: .85rem;
            color: var(--slate-600);
        }
        .footer-text {
            text-align: center;
            font-size: .75rem;
            color: var(--slate-400);
            margin-top: 2rem;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-logo">
        <div class="login-logo-badge"><span>KT</span></div>
        <div>
            <div class="login-title">SGT</div>
            <div class="login-subtitle">Système de Gestion des Tâches</div>
        </div>
    </div>

    @if ($errors->any())
        <div style="background:#FEE2E2;border:1px solid #FCA5A5;border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;color:#991B1B;font-size:.875rem;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label class="form-label" for="username">Identifiant</label>
            <input type="text" id="username" name="username"
                   class="form-control @error('username') is-invalid @enderror"
                   value="{{ old('username') }}" autofocus autocomplete="username">
            @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Mot de passe</label>
            <input type="password" id="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="remember-row">
            <input type="checkbox" name="remember" id="remember">
            <label for="remember">Se souvenir de moi</label>
        </div>

        <button type="submit" class="btn-login">Se connecter</button>
    </form>

    <div class="footer-text">KayTechnologie Gabon &mdash; LAN · Software · Hardware</div>
</div>
</body>
</html>

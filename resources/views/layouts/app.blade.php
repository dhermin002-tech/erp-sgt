<!DOCTYPE html>
<html lang="fr" data-direction="{{ auth()->user()->direction_ui ?? 'A' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SGT') — KayTechnologie</title>

    <link rel="stylesheet" href="{{ asset('charte-graphique.css') }}">
    @stack('styles')

    <style>
        /* ── Overrides layout ─────────────────────────────── */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── Flash messages ─────────────────────────────── */
        .flash { padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .875rem; }
        .flash-success { background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; }
        .flash-error   { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }
        .flash-info    { background: #DBEAFE; color: #1E40AF; border: 1px solid #BFDBFE; }

        /* ── Badge retard ─────────────────────────────────── */
        .badge-retard { background: #B0202E; color: #fff; font-size: .7rem; padding: .2rem .5rem; border-radius: 999px; }
        .badge-statut { color: #fff; font-size: .75rem; padding: .2rem .6rem; border-radius: 999px; font-weight: 600; }

        /* ── Direction A : sidebar gauche ─────────────────── */
        [data-direction="A"] .app-wrapper {
            display: flex; min-height: 100vh;
        }
        [data-direction="A"] .sidebar {
            width: 248px; min-height: 100vh; flex-shrink: 0;
            background: linear-gradient(180deg, #001f3f 0%, #003366 60%, #002855 100%);
            color: #fff; display: flex; flex-direction: column;
            box-shadow: 2px 0 16px rgba(0,0,0,.18);
        }
        [data-direction="A"] .sidebar-logo {
            padding: 1.25rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
            display: flex; flex-direction: column; align-items: center; gap: .5rem;
        }
        /* Logo KT image dans la sidebar */
        [data-direction="A"] .sidebar-logo .logo-img-wrap {
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 14px;
            padding: .65rem 1rem;
            display: flex; align-items: center; justify-content: center;
        }
        [data-direction="A"] .sidebar-logo .logo-img-wrap img {
            width: 130px; height: auto;
            filter: brightness(0) invert(1) drop-shadow(0 2px 6px rgba(0,0,0,.3));
        }
        /* Fallback si image absente */
        [data-direction="A"] .sidebar-logo .logo-img-wrap img[src=""],
        [data-direction="A"] .sidebar-logo .logo-img-wrap img:not([src]) {
            display: none;
        }
        [data-direction="A"] .sidebar-logo .logo-sub {
            font-size: .7rem; font-weight: 700; letter-spacing: .12em;
            color: rgba(255,255,255,.35); text-transform: uppercase;
        }
        [data-direction="A"] .sidebar-nav { flex: 1; padding: .75rem 0; }
        [data-direction="A"] .sidebar-nav a {
            display: flex; align-items: center; gap: .65rem;
            padding: .65rem 1.1rem; margin: .1rem .6rem;
            color: rgba(255,255,255,.7);
            text-decoration: none; font-size: .875rem; font-weight: 500;
            border-radius: 9px; transition: all .15s;
        }
        [data-direction="A"] .sidebar-nav a:hover {
            color: #fff; background: rgba(255,255,255,.08);
        }
        [data-direction="A"] .sidebar-nav a.active {
            color: #fff; background: #8B0000;
            box-shadow: 0 4px 12px rgba(139,0,0,.4);
            font-weight: 600;
        }
        [data-direction="A"] .sidebar-nav .nav-label {
            font-size: .68rem; font-weight: 700; letter-spacing: .09em;
            color: rgba(255,255,255,.3); padding: .85rem 1.25rem .3rem;
            text-transform: uppercase;
        }
        [data-direction="A"] .sidebar-footer {
            padding: 1rem 1.25rem; border-top: 1px solid rgba(255,255,255,.07); font-size: .78rem;
        }
        [data-direction="A"] .main-content { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        [data-direction="A"] .top-bar {
            background: var(--white); border-bottom: 1px solid var(--slate-200);
            padding: .75rem 1.5rem; display: flex; align-items: center;
            justify-content: space-between; position: sticky; top: 0; z-index: 10;
        }
        [data-direction="A"] .page-body { padding: 1.5rem; flex: 1; }

        /* ── Direction B : nav haute ──────────────────────── */
        [data-direction="B"] .app-wrapper { display: flex; flex-direction: column; min-height: 100vh; }
        [data-direction="B"] .sidebar { display: none; }
        [data-direction="B"] .top-bar {
            background: linear-gradient(90deg, #001f3f 0%, #003366 100%); color: #fff;
            padding: 0 1.5rem; display: flex; align-items: center;
            justify-content: space-between; position: sticky; top: 0; z-index: 10;
            height: 60px; box-shadow: 0 2px 12px rgba(0,0,0,.2);
            border-bottom: 2px solid #8B0000;
        }
        [data-direction="B"] .top-bar-logo {
            display: flex; align-items: center; gap: .75rem;
        }
        [data-direction="B"] .top-bar-logo img {
            height: 34px; width: auto;
            filter: brightness(0) invert(1);
        }
        [data-direction="B"] .top-bar-logo .logo-sep {
            width: 1px; height: 24px; background: rgba(255,255,255,.2);
        }
        [data-direction="B"] .top-bar-logo .logo-app {
            font-size: .85rem; font-weight: 700; color: rgba(255,255,255,.9);
            letter-spacing: .04em;
        }
        [data-direction="B"] .top-nav { display: flex; align-items: stretch; gap: 0; }
        [data-direction="B"] .top-nav a {
            display: flex; align-items: center; padding: 0 1rem;
            color: rgba(255,255,255,.8); text-decoration: none; font-size: .875rem;
            height: 56px; transition: all .15s; border-bottom: 3px solid transparent;
        }
        [data-direction="B"] .top-nav a:hover,
        [data-direction="B"] .top-nav a.active {
            color: #fff; border-bottom-color: var(--kt-orange);
        }
        [data-direction="B"] .main-content { flex: 1; display: flex; flex-direction: column; min-width: 0; }
        [data-direction="B"] .page-body { padding: 1.5rem; flex: 1; }

        /* ── Responsive mobile ──────────────────────────── */
        .hamburger { display: none; background: none; border: none; cursor: pointer; padding: .5rem; }
        @media (max-width: 768px) {
            [data-direction="A"] .sidebar { display: none; position: fixed; z-index: 100; left: 0; top: 0; height: 100vh; }
            [data-direction="A"] .sidebar.open { display: flex; }
            .hamburger { display: block; }
        }

        /* ── Dropdown user ───────────────────────────────── */
        .user-menu { position: relative; }
        .user-btn { background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: .5rem; font-family: var(--font-ui); }
        .user-dropdown {
            display: none; position: absolute; right: 0; top: calc(100% + 8px);
            background: var(--white); border: 1px solid var(--slate-200); border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,.1); min-width: 200px; z-index: 50;
            overflow: hidden;
        }
        .user-dropdown.open { display: block; }
        .user-dropdown a, .user-dropdown button {
            display: flex; align-items: center; gap: .5rem; width: 100%; text-align: left;
            padding: .65rem 1rem; font-size: .875rem; color: var(--slate-700);
            text-decoration: none; background: none; border: none; cursor: pointer;
            font-family: var(--font-ui);
        }
        .user-dropdown a:hover, .user-dropdown button:hover { background: var(--slate-50); }
        .user-dropdown .divider { border: none; border-top: 1px solid var(--slate-200); margin: .25rem 0; }

        /* ── Notif bell ──────────────────────────────────── */
        .notif-btn { position: relative; background: none; border: none; cursor: pointer; padding: .5rem; }
        .notif-badge {
            position: absolute; top: 2px; right: 2px;
            background: var(--kt-maroon); color: #fff;
            font-size: .65rem; font-weight: 700;
            min-width: 16px; height: 16px; border-radius: 999px;
            display: flex; align-items: center; justify-content: center;
        }

        /* ── Role badge ──────────────────────────────────── */
        .role-chip {
            font-size: .7rem; font-weight: 700; padding: .15rem .5rem; border-radius: 999px;
            background: var(--kt-orange-soft); color: var(--kt-orange);
            text-transform: capitalize;
        }
    </style>
</head>
<body>
<div class="app-wrapper" id="appWrapper">

    {{-- Sidebar (Direction A) --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <div class="logo-img-wrap">
                <img src="{{ asset('images/logo-kt.png') }}" alt="KayTechnologie"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                {{-- Fallback texte si image absente --}}
                <span style="display:none;align-items:center;gap:.4rem;font-family:'IBM Plex Sans',sans-serif;font-weight:800;font-size:1.1rem;color:#fff">
                    <span style="background:#8B0000;border-radius:6px;padding:.2rem .5rem">KT</span>
                    <span style="color:rgba(255,255,255,.85)">Kay<span style="color:#F47A1F">Tech</span></span>
                </span>
            </div>
            <span class="logo-sub">Gestion des Tâches</span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Navigation</div>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                📊 Tableau de bord
            </a>
            <a href="{{ route('taches.index') }}" class="{{ request()->routeIs('taches.index') || request()->routeIs('taches.show') || request()->routeIs('taches.create') || request()->routeIs('taches.edit') ? 'active' : '' }}">
                ✅ Tâches
            </a>
            <a href="{{ route('taches.archives') }}" class="{{ request()->routeIs('taches.archives') ? 'active' : '' }}">
                🗄 Archives
            </a>
            @if(auth()->user()->isManager())
            <div class="nav-label">Administration</div>
            <a href="{{ route('membres.index') }}" class="{{ request()->routeIs('membres.*') ? 'active' : '' }}">👥 Membres</a>
            <a href="{{ route('sites.index') }}" class="{{ request()->routeIs('sites.*') ? 'active' : '' }}">📍 Sites</a>
            @endif
        </nav>
        <div class="sidebar-footer" style="color:rgba(255,255,255,.35);font-size:.72rem;text-align:center">
            SGT v1.0 &mdash; KayTechnologie Gabon
        </div>
    </aside>

    {{-- Contenu principal --}}
    <div class="main-content">

        {{-- Barre supérieure --}}
        <header class="top-bar">
            {{-- Direction A : titre page + hamburger --}}
            <div style="display:flex;align-items:center;gap:.75rem">
                <button class="hamburger" onclick="document.getElementById('sidebar').classList.toggle('open')" aria-label="Menu">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                {{-- Direction B logo --}}
                <span class="top-bar-logo" style="display:none">
                    <img src="{{ asset('images/logo-kt.png') }}" alt="KayTechnologie">
                    <span class="logo-sep"></span>
                    <span class="logo-app">SGT</span>
                </span>
                {{-- Direction B nav --}}
                <nav class="top-nav">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                    <a href="{{ route('taches.index') }}" class="{{ request()->routeIs('taches.index','taches.show','taches.create','taches.edit') ? 'active' : '' }}">Tâches</a>
                    <a href="{{ route('taches.archives') }}" class="{{ request()->routeIs('taches.archives') ? 'active' : '' }}">Archives</a>
                    @if(auth()->user()->isManager())
                    <a href="#">Membres</a>
                    <a href="{{ route('sites.index') }}" class="{{ request()->routeIs('sites.*') ? 'active' : '' }}">Sites</a>
                    @endif
                </nav>
            </div>

            {{-- Droite : notifs + user --}}
            <div style="display:flex;align-items:center;gap:.75rem">
                {{-- Bascule lang FR/EN --}}
                @php $locale = app()->getLocale(); @endphp
                <div style="display:flex;align-items:center;gap:.2rem;font-size:.78rem;font-weight:700">
                    <form method="POST" action="{{ route('preferences.locale') }}" style="display:inline">
                        @csrf
                        <input type="hidden" name="locale" value="fr">
                        <button type="submit" style="background:none;border:none;cursor:pointer;padding:.2rem .35rem;border-radius:4px;font-weight:700;font-size:.78rem;{{ $locale==='fr' ? 'color:var(--kt-navy);text-decoration:underline' : 'color:var(--slate-400)' }}">FR</button>
                    </form>
                    <span style="color:var(--slate-300)">|</span>
                    <form method="POST" action="{{ route('preferences.locale') }}" style="display:inline">
                        @csrf
                        <input type="hidden" name="locale" value="en">
                        <button type="submit" style="background:none;border:none;cursor:pointer;padding:.2rem .35rem;border-radius:4px;font-weight:700;font-size:.78rem;{{ $locale==='en' ? 'color:var(--kt-navy);text-decoration:underline' : 'color:var(--slate-400)' }}">EN</button>
                    </form>
                </div>

                {{-- Bascule thème A/B --}}
                <button onclick="toggleDirectionAjax()" style="background:none;border:none;cursor:pointer;font-size:.82rem;color:var(--slate-500);padding:.2rem .4rem;border-radius:5px;border:1px solid var(--slate-200)" title="Direction A/B">
                    🎨 <span id="dirLabel">Dir. {{ auth()->user()->direction_ui }}</span>
                </button>

                {{-- Notifications --}}
                <div class="user-menu" id="notifMenu">
                    <button class="notif-btn" onclick="toggleNotifDropdown()" title="Notifications" id="notifBell">
                        🔔
                        @php $nbNotifs = auth()->user()->unreadNotifications()->count(); @endphp
                        @if($nbNotifs > 0)
                        <span class="notif-badge" id="notifBadge">{{ $nbNotifs > 9 ? '9+' : $nbNotifs }}</span>
                        @else
                        <span class="notif-badge" id="notifBadge" style="display:none">0</span>
                        @endif
                    </button>
                    <div class="user-dropdown" id="notifDropdown" style="width:320px;right:0;left:auto">
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:.65rem 1rem;border-bottom:1px solid var(--slate-100)">
                            <span style="font-size:.85rem;font-weight:700;color:var(--kt-navy)">Notifications</span>
                            <form method="POST" action="{{ route('notifications.tout-lire') }}">
                                @csrf @method('PATCH')
                                <button type="submit" style="background:none;border:none;cursor:pointer;font-size:.78rem;color:var(--kt-navy)">Tout lire</button>
                            </form>
                        </div>
                        @php $recentes = auth()->user()->notifications()->latest()->limit(8)->get(); @endphp
                        @forelse($recentes as $notif)
                        @php $data = $notif->data; @endphp
                        <a href="{{ $data['url'] ?? '#' }}" style="display:block;padding:.6rem 1rem;border-bottom:1px solid var(--slate-50);text-decoration:none;background:{{ is_null($notif->read_at) ? 'var(--slate-50)' : '#fff' }}">
                            <div style="font-size:.82rem;color:var(--slate-700);font-weight:{{ is_null($notif->read_at) ? '600' : '400' }};line-height:1.4">{{ $data['message'] ?? '—' }}</div>
                            <div style="font-size:.72rem;color:var(--slate-400);margin-top:.2rem">{{ $notif->created_at->diffForHumans() }}</div>
                        </a>
                        @empty
                        <div style="padding:1rem;text-align:center;font-size:.85rem;color:var(--slate-400)">Aucune notification</div>
                        @endforelse
                        <a href="{{ route('notifications.index') }}" style="display:block;text-align:center;padding:.6rem;font-size:.82rem;color:var(--kt-navy);text-decoration:none;border-top:1px solid var(--slate-100)">Voir toutes →</a>
                    </div>
                </div>

                {{-- User menu --}}
                <div class="user-menu">
                    <button class="user-btn" onclick="this.nextElementSibling.classList.toggle('open')">
                        <span style="font-size:.875rem;font-weight:600;color:var(--slate-700)">{{ auth()->user()->nom_complet }}</span>
                        <span class="role-chip">{{ auth()->user()->role }}</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="user-dropdown">
                        <a href="#">👤 Mon profil</a>
                        <a href="#">⚙️ Préférences</a>
                        <hr class="divider">
                        {{-- ERR-PHP-002 : logout via fetch() + finally pour éviter erreur CSRF 419 --}}
                        <button onclick="logoutSafe()">🚪 Se déconnecter</button>
                    </div>
                </div>
            </div>
        </header>

        {{-- Corps de la page --}}
        <main class="page-body">
            @if (session('success'))
                <div class="flash flash-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="flash flash-error">{{ session('error') }}</div>
            @endif
            @if (session('info'))
                <div class="flash flash-info">{{ session('info') }}</div>
            @endif

            @yield('content')
        </main>

    </div>{{-- /main-content --}}

</div>{{-- /app-wrapper --}}

{{-- Logout form CSRF-safe (ERR-PHP-002) --}}
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">
    @csrf
</form>

<script src="{{ asset('theme-switcher.js') }}"></script>
<script>
function logoutSafe() {
    fetch('{{ route('logout') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
    }).finally(() => { window.location.href = '{{ route('login') }}'; });
}

function toggleDirectionAjax() {
    const html = document.documentElement;
    const newDir = html.dataset.direction === 'A' ? 'B' : 'A';
    fetch('{{ route('preferences.direction') }}', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ direction: newDir })
    }).then(r => r.json()).then(d => {
        if (d.ok) {
            html.dataset.direction = d.direction;
            localStorage.setItem('sgt_direction', d.direction);
            const lbl = document.getElementById('dirLabel');
            if (lbl) lbl.textContent = 'Dir. ' + d.direction;
            syncDirectionUI();
        }
    });
}

function syncDirectionUI() {
    const dir = document.documentElement.dataset.direction;
    document.querySelectorAll('.top-bar-logo').forEach(el => {
        el.style.display = dir === 'B' ? 'flex' : 'none';
    });
}

function toggleNotifDropdown() {
    document.getElementById('notifDropdown').classList.toggle('open');
}

// Fermer les dropdowns au clic extérieur
document.addEventListener('click', function(e) {
    if (! e.target.closest('.user-menu')) {
        document.querySelectorAll('.user-dropdown').forEach(d => d.classList.remove('open'));
    }
});

document.addEventListener('DOMContentLoaded', syncDirectionUI);
</script>
@stack('scripts')
</body>
</html>

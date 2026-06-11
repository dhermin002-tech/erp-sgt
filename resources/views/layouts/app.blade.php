<!DOCTYPE html>
<html lang="fr" data-direction="{{ auth()->user()->direction_ui ?? 'A' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SGT') — KayTechnologie</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    <meta name="theme-color" content="#003366">

    <link rel="stylesheet" href="{{ asset('charte-graphique.css') }}">
    <link rel="stylesheet" href="{{ asset('sgt-premium.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
        /* Logo KT — fond blanc discret bien proportionné */
        [data-direction="A"] .sidebar-logo .logo-img-wrap {
            background: #fff;
            border-radius: 8px;
            padding: .35rem .7rem;
            display: inline-flex; align-items: center; justify-content: center;
            box-shadow: 0 1px 6px rgba(0,0,0,.2);
        }
        [data-direction="A"] .sidebar-logo .logo-img-wrap img {
            width: 96px; height: auto;
            display: block;
        }
        [data-direction="A"] .sidebar-logo .logo-sub {
            font-size: .68rem; font-weight: 700; letter-spacing: .14em;
            color: rgba(255,255,255,.3); text-transform: uppercase;
            margin-top: .15rem;
        }
        [data-direction="A"] .sidebar-nav { flex: 1; padding: .75rem 0; }
        [data-direction="A"] .sidebar-nav a {
            display: flex; align-items: center; gap: .75rem;
            padding: .78rem 1.1rem; margin: .15rem .6rem;
            color: rgba(255,255,255,.65);
            text-decoration: none; font-size: .9rem; font-weight: 500;
            border-radius: 10px; transition: all .15s;
        }
        [data-direction="A"] .sidebar-nav a i,
        [data-direction="A"] .sidebar-nav a .nav-icon {
            font-size: 1.1rem; width: 20px; text-align: center; flex-shrink: 0;
            opacity: .75;
        }
        [data-direction="A"] .sidebar-nav a:hover {
            color: #fff; background: rgba(255,255,255,.1);
        }
        [data-direction="A"] .sidebar-nav a:hover i,
        [data-direction="A"] .sidebar-nav a:hover .nav-icon { opacity: 1; }
        [data-direction="A"] .sidebar-nav a.active {
            color: #fff; background: #CC5500;
            box-shadow: 0 4px 14px rgba(204,85,0,.4);
            font-weight: 700;
        }
        [data-direction="A"] .sidebar-nav a.active i,
        [data-direction="A"] .sidebar-nav a.active .nav-icon { opacity: 1; }
        [data-direction="A"] .sidebar-nav .nav-label {
            font-size: .65rem; font-weight: 700; letter-spacing: .1em;
            color: rgba(255,255,255,.25); padding: .9rem 1.25rem .35rem;
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
        /* En Direction A, la nav horizontale (.top-nav) appartient à la Direction B :
           elle ne doit jamais apparaître ici, sinon ses liens s'empilent en colonne sur mobile. */
        [data-direction="A"] .top-nav { display: none !important; }

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
            height: 36px; width: auto;
            background: #fff;
            border-radius: 8px;
            padding: 3px 6px;
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
        .hamburger {
            display: none; background: var(--slate-50, #f3f1ec); border: none; cursor: pointer;
            width: 44px; height: 44px; min-width: 44px; border-radius: 10px;
            align-items: center; justify-content: center; color: var(--kt-navy, #003366);
        }
        .sidebar-overlay {
            display: none; position: fixed; inset: 0; background: rgba(0,15,35,.55);
            backdrop-filter: blur(2px); z-index: 99;
        }
        .sidebar-overlay.show { display: block; }

        @media (max-width: 768px) {
            [data-direction="A"] .sidebar {
                display: flex; position: fixed; z-index: 100; left: 0; top: 0; height: 100vh;
                width: min(86vw, 280px);
                transform: translateX(-100%);
                transition: transform .3s cubic-bezier(0.16,1,0.3,1);
                box-shadow: 12px 0 40px rgba(0,0,0,.25);
            }
            [data-direction="A"] .sidebar.open { transform: translateX(0); }
            .hamburger { display: flex; }

            /* Header compact : on masque le superflu, on garde burger + notif + avatar */
            .top-bar { padding: .6rem .85rem; gap: .5rem; }
            .top-bar .lang-switch,
            .top-bar .direction-toggle,
            .user-btn .user-fullname { display: none !important; }
            .role-chip { font-size: .65rem; padding: .12rem .4rem; }
            .user-btn { gap: .35rem; padding: .3rem; min-height: 44px; }
            .notif-btn { min-width: 44px; min-height: 44px; }
            .user-dropdown { right: -.5rem; min-width: 180px; }
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

        /* ── Role badge — couleur selon rôle ─────────────── */
        .role-chip {
            font-size: .7rem; font-weight: 700; padding: .15rem .5rem; border-radius: 999px;
            text-transform: capitalize; white-space: nowrap;
        }
        .role-chip[data-role="manager"]    { background: rgba(0,51,102,.12);  color: #003366; }
        .role-chip[data-role="technicien"] { background: rgba(37,99,235,.12); color: #1D4ED8; }
        .role-chip[data-role="agent"]      { background: rgba(22,163,74,.12); color: #16A34A; }
        .role-chip[data-role="dev"]        { background: rgba(126,34,206,.12);color: #7E22CE; }
        .role-chip[data-role="stagiaire"]  { background: rgba(100,116,139,.12);color: #475569; }
        /* fallback si rôle inconnu */
        .role-chip:not([data-role])        { background: var(--kt-orange-soft); color: var(--kt-orange); }

        /* ── Top-bar mobile améliorée ─────────────────────── */
        @media (max-width: 768px) {
            /* Logo SGT visible à gauche du hamburger */
            .mobile-logo-wrap {
                display: flex !important;
                align-items: center;
                gap: .45rem;
            }
            .mobile-logo-wrap img {
                height: 30px; width: auto;
                background: #fff;
                border-radius: 6px;
                padding: 2px 5px;
                display: block;
            }
            .mobile-logo-wrap .mobile-logo-name {
                font-family: 'Space Grotesk', sans-serif;
                font-size: .82rem; font-weight: 700;
                color: var(--kt-navy, #003366);
                letter-spacing: .03em;
                white-space: nowrap;
            }
            /* Top-bar hauteur min 56px */
            [data-direction="A"] .top-bar,
            [data-direction="B"] .top-bar {
                min-height: 56px;
            }
            /* Cloche — zone de tap 44×44 */
            .notif-btn {
                min-width: 44px !important; min-height: 44px !important;
                display: flex !important; align-items: center; justify-content: center;
                font-size: 1.1rem;
            }
        }

        /* Logo wrap mobile — caché sur desktop */
        .mobile-logo-wrap { display: none; }

        /* ── Sidebar — ligne décorative orange en bas du logo ─ */
        [data-direction="A"] .sidebar-logo {
            border-bottom: 2px solid transparent !important;
            position: relative;
        }
        [data-direction="A"] .sidebar-logo::after {
            content: '';
            position: absolute;
            bottom: 0; left: 1.25rem; right: 1.25rem;
            height: 2px;
            background: linear-gradient(90deg, transparent, #CC5500 30%, #CC5500 70%, transparent);
            border-radius: 999px;
        }
    </style>
</head>
<body>
<div class="app-wrapper" id="appWrapper">

    {{-- Overlay assombri derrière le drawer mobile --}}
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebarMobile()"></div>

    {{-- Sidebar (Direction A) --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <div class="logo-img-wrap">
                <img src="{{ asset('images/logo-kt.jpg') }}" alt="KayTechnologie"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                {{-- Fallback texte si image absente --}}
                <span style="display:none;align-items:center;gap:.4rem;font-family:'IBM Plex Sans',sans-serif;font-weight:800;font-size:1.1rem;color:#1a1a1a">
                    <span style="background:#8B0000;color:#fff;border-radius:6px;padding:.2rem .5rem">KT</span>
                    <span style="color:#1a1a1a">Kay<span style="color:#CC5500">Tech</span></span>
                </span>
            </div>
            <span class="logo-sub">Gestion des Tâches</span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-label">Navigation</div>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Tableau de bord
            </a>
            <a href="{{ route('taches.index') }}" class="{{ request()->routeIs('taches.index') || request()->routeIs('taches.show') || request()->routeIs('taches.create') || request()->routeIs('taches.edit') ? 'active' : '' }}">
                <i class="bi bi-check2-square"></i> Tâches
            </a>
            <a href="{{ route('taches.archives') }}" class="{{ request()->routeIs('taches.archives') ? 'active' : '' }}">
                <i class="bi bi-archive"></i> Archives
            </a>
            <a href="{{ route('rapports.index') }}" class="{{ request()->routeIs('rapports.index') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph"></i> Rapport
            </a>
            @if(auth()->user()->isManager())
            <div class="nav-label">Administration</div>
            <a href="{{ route('membres.index') }}" class="{{ request()->routeIs('membres.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Membres
            </a>
            <a href="{{ route('sites.index') }}" class="{{ request()->routeIs('sites.*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt"></i> Sites
            </a>
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
                {{-- Logo SGT visible sur mobile à gauche du hamburger --}}
                <span class="mobile-logo-wrap">
                    <img src="{{ asset('images/logo-kt.jpg') }}" alt="SGT"
                         onerror="this.style.display='none'">
                    <span class="mobile-logo-name">SGT</span>
                </span>
                <button class="hamburger" onclick="toggleSidebarMobile()" aria-label="Menu">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                {{-- Direction B logo --}}
                <span class="top-bar-logo" style="display:none">
                    <img src="{{ asset('images/logo-kt.jpg') }}" alt="KayTechnologie">
                    <span class="logo-sep"></span>
                    <span class="logo-app">SGT</span>
                </span>
                {{-- Direction B nav --}}
                <nav class="top-nav">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                    <a href="{{ route('taches.index') }}" class="{{ request()->routeIs('taches.index','taches.show','taches.create','taches.edit') ? 'active' : '' }}">Tâches</a>
                    <a href="{{ route('taches.archives') }}" class="{{ request()->routeIs('taches.archives') ? 'active' : '' }}">Archives</a>
                    <a href="{{ route('rapports.index') }}" class="{{ request()->routeIs('rapports.index') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-bar-graph"></i> Rapport
                    </a>
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
                <div class="lang-switch" style="display:flex;align-items:center;gap:.2rem;font-size:.78rem;font-weight:700">
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
                <button class="direction-toggle" onclick="toggleDirectionAjax()" style="background:none;border:none;cursor:pointer;font-size:.82rem;color:var(--slate-500);padding:.2rem .4rem;border-radius:5px;border:1px solid var(--slate-200)" title="Direction A/B">
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
                        <span class="user-fullname" style="font-size:.875rem;font-weight:600;color:var(--slate-700)">{{ auth()->user()->nom_complet }}</span>
                        <span class="role-chip" data-role="{{ strtolower(auth()->user()->role) }}">{{ auth()->user()->role }}</span>
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
const csrfToken = document.querySelector('meta[name=csrf-token]').content;

function toggleSidebarMobile() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('show');
    document.body.style.overflow = document.getElementById('sidebar').classList.contains('open') ? 'hidden' : '';
}
function closeSidebarMobile() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('show');
    document.body.style.overflow = '';
}

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

{{-- SGT Premium JS — animations + compteurs ─────────────────────────── --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── 1. Scroll-triggered animations (.animate-in) ───────────────────────
    const animObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) {
                e.target.classList.add('visible');
                animObserver.unobserve(e.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -32px 0px' });

    document.querySelectorAll('.animate-in').forEach(function (el) {
        animObserver.observe(el);
    });

    // ── 2. Compteurs KPI animés (data-target) ─────────────────────────────
    function animateCounter(el) {
        var target = parseInt(el.dataset.target, 10);
        if (isNaN(target)) return;
        var duration = 900;
        var start = performance.now();
        function update(now) {
            var progress = Math.min((now - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(eased * target);
            if (progress < 1) requestAnimationFrame(update);
        }
        requestAnimationFrame(update);
    }

    var kpiObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) {
                animateCounter(e.target);
                kpiObserver.unobserve(e.target);
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('.kpi-value[data-target]').forEach(function (el) {
        kpiObserver.observe(el);
    });

    // ── 3. Fade-out au clic sur lien interne ──────────────────────────────
    document.querySelectorAll('a[href]').forEach(function (a) {
        var href = a.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript')
            || a.target === '_blank' || a.hasAttribute('data-no-transition')) return;
        a.addEventListener('click', function (e) {
            var el = document.querySelector('.page-body');
            if (!el) return;
            e.preventDefault();
            var dest = href;
            el.style.transition = 'opacity 0.18s ease, transform 0.18s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(6px)';
            setTimeout(function () { window.location.href = dest; }, 185);
        });
    });

    // ── 4. Classes stagger-grid + reveal-up sur KPI grid ─────────────────
    var kpiGrid = document.querySelector('.kpi-grid');
    if (kpiGrid) {
        kpiGrid.classList.add('stagger-grid');
        kpiGrid.querySelectorAll('.kpi-card').forEach(function (card) {
            card.classList.add('reveal-up');
        });
    }

    // Task rows — animate-in au scroll
    document.querySelectorAll('.kt-task-row').forEach(function (row) {
        row.classList.add('animate-in');
        animObserver.observe(row);
    });

});
</script>
@stack('scripts')
</body>
</html>

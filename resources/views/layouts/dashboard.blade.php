<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — PizzaFlow</title>
    <meta name="theme-color" content="#E63946">

    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🍕</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    @stack('styles')
</head>
<body class="pf-dash-body">
    <div class="pf-dash-shell">
        {{-- Sidebar --}}
        <aside class="pf-dash-sidebar" id="dashSidebar">
            <div class="pf-dash-brand">
                <span class="pf-dash-brand-icon"><i class="bi bi-pie-chart-fill"></i></span>
                <div>
                    <strong>PizzaFlow</strong>
                    <small>{{ strtoupper($role->label()) }} PORTAL</small>
                </div>
            </div>

            <nav class="pf-dash-nav">
                <div class="pf-dash-nav-group">
                    <p class="pf-dash-nav-label">Main</p>
                    <a href="{{ route($role->dashboardRoute()) }}"
                       class="pf-dash-nav-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
                        <i class="bi bi-grid-1x2"></i>
                        <span>Overview</span>
                    </a>
                </div>

                @foreach ($navGroups as $group => $items)
                    <div class="pf-dash-nav-group">
                        <p class="pf-dash-nav-label">{{ strtoupper($group) }}</p>
                        @foreach ($items as $item)
                            @php
                                $itemUrl = !empty($item['route']) && \Illuminate\Support\Facades\Route::has($item['route'])
                                    ? route($item['route'])
                                    : route($role->dashboardRoute()).'#module-'.$item['key'];
                                $routePrefix = !empty($item['route']) ? explode('.', $item['route'])[0].'.*' : null;
                                $itemActive = $routePrefix && request()->routeIs($routePrefix);
                            @endphp
                            <a href="{{ $itemUrl }}"
                               class="pf-dash-nav-link {{ $itemActive ? 'active' : '' }}">
                                <i class="bi {{ $item['icon'] }}"></i>
                                <span>{{ $item['short'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>

            <div class="pf-dash-sidebar-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="pf-dash-nav-link w-100 text-start border-0 bg-transparent">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main column --}}
        <div class="pf-dash-main">
            <header class="pf-dash-topbar">
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="pf-dash-toggle" id="dashSidebarToggle" aria-label="Toggle sidebar">
                        <i class="bi bi-list"></i>
                    </button>
                    <h1 class="pf-dash-page-title mb-0">@yield('page_title', 'Overview')</h1>
                </div>

                <div class="pf-dash-top-actions">
                    <button type="button" class="pf-dash-icon-btn" onclick="window.location.reload()" aria-label="Refresh">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>

                    @php
                        $notifications = $notifications ?? collect();
                        $unreadNotifications = $unreadNotifications ?? 0;
                    @endphp

                    <div class="dropdown">
                        <button type="button"
                                class="pf-dash-icon-btn"
                                id="dashNotifyBtn"
                                data-bs-toggle="dropdown"
                                data-bs-auto-close="outside"
                                aria-expanded="false"
                                aria-label="Notifications">
                            <i class="bi bi-bell"></i>
                            @if ($unreadNotifications > 0)
                                <span class="pf-dash-badge">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
                            @endif
                        </button>

                        <div class="dropdown-menu dropdown-menu-end pf-notify-menu" aria-labelledby="dashNotifyBtn">
                            <div class="pf-notify-head">
                                <strong>Notifications</strong>
                                @if ($unreadNotifications > 0)
                                    <form method="POST" action="{{ route('notifications.read-all') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-link btn-sm p-0">Mark all read</button>
                                    </form>
                                @endif
                            </div>

                            <div class="pf-notify-list">
                                @forelse ($notifications as $note)
                                    <form method="POST" action="{{ route('notifications.read', $note->_id) }}" class="pf-notify-item-form">
                                        @csrf
                                        <button type="submit" class="pf-notify-item {{ $note->isUnread() ? 'is-unread' : '' }}">
                                            <span class="pf-notify-icon"><i class="bi {{ $note->icon() }}"></i></span>
                                            <span class="pf-notify-body">
                                                <strong>{{ $note->title }}</strong>
                                                <small>{{ $note->body }}</small>
                                                <em>{{ optional($note->created_at)->diffForHumans() }}</em>
                                            </span>
                                        </button>
                                    </form>
                                @empty
                                    <div class="pf-notify-empty text-muted">
                                        No notifications yet.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="pf-dash-userchip">
                        <span class="pf-dash-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        <div class="pf-dash-userchip-text">
                            <strong>{{ $user->name }}</strong>
                            <small>{{ $role->label() }}</small>
                        </div>
                    </div>
                </div>
            </header>

            <main class="pf-dash-content">
                @if (session('success'))
                    <div class="alert alert-success pf-alert">{{ session('success') }}</div>
                @endif

                @yield('content')
            </main>

            <footer class="pf-dash-footer">
                <span>&copy; {{ date('Y') }} PizzaFlow</span>
                <span>Version 1.0.0</span>
            </footer>
        </div>
    </div>

    <div class="pf-dash-overlay" id="dashOverlay"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
        (function () {
            const sidebar = document.getElementById('dashSidebar');
            const toggle = document.getElementById('dashSidebarToggle');
            const overlay = document.getElementById('dashOverlay');

            function closeSidebar() {
                sidebar?.classList.remove('open');
                overlay?.classList.remove('show');
            }

            toggle?.addEventListener('click', () => {
                sidebar?.classList.toggle('open');
                overlay?.classList.toggle('show');
            });

            overlay?.addEventListener('click', closeSidebar);
        })();
    </script>
    @stack('scripts')
</body>
</html>

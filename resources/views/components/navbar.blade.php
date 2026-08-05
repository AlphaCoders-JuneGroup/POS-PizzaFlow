{{-- Sticky Navigation Bar --}}
<nav class="navbar navbar-expand-lg pf-navbar sticky-top" id="mainNavbar">
    <div class="container">
        {{-- Brand Logo --}}
        <a class="navbar-brand pf-brand" href="{{ route('home') }}#home">
            <span class="pf-brand-icon"><i class="bi bi-pie-chart-fill"></i></span>
            <span class="pf-brand-text">Pizza<span>Flow</span></span>
        </a>

        {{-- Mobile Toggle --}}
        <button class="navbar-toggler pf-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        {{-- Nav Links --}}
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto pf-nav-links">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}#home">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}#menu">Menu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}#offers">Offers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}#about">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}#contact">Contact</a>
                </li>
            </ul>

            {{-- Auth & Cart Actions --}}
            <div class="d-flex align-items-center gap-2 pf-nav-actions">
                @auth
                    @php
                        $navUser = auth()->user();
                        $navAccountRoute = $navUser->isCustomer()
                            ? route('profile.index')
                            : route($navUser->dashboardRoute());
                    @endphp
                    <a href="{{ $navAccountRoute }}" class="btn btn-pf-outline btn-sm">
                        <i class="bi bi-person-circle me-1"></i>
                        {{ Str::limit($navUser->name, 12) }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-pf-primary btn-sm">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-pf-outline btn-sm">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-pf-primary btn-sm">Register</a>
                @endauth

                <button type="button" class="pf-cart-btn position-relative" id="cartDrawerTrigger" aria-label="Open shopping cart">
                    <i class="bi bi-cart3"></i>
                    <span class="pf-cart-badge" id="cartCount">0</span>
                </button>
            </div>
        </div>
    </div>
</nav>

@if (session('guest_checkout') && !auth()->check())
    <div class="container mt-2">
        <div class="alert alert-info pf-alert d-flex flex-wrap justify-content-between align-items-center gap-2 mb-0">
            <span>
                <i class="bi bi-person-walking me-1"></i>
                Guest checkout active for <strong>{{ session('guest.name') }}</strong>
            </span>
            <form method="POST" action="{{ route('guest.destroy') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">Clear guest</button>
            </form>
        </div>
    </div>
@endif

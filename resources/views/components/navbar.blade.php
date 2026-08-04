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
                    <a class="nav-link active" href="#home">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#menu">Menu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#offers">Offers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#about">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contact">Contact</a>
                </li>
            </ul>

            {{-- Auth & Cart Actions --}}
            <div class="d-flex align-items-center gap-2 pf-nav-actions">
                <a href="#login" class="btn btn-pf-outline btn-sm">Login</a>
                <a href="#register" class="btn btn-pf-primary btn-sm">Register</a>
                <a href="#cart" class="pf-cart-btn" aria-label="Shopping cart">
                    <i class="bi bi-cart3"></i>
                    <span class="pf-cart-badge" id="cartCount">0</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    {{-- SEO Meta Tags --}}
    <title>@yield('title', 'PizzaFlow — Fresh Pizza Delivered Hot & Fast')</title>
    <meta name="description" content="@yield('meta_description', 'PizzaFlow is a modern online pizza ordering system. Customize your favorite pizza and enjoy fast doorstep delivery.')">
    <meta name="keywords" content="pizza, order pizza online, PizzaFlow, fast delivery, Italian pizza, Sri Lanka">
    <meta name="author" content="PizzaFlow">
    <meta name="theme-color" content="#E63946">

    {{-- Open Graph --}}
    <meta property="og:title" content="@yield('title', 'PizzaFlow — Fresh Pizza Delivered Hot & Fast')">
    <meta property="og:description" content="Customize your favorite pizza and enjoy fast doorstep delivery.">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="PizzaFlow">

    {{-- Favicon --}}
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🍕</text></svg>">

    {{-- Google Fonts: Poppins --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- AOS Animations --}}
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">

    {{-- PizzaFlow Custom Styles --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    @stack('styles')
</head>
<body>
    {{-- Page Loader --}}
    <div id="page-loader" aria-hidden="true">
        <div class="loader-spinner">
            <i class="bi bi-pie-chart-fill"></i>
        </div>
    </div>

    {{-- Sticky Navigation --}}
    @include('components.navbar')

    {{-- Main Content --}}
    <main id="main-content">
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('components.footer')

    {{-- Back to Top --}}
    <button type="button" id="back-to-top" class="back-to-top" aria-label="Back to top">
        <i class="bi bi-arrow-up"></i>
    </button>

    {{-- Bootstrap 5 JS Bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    {{-- AOS Animations --}}
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>

    {{-- PizzaFlow Custom Scripts --}}
    <script src="{{ asset('js/app.js') }}"></script>

    @stack('scripts')
</body>
</html>

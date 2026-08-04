@extends('layouts.auth')

@section('title', 'Login — PizzaFlow')

@section('content')
<section class="pf-auth-section pf-auth-section-solo">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="pf-auth-card" data-aos="fade-up">
                    <div class="text-center mb-4">
                        <a href="{{ route('home') }}" class="pf-brand-icon d-inline-grid mb-3 text-decoration-none">
                            <i class="bi bi-pie-chart-fill"></i>
                        </a>
                        <h1 class="pf-auth-title">Welcome back</h1>
                        <p class="pf-auth-sub">Sign in to manage orders, track delivery, or access your dashboard.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger pf-alert">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email"
                                   class="form-control pf-input @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required autofocus
                                   placeholder="you@email.com">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password"
                                   class="form-control pf-input @error('password') is-invalid @enderror"
                                   required placeholder="••••••••">
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember"
                                       {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-pf-primary w-100 btn-lg mb-3">
                            Login
                        </button>
                    </form>

                    <div class="pf-auth-divider"><span>or</span></div>

                    <a href="{{ route('home') }}" class="btn btn-pf-outline w-100 mb-3">
                        <i class="bi bi-person-walking me-1"></i> Continue as Guest
                    </a>

                    <p class="text-center mb-0 pf-auth-footer-text">
                        New to PizzaFlow?
                        <a href="{{ route('register') }}">Create an account</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

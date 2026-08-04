@extends('layouts.auth')

@section('title', 'Register — PizzaFlow')

@section('content')
<section class="pf-auth-section pf-auth-section-solo">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="pf-auth-card" data-aos="fade-up">
                    <div class="text-center mb-4">
                        <a href="{{ route('home') }}" class="pf-brand-icon d-inline-grid mb-3 text-decoration-none">
                            <i class="bi bi-pie-chart-fill"></i>
                        </a>
                        <h1 class="pf-auth-title">Create your account</h1>
                        <p class="pf-auth-sub">Register as a customer to save addresses, track orders, and reorder faster.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger pf-alert">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.store') }}" novalidate>
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="name" class="form-label">Full name</label>
                                <input type="text" name="name" id="name"
                                       class="form-control pf-input" value="{{ old('name') }}"
                                       required placeholder="Your name">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email"
                                       class="form-control pf-input" value="{{ old('email') }}"
                                       required placeholder="you@email.com">
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" name="phone" id="phone"
                                       class="form-control pf-input" value="{{ old('phone') }}"
                                       required placeholder="+94 77 123 4567">
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" id="password"
                                       class="form-control pf-input" required placeholder="Min. 8 characters">
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirm password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="form-control pf-input" required placeholder="Repeat password">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-pf-primary w-100 btn-lg mt-4 mb-3">
                            Register
                        </button>
                    </form>

                    <div class="pf-auth-divider"><span>or</span></div>

                    <a href="{{ route('home') }}" class="btn btn-pf-outline w-100 mb-3">
                        Checkout as Guest
                    </a>

                    <p class="text-center mb-0 pf-auth-footer-text">
                        Already have an account?
                        <a href="{{ route('login') }}">Login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

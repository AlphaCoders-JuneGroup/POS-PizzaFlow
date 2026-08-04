@extends('layouts.app')

@section('title', 'Guest Checkout — PizzaFlow')

@section('content')
<section class="pf-auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="pf-auth-card" data-aos="fade-up">
                    <div class="text-center mb-4">
                        <div class="pf-brand-icon mx-auto mb-3"><i class="bi bi-bag-check"></i></div>
                        <h1 class="pf-auth-title">Checkout as Guest</h1>
                        <p class="pf-auth-sub">No account needed. Save your contact &amp; delivery details for this order.</p>
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

                    <form method="POST" action="{{ route('guest.store') }}" novalidate>
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="name">Full name</label>
                                <input type="text" name="name" id="name" class="form-control pf-input"
                                       value="{{ old('name', $guest['name'] ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="phone">Phone</label>
                                <input type="text" name="phone" id="phone" class="form-control pf-input"
                                       value="{{ old('phone', $guest['phone'] ?? '') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="email">Email</label>
                                <input type="email" name="email" id="email" class="form-control pf-input"
                                       value="{{ old('email', $guest['email'] ?? '') }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="address_line">Delivery address</label>
                                <input type="text" name="address_line" id="address_line" class="form-control pf-input"
                                       value="{{ old('address_line', $guest['address_line'] ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="city">City</label>
                                <input type="text" name="city" id="city" class="form-control pf-input"
                                       value="{{ old('city', $guest['city'] ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="postal_code">Postal code</label>
                                <input type="text" name="postal_code" id="postal_code" class="form-control pf-input"
                                       value="{{ old('postal_code', $guest['postal_code'] ?? '') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="delivery_notes">Delivery notes</label>
                                <textarea name="delivery_notes" id="delivery_notes" rows="2"
                                          class="form-control pf-input">{{ old('delivery_notes', $guest['delivery_notes'] ?? '') }}</textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-pf-primary w-100 btn-lg mt-4 mb-3">
                            Save &amp; Continue
                        </button>
                    </form>

                    <p class="text-center mb-0 pf-auth-footer-text">
                        Want saved addresses next time?
                        <a href="{{ route('register') }}">Create an account</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

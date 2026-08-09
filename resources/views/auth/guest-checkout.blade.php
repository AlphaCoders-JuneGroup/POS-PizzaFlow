@extends('layouts.app')

@section('title', 'Order without account — PizzaFlow')

@section('content')
<section class="pf-auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="pf-auth-card" data-aos="fade-up">
                    <div class="text-center mb-4">
                        <div class="pf-brand-icon mx-auto mb-3"><i class="bi bi-bag-check"></i></div>
                        <h1 class="pf-auth-title">Order without login</h1>
                        <p class="pf-auth-sub">Enter your details and place the order. It goes straight to Order Management &amp; Kitchen.</p>
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

                    <form method="POST" action="{{ route('guest.store') }}" id="guestOrderForm" novalidate>
                        @csrf
                        <input type="hidden" name="cart_data" id="guestCartData" value="">
                        <input type="hidden" name="promo_code" id="guestPromoCode" value="">

                        <div class="mb-3">
                            <label class="form-label">Order type</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="fulfillment_type" id="guestDelivery" value="delivery" checked>
                                    <label class="form-check-label" for="guestDelivery">Delivery</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="fulfillment_type" id="guestPickup" value="pickup">
                                    <label class="form-check-label" for="guestPickup">Pickup</label>
                                </div>
                            </div>
                        </div>

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
                            <div class="col-12" id="guestAddressFields">
                                <label class="form-label" for="address_line">Delivery address</label>
                                <input type="text" name="address_line" id="address_line" class="form-control pf-input"
                                       value="{{ old('address_line', $guest['address_line'] ?? '') }}" required>
                            </div>
                            <div class="col-md-6" id="guestCityField">
                                <label class="form-label" for="city">City</label>
                                <input type="text" name="city" id="city" class="form-control pf-input"
                                       value="{{ old('city', $guest['city'] ?? '') }}" required>
                            </div>
                            <div class="col-md-6" id="guestPostalField">
                                <label class="form-label" for="postal_code">Postal code</label>
                                <input type="text" name="postal_code" id="postal_code" class="form-control pf-input"
                                       value="{{ old('postal_code', $guest['postal_code'] ?? '') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="delivery_notes">Order notes</label>
                                <textarea name="delivery_notes" id="delivery_notes" rows="2"
                                          class="form-control pf-input" placeholder="Allergies, gate code, etc.">{{ old('delivery_notes', $guest['delivery_notes'] ?? '') }}</textarea>
                            </div>
                        </div>

                        <div class="alert alert-light border mt-3 mb-0 small" id="guestCartSummary">
                            Loading cart…
                        </div>

                        <button type="submit" class="btn btn-pf-primary w-100 btn-lg mt-4 mb-3" id="guestPlaceOrderBtn">
                            <i class="bi bi-bag-check-fill me-1"></i> Place Order
                        </button>
                    </form>

                    <p class="text-center mb-0 pf-auth-footer-text">
                        Prefer an account?
                        <a href="{{ route('login') }}">Login</a>
                        ·
                        <a href="{{ route('register') }}">Register</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cartField = document.getElementById('guestCartData');
    const promoField = document.getElementById('guestPromoCode');
    const summary = document.getElementById('guestCartSummary');
    const btn = document.getElementById('guestPlaceOrderBtn');
    const form = document.getElementById('guestOrderForm');

    let items = [];
    try { items = JSON.parse(localStorage.getItem('pizzaflow_cart_items') || '[]'); } catch (e) { items = []; }
    if (!Array.isArray(items)) items = [];

    if (cartField) cartField.value = JSON.stringify(items);
    if (promoField) promoField.value = (localStorage.getItem('pizzaflow_promo_code') || '').trim();

    if (summary) {
        if (!items.length) {
            summary.className = 'alert alert-warning mt-3 mb-0 small';
            summary.textContent = 'Your cart is empty. Add pizzas from the menu first, then come back here.';
            if (btn) btn.disabled = true;
        } else {
            const count = items.reduce((n, i) => n + (i.qty || 1), 0);
            const subtotal = items.reduce((n, i) => n + ((i.price || 0) * (i.qty || 1)), 0);
            summary.className = 'alert alert-light border mt-3 mb-0 small';
            summary.innerHTML = '<strong>' + count + ' item(s)</strong> in cart · Subtotal <strong>Rs. ' + subtotal.toLocaleString() + '</strong>';
        }
    }

    function toggleAddress() {
        const pickup = document.getElementById('guestPickup')?.checked;
        ['guestAddressFields', 'guestCityField', 'guestPostalField'].forEach((id) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.style.display = pickup ? 'none' : '';
            el.querySelectorAll('input').forEach((input) => {
                if (input.id === 'postal_code') return;
                input.required = !pickup;
                if (pickup) input.removeAttribute('required');
            });
        });
    }

    document.getElementById('guestDelivery')?.addEventListener('change', toggleAddress);
    document.getElementById('guestPickup')?.addEventListener('change', toggleAddress);
    toggleAddress();

    form?.addEventListener('submit', function () {
        try { items = JSON.parse(localStorage.getItem('pizzaflow_cart_items') || '[]'); } catch (e) { items = []; }
        if (cartField) cartField.value = JSON.stringify(items || []);
        if (promoField) promoField.value = (localStorage.getItem('pizzaflow_promo_code') || '').trim();
    });
});
</script>
@endpush

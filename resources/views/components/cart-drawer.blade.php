{{-- Shopping Cart Drawer --}}
<div class="pf-cart-drawer-overlay" id="cartDrawerOverlay"></div>
@php
    $canPlaceAsCustomer = auth()->check() && auth()->user()->isCustomer();
    $canPlaceAsGuest = ! auth()->check() && session('guest_checkout') && session('guest');
    $placeOrderUrl = $canPlaceAsCustomer
        ? route('orders.store')
        : ($canPlaceAsGuest ? route('guest.order') : '');
@endphp
<div class="pf-cart-drawer text-dark" id="cartDrawer"
     data-orders-url="{{ $placeOrderUrl }}"
     data-promo-url="{{ route('promotions.apply') }}"
     data-guest-url="{{ route('guest.create') }}"
     data-csrf="{{ csrf_token() }}">
    <div class="pf-cart-drawer-header d-flex justify-content-between align-items-center p-3 border-bottom border-light-subtle">
        <h4 class="mb-0 fs-5 font-poppins d-flex align-items-center gap-2">
            <i class="bi bi-cart3 text-pf-primary"></i>
            <span>Your Order</span>
        </h4>
        <button type="button" class="btn-close" id="closeCartDrawer" aria-label="Close cart"></button>
    </div>

    {{-- Cart Items --}}
    <div class="pf-cart-drawer-body p-3 flex-grow-1 overflow-y-auto" id="cartDrawerItems">
        {{-- Filled dynamically by JS --}}
        <div class="text-center text-muted py-5" id="cartEmptyState">
            <i class="bi bi-cart-x fs-1 d-block mb-3 text-muted"></i>
            <p>Your cart is empty.</p>
            <a href="#menu" class="btn btn-sm btn-pf-primary mt-2" id="cartStartShoppingBtn">Start Shopping</a>
        </div>
    </div>

    {{-- Cart Footer / Summary --}}
    <div class="pf-cart-drawer-footer p-3 border-top border-light-subtle bg-light d-none" id="cartDrawerFooter">
        <div class="mb-3">
            <label class="form-label small text-muted mb-1" for="cartPromoCode">Promo code</label>
            <div class="input-group input-group-sm">
                <input type="text" id="cartPromoCode" class="form-control pf-input"
                       placeholder="e.g. FLOW20" maxlength="40" autocomplete="off">
                <button type="button" class="btn btn-pf-outline" id="cartApplyPromoBtn">Apply</button>
            </div>
            <div class="small mt-1" id="cartPromoMessage"></div>
        </div>

        <div class="d-flex flex-column gap-2 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted small">Subtotal</span>
                <span class="fw-bold">Rs. <span id="cartSubtotal">0</span></span>
            </div>
            <div class="d-flex justify-content-between align-items-center d-none" id="cartDiscountRow">
                <span class="text-muted small">Discount <span id="cartPromoLabel"></span></span>
                <span class="fw-bold text-success">- Rs. <span id="cartDiscount">0</span></span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted small">Delivery Fee</span>
                <span class="fw-bold">Rs. <span id="cartDeliveryFee">0</span></span>
            </div>
            <div class="d-flex justify-content-between align-items-center border-top border-light-subtle pt-2 mt-1">
                <span class="fs-5 fw-bold font-poppins">Total</span>
                <span class="fs-4 fw-bold text-pf-primary font-poppins">Rs. <span id="cartTotal">0</span></span>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small text-muted mb-1">Payment Method</label>
            <div class="d-flex gap-3 small">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="cart_payment_method" id="cartPaymentCOD" value="Cash on Delivery" checked>
                    <label class="form-check-label" for="cartPaymentCOD">Cash on Delivery</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="cart_payment_method" id="cartPaymentCard" value="Pay Online (Card)">
                    <label class="form-check-label" for="cartPaymentCard">Pay Online (Card)</label>
                </div>
            </div>
        </div>

        @auth
            @if(auth()->user()->isCustomer())
                <button type="button" class="btn btn-pf-primary w-100 py-2 fw-semibold" id="cartPlaceOrderBtn">
                    <i class="bi bi-bag-check-fill me-1"></i> Place Order
                </button>
            @else
                <div class="alert alert-warning pf-alert mb-0 text-center py-2 fs-7">
                    Staff accounts cannot place orders. Login as <strong>customer@pizzaflow.com</strong>.
                </div>
            @endif
        @else
            <div class="d-flex flex-column gap-2">
                @if ($canPlaceAsGuest)
                    <button type="button" class="btn btn-pf-primary w-100 py-2 fw-semibold" id="cartPlaceOrderBtn">
                        <i class="bi bi-bag-check-fill me-1"></i> Place Order
                    </button>
                    <div class="text-center text-muted fs-8">
                        Ordering as guest: {{ session('guest.name') }}
                        · <a href="{{ route('guest.create') }}" class="text-pf-primary text-decoration-none">Edit details</a>
                    </div>
                @else
                    <button type="button" class="btn btn-pf-primary w-100 py-2 fw-semibold" id="cartGuestCheckoutBtn">
                        <i class="bi bi-bag-check-fill me-1"></i> Order without login
                    </button>
                    <div class="text-center text-muted fs-8">
                        No account needed · or <a href="{{ route('login') }}" class="text-pf-primary text-decoration-none">Login</a>
                    </div>
                @endif
            </div>
        @endauth
    </div>
</div>

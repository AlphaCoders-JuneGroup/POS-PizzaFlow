{{-- Shopping Cart Drawer --}}
<div class="pf-cart-drawer-overlay" id="cartDrawerOverlay"></div>
<div class="pf-cart-drawer text-dark" id="cartDrawer">
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
        <div class="d-flex flex-column gap-2 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted small">Subtotal</span>
                <span class="fw-bold">Rs. <span id="cartSubtotal">0</span></span>
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

        @auth
            @if(auth()->user()->isCustomer())
                <button type="button" class="btn btn-pf-primary w-100 py-2 fw-semibold" id="cartPlaceOrderBtn">
                    <i class="bi bi-bag-check-fill me-1"></i> Place Order
                </button>
            @else
                <div class="alert alert-warning pf-alert mb-0 text-center py-2 fs-7">
                    Staff accounts cannot place orders.
                </div>
            @endif
        @else
            <div class="d-flex flex-column gap-2">
                <button type="button" class="btn btn-outline-dark w-100 py-2 fw-semibold" id="cartGuestCheckoutBtn">
                    <i class="bi bi-person-walking me-1"></i> Checkout as Guest
                </button>
                <div class="text-center text-muted fs-8">
                    or <a href="{{ route('login') }}" class="text-pf-primary text-decoration-none">Login</a> to earn rewards
                </div>
            </div>
        @endauth
    </div>
</div>

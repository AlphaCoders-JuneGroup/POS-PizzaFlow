/**
 * PizzaFlow — Landing Page Scripts
 * Handles loader, AOS, smooth nav, cart UI, favorites, newsletter
 */

(function () {
    'use strict';

    const CART_KEY = 'pizzaflow_cart_count';
    const PROMO_CODE_KEY = 'pizzaflow_promo_code';
    let appliedPromoQuote = null;

    document.addEventListener('DOMContentLoaded', init);

    function init() {
        initLoader();

        try {
            initAOS();
            initNavbar();
            initSmoothScroll();
            initBackToTop();
            initFavorites();
            initCart();
            initNewsletter();
            initActiveNavOnScroll();
            initCategoryTabs();
            initPizzaCustomizer();
        } catch (err) {
            console.error('PizzaFlow init error:', err);
        }
    }

    /* ---------- Page Loader ---------- */
    function initLoader() {
        const loader = document.getElementById('page-loader');
        if (!loader) return;

        const hide = () => loader.classList.add('hidden');

        // Hide as soon as DOM is ready — do not wait for slow images/CDN.
        setTimeout(hide, 200);

        window.addEventListener('load', hide);

        // Hard fallback so the spinner never gets stuck
        setTimeout(hide, 1500);
    }

    /* ---------- AOS ---------- */
    function initAOS() {
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 800,
                easing: 'ease-out-cubic',
                once: true,
                offset: 60,
                disable: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
            });
        }
    }

    /* ---------- Navbar scroll state ---------- */
    function initNavbar() {
        const navbar = document.getElementById('mainNavbar');
        if (!navbar) return;

        const onScroll = () => {
            navbar.classList.toggle('scrolled', window.scrollY > 40);
        };

        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    /* ---------- Smooth scrolling for in-page anchors ---------- */
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
            anchor.addEventListener('click', (event) => {
                const href = anchor.getAttribute('href');
                if (!href || href === '#' || href.length < 2) return;

                const target = document.querySelector(href);
                if (!target) return;

                event.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });

                // Close mobile menu after tap
                const collapse = document.getElementById('navbarNav');
                if (collapse && collapse.classList.contains('show')) {
                    const toggler = document.querySelector('.navbar-toggler');
                    if (toggler) toggler.click();
                }
            });
        });
    }

    /* ---------- Active nav link while scrolling ---------- */
    function initActiveNavOnScroll() {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.pf-nav-links .nav-link');

        if (!sections.length || !navLinks.length) return;

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    const id = entry.target.getAttribute('id');
                    navLinks.forEach((link) => {
                        const href = link.getAttribute('href');
                        link.classList.toggle('active', href === `#${id}` || (id === 'categories' && href === '#menu'));
                    });
                });
            },
            { rootMargin: '-40% 0px -50% 0px', threshold: 0 }
        );

        sections.forEach((section) => observer.observe(section));
    }

    /* ---------- Back to top ---------- */
    function initBackToTop() {
        const btn = document.getElementById('back-to-top');
        if (!btn) return;

        window.addEventListener(
            'scroll',
            () => {
                btn.classList.toggle('visible', window.scrollY > 500);
            },
            { passive: true }
        );

        btn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ---------- Favorites (saved to account) ---------- */
    function initFavorites() {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;

        document.querySelectorAll('[data-favorite-toggle]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const url = btn.getAttribute('data-url');
                if (!url || !token) return;

                btn.disabled = true;

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (response.status === 401) {
                        window.location.href = '/login';
                        return;
                    }

                    const data = await response.json();
                    if (!response.ok) {
                        showToast(data.message || 'Could not update favorite');
                        return;
                    }

                    const icon = btn.querySelector('i');
                    btn.classList.toggle('active', data.favorited);
                    if (icon) {
                        icon.className = data.favorited ? 'bi bi-heart-fill' : 'bi bi-heart';
                    }
                    btn.setAttribute(
                        'aria-label',
                        data.favorited ? 'Remove from favorites' : 'Add to favorites'
                    );
                    showToast(data.message);
                } catch (error) {
                    showToast('Could not update favorite');
                } finally {
                    btn.disabled = false;
                }
            });
        });
    }

    /* ---------- Categories filter tabs ---------- */
    function initCategoryTabs() {
        const tabs = document.querySelectorAll('[data-tab-target]');
        const panels = document.querySelectorAll('.menu-tab-panel');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const target = tab.getAttribute('data-tab-target');

                // Toggle tabs active
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                // Toggle panels visibility
                panels.forEach(p => {
                    if (p.getAttribute('id') === `menu-panel-${target}`) {
                        p.classList.remove('d-none');
                        p.classList.add('active');
                    } else {
                        p.classList.add('d-none');
                        p.classList.remove('active');
                    }
                });
            });
        });

        // Category card triggers in categories section
        document.querySelectorAll('.pf-category-tab-trigger').forEach(trigger => {
            trigger.addEventListener('click', () => {
                const slug = trigger.getAttribute('data-category-slug');
                const targetTab = document.querySelector(`[data-tab-target="${slug}"]`);
                if (targetTab) {
                    targetTab.click();
                }
            });
        });
    }

    /* ---------- Pizza Customizer Modal ---------- */
    function initPizzaCustomizer() {
        const modalEl = document.getElementById('pizzaCustomizerModal');
        if (!modalEl) return;

        const modal = new bootstrap.Modal(modalEl);
        const form = document.getElementById('pizzaCustomizerForm');
        const totalPriceEl = document.getElementById('custTotalPrice');

        let currentPizza = null;

        // Recalculate price when checkbox changes
        document.querySelectorAll('.topping-checkbox').forEach(cb => {
            cb.addEventListener('change', calculatePrice);
        });

        // Attach listeners for other inputs to recalculate price
        form.addEventListener('change', calculatePrice);

        // Open customizer modal on click
        document.querySelectorAll('[data-customize-pizza]').forEach(btn => {
            btn.addEventListener('click', () => {
                currentPizza = {
                    id: btn.getAttribute('data-id'),
                    name: btn.getAttribute('data-name'),
                    slug: btn.getAttribute('data-slug'),
                    image: btn.getAttribute('data-image'),
                    price: parseInt(btn.getAttribute('data-price'), 10)
                };

                // Reset form
                form.reset();
                document.querySelectorAll('.pf-topping-controls').forEach(el => el.classList.add('d-none'));

                // Set preview info
                document.getElementById('custPizzaId').value = currentPizza.id;
                document.getElementById('custPizzaSlug').value = currentPizza.slug;
                document.getElementById('custPizzaName').textContent = currentPizza.name;
                document.getElementById('custPizzaDesc').textContent = btn.closest('.pf-pizza-card')?.querySelector('.pf-pizza-desc')?.textContent || '';
                document.getElementById('custPizzaImage').src = currentPizza.image;
                document.getElementById('custBasePrice').textContent = currentPizza.price.toLocaleString();

                calculatePrice();
                modal.show();
            });
        });

        function calculatePrice() {
            if (!currentPizza) return;

            let total = currentPizza.price;

            // 1. Size modifier
            const selectedSize = form.querySelector('input[name="pizza_size"]:checked');
            if (selectedSize) {
                total += parseInt(selectedSize.getAttribute('data-price') || '0', 10);
            }

            // 2. Crust modifier
            const selectedCrust = form.querySelector('input[name="pizza_crust"]:checked');
            if (selectedCrust) {
                total += parseInt(selectedCrust.getAttribute('data-price') || '0', 10);
            }

            // 3. Sauce modifier
            const selectedSauce = form.querySelector('select[name="pizza_sauce"] option:checked');
            if (selectedSauce) {
                total += parseInt(selectedSauce.getAttribute('data-price') || '0', 10);
            }

            // 4. Toppings pricing
            document.querySelectorAll('.topping-checkbox:checked').forEach(cb => {
                total += parseInt(cb.getAttribute('data-price') || '0', 10);
            });

            totalPriceEl.textContent = total.toLocaleString();
        }

        // Add custom pizza to cart
        document.getElementById('custAddToCartBtn')?.addEventListener('click', () => {
            if (!currentPizza) return;

            const selectedSize = form.querySelector('input[name="pizza_size"]:checked')?.value || 'Personal (Small)';
            const selectedCrust = form.querySelector('input[name="pizza_crust"]:checked')?.value || 'Classic Thin';
            const selectedSauce = form.querySelector('select[name="pizza_sauce"]')?.value || 'Marinara Classic';

            // Collect chosen toppings
            const chosenToppings = [];
            document.querySelectorAll('.topping-checkbox:checked').forEach(cb => {
                chosenToppings.push({
                    name: cb.value
                });
            });

            const finalPrice = parseInt(totalPriceEl.textContent.replace(/,/g, ''), 10);

            // Add item to cart array
            const cartItem = {
                id: currentPizza.slug + '-' + Date.now(),
                name: currentPizza.name,
                slug: currentPizza.slug,
                image: currentPizza.image,
                type: 'pizza',
                size: selectedSize,
                crust: selectedCrust,
                sauce: selectedSauce,
                toppings: chosenToppings,
                price: finalPrice,
                qty: 1
            };

            addCartItem(cartItem);
            modal.hide();
            showToast(`${currentPizza.name} added to cart`);
            openCartDrawer();
        });
    }

    /* ---------- Cart ---------- */
    const CART_ITEMS_KEY = 'pizzaflow_cart_items';

    function initCart() {
        updateCartBadge();

        // 1. Simple add to cart (non-customizable items)
        document.querySelectorAll('[data-add-simple-cart]').forEach(btn => {
            btn.addEventListener('click', () => {
                const item = {
                    id: btn.getAttribute('data-id') + '-' + Date.now(),
                    name: btn.getAttribute('data-name'),
                    price: parseInt(btn.getAttribute('data-price'), 10),
                    image: btn.getAttribute('data-image'),
                    type: 'simple',
                    qty: 1
                };

                addCartItem(item);
                showToast(`${item.name} added to cart`);

                btn.classList.add('added');
                setTimeout(() => btn.classList.remove('added'), 400);
                openCartDrawer();
            });
        });

        // 2. Cart Drawer Toggles
        const drawer = document.getElementById('cartDrawer');
        const overlay = document.getElementById('cartDrawerOverlay');
        const trigger = document.getElementById('cartDrawerTrigger');
        const closeBtn = document.getElementById('closeCartDrawer');
        const startShopping = document.getElementById('cartStartShoppingBtn');

        if (trigger && drawer && overlay) {
            trigger.addEventListener('click', openCartDrawer);
            closeBtn?.addEventListener('click', closeCartDrawer);
            overlay.addEventListener('click', closeCartDrawer);
            startShopping?.addEventListener('click', closeCartDrawer);
        }

        // 3. Checkout / Place Order
        const placeOrderBtn = document.getElementById('cartPlaceOrderBtn');
        const guestCheckoutBtn = document.getElementById('cartGuestCheckoutBtn');
        const applyPromoBtn = document.getElementById('cartApplyPromoBtn');
        const promoInput = document.getElementById('cartPromoCode');

        placeOrderBtn?.addEventListener('click', () => submitCartOrder(drawer));
        guestCheckoutBtn?.addEventListener('click', () => {
            const guestUrl = drawer?.dataset.guestUrl;
            if (guestUrl) {
                window.location.href = guestUrl;
                return;
            }
            showToast('Please login or continue as guest.');
        });

        applyPromoBtn?.addEventListener('click', () => applyPromoCode());
        promoInput?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyPromoCode();
            }
        });

        // Prefill + auto-apply previously claimed offer codes
        const claimed = localStorage.getItem(PROMO_CODE_KEY);
        if (claimed && promoInput) {
            promoInput.value = claimed;
            if (getCartItems().length > 0) {
                applyPromoCode();
            }
        }

        document.querySelectorAll('[data-claim-promo]').forEach((btn) => {
            btn.addEventListener('click', async (event) => {
                event.preventDefault();
                const code = (btn.getAttribute('data-claim-promo') || '').trim().toUpperCase();
                if (!code) return;

                localStorage.setItem(PROMO_CODE_KEY, code);
                if (promoInput) promoInput.value = code;

                document.querySelectorAll('[data-claim-promo]').forEach((el) => {
                    el.classList.remove('is-claimed');
                    if (el.dataset.originalLabel) {
                        el.textContent = el.dataset.originalLabel;
                    }
                });
                if (!btn.dataset.originalLabel) {
                    btn.dataset.originalLabel = btn.textContent.trim();
                }
                btn.classList.add('is-claimed');
                btn.textContent = 'Claimed ✓';

                openCartDrawer();

                const items = getCartItems();
                if (items.length === 0) {
                    showToast(`${code} claimed! Add pizzas, then checkout — discount applies.`);
                    const msgEl = document.getElementById('cartPromoMessage');
                    if (msgEl) {
                        msgEl.textContent = `${code} ready. Add items to see your discount.`;
                        msgEl.className = 'small mt-1 text-success';
                    }
                    return;
                }

                await applyPromoCode();
                showToast(`${code} claimed and applied to your order.`);
            });
        });
    }

    function cartSubtotal(items) {
        return items.reduce((sum, item) => sum + (item.price * item.qty), 0);
    }

    async function applyPromoCode() {
        const drawer = document.getElementById('cartDrawer');
        const promoInput = document.getElementById('cartPromoCode');
        const msgEl = document.getElementById('cartPromoMessage');
        const promoUrl = drawer?.dataset.promoUrl;
        if (!promoUrl || !promoInput) return;

        const items = getCartItems();
        const subtotal = cartSubtotal(items);
        const code = promoInput.value.trim().toUpperCase();

        if (!code) {
            appliedPromoQuote = null;
            localStorage.removeItem(PROMO_CODE_KEY);
            if (msgEl) {
                msgEl.textContent = 'Promo cleared.';
                msgEl.className = 'small mt-1 text-muted';
            }
            renderCartDrawer();
            return;
        }

        try {
            const res = await fetch(promoUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': drawer.dataset.csrf || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ code, subtotal }),
            });
            const data = await res.json();

            if (!data.applied) {
                appliedPromoQuote = null;
                localStorage.removeItem(PROMO_CODE_KEY);
                if (msgEl) {
                    msgEl.textContent = data.error || 'Invalid promo code.';
                    msgEl.className = 'small mt-1 text-danger';
                }
                renderCartDrawer();
                return;
            }

            appliedPromoQuote = data.quote;
            localStorage.setItem(PROMO_CODE_KEY, code);
            if (msgEl) {
                msgEl.textContent = data.quote.message || 'Promo applied.';
                msgEl.className = 'small mt-1 text-success';
            }
            renderCartDrawer();
        } catch (e) {
            if (msgEl) {
                msgEl.textContent = 'Could not apply promo. Try again.';
                msgEl.className = 'small mt-1 text-danger';
            }
        }
    }

    function submitCartOrder(drawer) {
        const ordersUrl = drawer?.dataset.ordersUrl;
        if (!ordersUrl) {
            showToast('Login as customer, or complete Guest Checkout first.');
            return;
        }

        const items = getCartItems();
        if (!items.length) {
            showToast('Your cart is empty.');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = ordersUrl;
        form.style.display = 'none';

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = drawer.dataset.csrf || '';
        form.appendChild(csrf);

        const cartField = document.createElement('input');
        cartField.type = 'hidden';
        cartField.name = 'cart_data';
        cartField.value = JSON.stringify(items);
        form.appendChild(cartField);

        const promoField = document.createElement('input');
        promoField.type = 'hidden';
        promoField.name = 'promo_code';
        promoField.value = (document.getElementById('cartPromoCode')?.value || localStorage.getItem(PROMO_CODE_KEY) || '').trim();
        form.appendChild(promoField);

        document.body.appendChild(form);
        form.submit();
    }

    function getCartItems() {
        try {
            return JSON.parse(localStorage.getItem(CART_ITEMS_KEY) || '[]');
        } catch (e) {
            return [];
        }
    }

    function saveCartItems(items) {
        localStorage.setItem(CART_ITEMS_KEY, JSON.stringify(items));
        updateCartBadge();
        renderCartDrawer();
    }

    function addCartItem(newItem) {
        const items = getCartItems();
        
        if (newItem.type === 'simple') {
            const existing = items.find(i => i.type === 'simple' && i.name === newItem.name);
            if (existing) {
                existing.qty += 1;
                saveCartItems(items);
                return;
            }
        }
        
        items.push(newItem);
        saveCartItems(items);
    }

    function updateCartBadge() {
        const badge = document.getElementById('cartCount');
        if (!badge) return;
        
        const items = getCartItems();
        const count = items.reduce((sum, item) => sum + (item.qty || 0), 0);
        
        badge.textContent = String(count);
        localStorage.setItem(CART_KEY, String(count)); // Keep sync with legacy counter if needed
    }

    function openCartDrawer() {
        const drawer = document.getElementById('cartDrawer');
        const overlay = document.getElementById('cartDrawerOverlay');
        if (drawer && overlay) {
            renderCartDrawer();
            drawer.classList.add('open');
            overlay.classList.add('show');
        }
    }

    function closeCartDrawer() {
        const drawer = document.getElementById('cartDrawer');
        const overlay = document.getElementById('cartDrawerOverlay');
        if (drawer && overlay) {
            drawer.classList.remove('open');
            overlay.classList.remove('show');
        }
    }

    function renderCartDrawer() {
        const container = document.getElementById('cartDrawerItems');
        const footer = document.getElementById('cartDrawerFooter');
        const emptyState = document.getElementById('cartEmptyState');
        const subtotalEl = document.getElementById('cartSubtotal');
        const deliveryEl = document.getElementById('cartDeliveryFee');
        const totalEl = document.getElementById('cartTotal');

        if (!container) return;

        const items = getCartItems();

        if (items.length === 0) {
            emptyState?.classList.remove('d-none');
            footer?.classList.add('d-none');
            // Remove previous items from view
            container.querySelectorAll('.pf-cart-item-card').forEach(el => el.remove());
            return;
        }

        emptyState?.classList.add('d-none');
        footer?.classList.remove('d-none');

        // Remove previous items
        container.querySelectorAll('.pf-cart-item-card').forEach(el => el.remove());

        let subtotal = 0;

        items.forEach((item, index) => {
            subtotal += item.price * item.qty;

            const itemCard = document.createElement('div');
            itemCard.className = 'pf-cart-item-card d-flex gap-2 p-2 rounded mb-2 border border-light-subtle';
            
            // Build customizations label
            let customizationText = '';
            if (item.type === 'pizza') {
                const parts = [item.size, item.crust];
                if (item.toppings && item.toppings.length > 0) {
                    const topList = item.toppings.map(t => `${t.name} (${t.portion === 'Regular' ? '' : t.portion + ' '}${t.side === 'Whole' ? '' : t.side + ' half'})`).join(', ');
                    parts.push(topList);
                }
                customizationText = `<div class="text-muted fs-8 mt-1">${parts.join(' | ')}</div>`;
            }

            itemCard.innerHTML = `
                <img src="${item.image || 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=600&q=80'}" alt="${item.name}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <strong class="fs-7 text-dark">${item.name}</strong>
                        <span class="fs-7 fw-bold text-pf-primary">Rs. ${(item.price * item.qty).toLocaleString()}</span>
                    </div>
                    ${customizationText}
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-dark py-0 px-2 btn-qty-minus" data-index="${index}">-</button>
                            <span class="btn border-dark-subtle border-start-0 border-end-0 py-0 px-3 disabled text-dark bg-transparent">${item.qty}</span>
                            <button type="button" class="btn btn-outline-dark py-0 px-2 btn-qty-plus" data-index="${index}">+</button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger border-0 py-0 px-2 btn-remove-item" data-index="${index}">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(itemCard);
        });

        const discountRow = document.getElementById('cartDiscountRow');
        const discountEl = document.getElementById('cartDiscount');
        const promoLabel = document.getElementById('cartPromoLabel');
        const promoInput = document.getElementById('cartPromoCode');

        // Prefer server quote when a promo is applied; otherwise local defaults.
        let deliveryFee = 250;
        let discount = 0;
        let total = subtotal + deliveryFee;

        if (appliedPromoQuote && appliedPromoQuote.subtotal === subtotal) {
            deliveryFee = appliedPromoQuote.delivery_fee;
            discount = appliedPromoQuote.discount || 0;
            total = appliedPromoQuote.total;
            if (promoInput && appliedPromoQuote.promo_code) {
                promoInput.value = appliedPromoQuote.promo_code;
            }
        } else if (appliedPromoQuote && appliedPromoQuote.promo_code && !window.__pfPromoRefreshing) {
            // Cart changed after apply — re-quote asynchronously once
            window.__pfPromoRefreshing = true;
            applyPromoCode().finally(() => { window.__pfPromoRefreshing = false; });
        } else {
            // Auto free delivery threshold fallback (matches seeded free-delivery promo)
            if (subtotal >= 5000) {
                deliveryFee = 0;
                total = subtotal;
            }
        }

        if (subtotalEl) subtotalEl.textContent = subtotal.toLocaleString();
        if (deliveryEl) deliveryEl.textContent = deliveryFee === 0 ? 'FREE' : deliveryFee.toLocaleString();
        if (totalEl) totalEl.textContent = total.toLocaleString();

        if (discountRow && discountEl) {
            if (discount > 0) {
                discountRow.classList.remove('d-none');
                discountEl.textContent = discount.toLocaleString();
                if (promoLabel) {
                    promoLabel.textContent = appliedPromoQuote?.promo_code
                        ? `(${appliedPromoQuote.promo_code})`
                        : '';
                }
            } else {
                discountRow.classList.add('d-none');
            }
        }

        // Setup quantity and remove event listeners
        container.querySelectorAll('.btn-qty-plus').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.getAttribute('data-index'), 10);
                items[idx].qty += 1;
                saveCartItems(items);
            });
        });

        container.querySelectorAll('.btn-qty-minus').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.getAttribute('data-index'), 10);
                if (items[idx].qty > 1) {
                    items[idx].qty -= 1;
                } else {
                    items.splice(idx, 1);
                }
                saveCartItems(items);
            });
        });

        container.querySelectorAll('.btn-remove-item').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.getAttribute('data-index'), 10);
                items.splice(idx, 1);
                saveCartItems(items);
            });
        });
    }

    function showToast(message) {
        let toast = document.querySelector('.pf-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.className = 'pf-toast';
            toast.setAttribute('role', 'status');
            document.body.appendChild(toast);
        }

        toast.textContent = message;
        toast.classList.add('show');
        clearTimeout(showToast._timer);
        showToast._timer = setTimeout(() => toast.classList.remove('show'), 2200);
    }

    /* ---------- Newsletter ---------- */
    function initNewsletter() {
        const form = document.getElementById('newsletterForm');
        const success = document.getElementById('newsletterSuccess');
        if (!form) return;

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            const email = form.querySelector('input[type="email"]');
            if (!email || !email.checkValidity()) {
                email?.reportValidity();
                return;
            }

            if (success) success.classList.remove('d-none');
            form.reset();
            showToast('Subscribed successfully!');
        });
    }
})();

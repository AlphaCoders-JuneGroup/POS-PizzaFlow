/**
 * PizzaFlow — Landing Page Scripts
 * Handles loader, AOS, smooth nav, cart UI, favorites, newsletter
 */

(function () {
    'use strict';

    const CART_KEY = 'pizzaflow_cart_count';

    document.addEventListener('DOMContentLoaded', init);

    function init() {
        initLoader();
        initAOS();
        initNavbar();
        initSmoothScroll();
        initBackToTop();
        initFavorites();
        initCart();
        initNewsletter();
        initActiveNavOnScroll();
    }

    /* ---------- Page Loader ---------- */
    function initLoader() {
        const loader = document.getElementById('page-loader');
        if (!loader) return;

        window.addEventListener('load', () => {
            setTimeout(() => loader.classList.add('hidden'), 350);
        });

        // Fallback if load already fired
        if (document.readyState === 'complete') {
            setTimeout(() => loader.classList.add('hidden'), 350);
        }
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

    /* ---------- Cart ---------- */
    function initCart() {
        const badge = document.getElementById('cartCount');
        let count = parseInt(localStorage.getItem(CART_KEY) || '0', 10);
        updateBadge(badge, count);

        document.querySelectorAll('[data-add-cart]').forEach((btn) => {
            btn.addEventListener('click', () => {
                count += 1;
                localStorage.setItem(CART_KEY, String(count));
                updateBadge(badge, count);

                const name = btn.getAttribute('data-name') || 'Pizza';
                showToast(`${name} added to cart`);

                btn.classList.add('added');
                setTimeout(() => btn.classList.remove('added'), 400);
            });
        });
    }

    function updateBadge(badge, count) {
        if (!badge) return;
        badge.textContent = String(count);
        badge.style.display = count > 0 ? 'grid' : 'grid';
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

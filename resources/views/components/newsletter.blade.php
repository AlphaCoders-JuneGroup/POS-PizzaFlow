{{-- Newsletter Section --}}
<section class="pf-section pf-newsletter" id="newsletter">
    <div class="container">
        <div class="pf-newsletter-card" data-aos="fade-up">
            <div class="row align-items-center g-4">
                <div class="col-lg-5">
                    <span class="pf-eyebrow text-white-50">Stay Hungry</span>
                    <h2 class="pf-newsletter-title">Get exclusive offers in your inbox</h2>
                    <p class="pf-newsletter-text">
                        Subscribe for weekly deals, new menu drops, and early access to specials.
                    </p>
                </div>
                <div class="col-lg-7">
                    <form class="pf-newsletter-form" id="newsletterForm" action="#" method="post" novalidate>
                        @csrf
                        <div class="input-group">
                            <input type="email"
                                   name="email"
                                   class="form-control pf-newsletter-input"
                                   placeholder="Enter your email address"
                                   aria-label="Email address"
                                   required>
                            <button type="submit" class="btn btn-pf-accent">
                                Subscribe
                                <i class="bi bi-send ms-1"></i>
                            </button>
                        </div>
                        <p class="pf-newsletter-hint mt-2 mb-0">No spam. Unsubscribe anytime.</p>
                        <div class="pf-newsletter-success d-none" id="newsletterSuccess" role="status">
                            <i class="bi bi-check-circle-fill"></i> Thanks! You're on the list.
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

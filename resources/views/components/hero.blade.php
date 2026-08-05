{{-- Hero Section --}}
<section class="pf-hero" id="home">
    <div class="pf-hero-bg" aria-hidden="true"></div>
    <div class="pf-hero-overlay" aria-hidden="true"></div>

    <div class="container position-relative">
        <div class="row align-items-center min-vh-hero">
            {{-- Copy --}}
            <div class="col-lg-6" data-aos="fade-right" data-aos-duration="900">
                <h1 class="pf-hero-title">
                    Fresh Pizza Delivered<br>
                    <span>Hot &amp; Fast</span>
                </h1>
                <p class="pf-hero-subtitle">
                    Customize your favorite pizza and enjoy fast doorstep delivery.
                </p>

                <div class="pf-hero-ctas d-flex flex-wrap gap-3 mb-4">
                    <a href="#menu" class="btn btn-pf-primary btn-lg">
                        <i class="bi bi-bag-check me-2"></i>Order Now
                    </a>
                    <a href="#menu" class="btn btn-pf-glass btn-lg">
                        <i class="bi bi-grid me-2"></i>Explore Menu
                    </a>
                </div>

                {{-- Animated Food Badges --}}
                <div class="pf-hero-badges d-flex flex-wrap gap-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="pf-badge-pill">
                        <span>⭐</span> Fresh Ingredients
                    </div>
                    <div class="pf-badge-pill">
                        <span>🚚</span> 30 Min Delivery
                    </div>
                    <div class="pf-badge-pill">
                        <span>🍕</span> Best Quality
                    </div>
                </div>
            </div>

            {{-- Floating Pizza Image --}}
            <div class="col-lg-6 text-center mt-5 mt-lg-0" data-aos="fade-left" data-aos-duration="1000">
                <div class="pf-hero-image-wrap">
                    <img src="https://images.unsplash.com/photo-1513104890138-7c749659a591?w=700&q=85"
                         alt="Delicious fresh pizza from PizzaFlow"
                         class="pf-hero-pizza img-fluid"
                         width="520" height="520"
                         loading="eager">
                    <div class="pf-float-badge pf-float-1">
                        <i class="bi bi-lightning-charge-fill"></i>
                        <span>Hot &amp; Fresh</span>
                    </div>
                    <div class="pf-float-badge pf-float-2">
                        <i class="bi bi-star-fill"></i>
                        <span>4.9 Rating</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scroll Cue --}}
    <a href="#categories" class="pf-scroll-cue" aria-label="Scroll to categories">
        <i class="bi bi-chevron-down"></i>
    </a>
</section>

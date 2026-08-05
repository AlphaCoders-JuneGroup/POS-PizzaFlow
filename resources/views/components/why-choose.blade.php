{{-- Why Choose PizzaFlow --}}
<section class="pf-section pf-why" id="about">
    <div class="container">
        <div class="pf-section-header text-center" data-aos="fade-up">
            <span class="pf-eyebrow">Our Promise</span>
            <h2 class="pf-section-title">Why Choose PizzaFlow</h2>
            <p class="pf-section-sub">Everything you need for a seamless pizza experience.</p>
        </div>

        <div class="row g-4">
            @php
                $features = [
                    ['icon' => 'bi-truck', 'emoji' => '🚚', 'title' => 'Fast Delivery', 'text' => 'Hot pizza at your door in 30 minutes or less, guaranteed.'],
                    ['icon' => 'bi-egg-fried', 'emoji' => '🍅', 'title' => 'Fresh Ingredients', 'text' => 'Locally sourced produce and authentic Italian cheeses.'],
                    ['icon' => 'bi-person-badge', 'emoji' => '👨‍🍳', 'title' => 'Expert Chefs', 'text' => 'Crafted by chefs who live and breathe Neapolitan tradition.'],
                    ['icon' => 'bi-shield-lock', 'emoji' => '💳', 'title' => 'Secure Payments', 'text' => 'Encrypted checkout with multiple trusted payment options.'],
                    ['icon' => 'bi-geo-alt', 'emoji' => '📍', 'title' => 'Live Order Tracking', 'text' => 'Follow your order from the oven to your doorstep in real time.'],
                    ['icon' => 'bi-emoji-smile', 'emoji' => '⭐', 'title' => 'Customer Satisfaction', 'text' => 'Thousands of happy customers and a 4.9 average rating.'],
                ];
            @endphp

            @foreach ($features as $index => $feature)
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $index * 80 }}">
                    <div class="pf-feature-card">
                        <div class="pf-feature-icon" aria-hidden="true">
                            <span class="pf-feature-emoji">{{ $feature['emoji'] }}</span>
                            <i class="bi {{ $feature['icon'] }}"></i>
                        </div>
                        <h3 class="pf-feature-title">{{ $feature['title'] }}</h3>
                        <p class="pf-feature-text">{{ $feature['text'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

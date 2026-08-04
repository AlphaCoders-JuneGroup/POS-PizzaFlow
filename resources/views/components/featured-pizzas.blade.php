{{-- Featured Pizzas --}}
<section class="pf-section pf-menu" id="menu">
    <div class="container">
        <div class="pf-section-header text-center" data-aos="fade-up">
            <span class="pf-eyebrow">Our Favorites</span>
            <h2 class="pf-section-title">Featured Pizzas</h2>
            <p class="pf-section-sub">Handcrafted with love — try our most loved creations.</p>
        </div>

        <div class="row g-4">
            @foreach ($featuredPizzas as $index => $pizza)
                <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="{{ ($index % 4) * 80 }}">
                    <article class="pf-pizza-card">
                        <div class="pf-pizza-image-wrap">
                            <img src="{{ $pizza['image'] }}"
                                 alt="{{ $pizza['name'] }}"
                                 class="pf-pizza-image"
                                 width="400" height="280"
                                 loading="lazy">
                            <button type="button"
                                    class="pf-fav-btn"
                                    aria-label="Add {{ $pizza['name'] }} to favorites"
                                    data-favorite>
                                <i class="bi bi-heart"></i>
                            </button>
                            <div class="pf-rating-badge">
                                <i class="bi bi-star-fill"></i>
                                {{ number_format($pizza['rating'], 1) }}
                            </div>
                        </div>

                        <div class="pf-pizza-body">
                            <h3 class="pf-pizza-name">{{ $pizza['name'] }}</h3>
                            <p class="pf-pizza-desc">{{ $pizza['description'] }}</p>

                            <div class="pf-pizza-footer">
                                <span class="pf-pizza-price">
                                    Rs. {{ number_format($pizza['price']) }}
                                </span>
                                <button type="button"
                                        class="btn btn-pf-primary btn-sm pf-add-cart"
                                        data-add-cart
                                        data-name="{{ $pizza['name'] }}"
                                        data-price="{{ $pizza['price'] }}">
                                    <i class="bi bi-cart-plus"></i>
                                    <span class="d-none d-xl-inline ms-1">Add</span>
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>

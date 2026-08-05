{{-- Dynamic Menu & Customization --}}
<section class="pf-section pf-menu" id="menu">
    <div class="container">
        <div class="pf-section-header text-center" data-aos="fade-up">
            <span class="pf-eyebrow">Explore Our Menu</span>
            <h2 class="pf-section-title">Delicious Offerings</h2>
            <p class="pf-section-sub">Handcrafted pizzas, sides, cold beverages, and freshly baked desserts.</p>
        </div>

        {{-- Category Filter Tabs --}}
        <div class="d-flex justify-content-center flex-wrap gap-2 mb-5" data-aos="fade-up">
            @foreach ($categories as $index => $cat)
                <button class="btn btn-pf-category-tab {{ $index === 0 ? 'active' : '' }}" data-tab-target="{{ $cat->slug }}">
                    <span class="me-1"><i class="bi {{ $cat->icon }}"></i></span> {{ $cat->name }}
                </button>
            @endforeach
        </div>

        {{-- Menu Items Panels --}}
        <div class="menu-tab-contents">
            @foreach ($categories as $catIndex => $cat)
                <div class="menu-tab-panel row g-4 {{ $catIndex === 0 ? 'active' : 'd-none' }}" id="menu-panel-{{ $cat->slug }}">
                    @forelse ($menuItems->where('category_id', $cat->_id) as $index => $item)
                        @php $isFavorite = in_array($item->slug, $favoriteSlugs ?? [], true); @endphp
                        <div class="col-12 col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="{{ ($index % 4) * 80 }}">
                            <article class="pf-pizza-card">
                                <div class="pf-pizza-image-wrap">
                                    <img src="{{ $item->image }}"
                                         alt="{{ $item->name }}"
                                         class="pf-pizza-image"
                                         width="400" height="280"
                                         loading="lazy">

                                    @if ($item->is_customizable)
                                        @auth
                                            <button type="button"
                                                    class="pf-fav-btn {{ $isFavorite ? 'active' : '' }}"
                                                    aria-label="{{ $isFavorite ? 'Remove from favorites' : 'Add to favorites' }}"
                                                    data-favorite-toggle
                                                    data-slug="{{ $item->slug }}"
                                                    data-url="{{ route('favorites.toggle', $item->slug) }}">
                                                <i class="bi {{ $isFavorite ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                            </button>
                                        @else
                                            <a href="{{ route('login') }}"
                                               class="pf-fav-btn"
                                               aria-label="Login to save favorites"
                                               title="Login to save favorites">
                                                <i class="bi bi-heart"></i>
                                            </a>
                                        @endauth
                                    @endif

                                    <div class="pf-rating-badge">
                                        <i class="bi bi-star-fill"></i>
                                        {{ number_format($item->rating, 1) }}
                                    </div>
                                </div>

                                <div class="pf-pizza-body d-flex flex-column justify-content-between h-100">
                                    <div>
                                        <h3 class="pf-pizza-name">{{ $item->name }}</h3>
                                        <p class="pf-pizza-desc">{{ $item->description }}</p>
                                    </div>

                                    <div class="pf-pizza-footer mt-3">
                                        <span class="pf-pizza-price">
                                            Rs. {{ number_format($item->price) }}
                                        </span>

                                        @if ($item->is_customizable)
                                            <button type="button"
                                                    class="btn btn-pf-primary btn-sm pf-customize-btn"
                                                    data-customize-pizza
                                                    data-id="{{ $item->_id }}"
                                                    data-name="{{ $item->name }}"
                                                    data-slug="{{ $item->slug }}"
                                                    data-price="{{ $item->price }}"
                                                    data-image="{{ $item->image }}">
                                                <i class="bi bi-sliders2-vertical"></i>
                                                <span class="ms-1">Customize</span>
                                            </button>
                                        @else
                                            <button type="button"
                                                    class="btn btn-pf-primary btn-sm pf-add-cart-simple"
                                                    data-add-simple-cart
                                                    data-id="{{ $item->_id }}"
                                                    data-name="{{ $item->name }}"
                                                    data-price="{{ $item->price }}"
                                                    data-image="{{ $item->image }}">
                                                <i class="bi bi-cart-plus"></i>
                                                <span class="ms-1">Add</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted py-5" data-aos="fade-up">
                            <div class="fs-1">🍽️</div>
                            <h4 class="mt-3">No items available</h4>
                            <p class="text-white-50">Please check back later.</p>
                        </div>
                    @endforelse
                </div>
            @endforeach
        </div>
    </div>
</section>

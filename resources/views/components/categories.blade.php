{{-- Featured Categories --}}
<section class="pf-section pf-categories" id="categories">
    <div class="container">
        <div class="pf-section-header text-center" data-aos="fade-up">
            <span class="pf-eyebrow">Browse Menu</span>
            <h2 class="pf-section-title">Featured Categories</h2>
            <p class="pf-section-sub">Pick a category and start building your perfect order.</p>
        </div>

        <div class="row g-4 justify-content-center">
            @foreach ($categories as $index => $category)
                <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="{{ $index * 100 }}">
                    <a href="#menu" class="pf-category-card text-decoration-none pf-category-tab-trigger" data-category-slug="{{ $category->slug }}">
                        <div class="pf-category-icon"><i class="bi {{ $category->icon }}"></i></div>
                        <h3 class="pf-category-name">{{ $category->name }}</h3>
                        <p class="pf-category-count">Fresh Selection</p>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Featured Categories --}}
<section class="pf-section pf-categories" id="categories">
    <div class="container">
        <div class="pf-section-header text-center" data-aos="fade-up">
            <span class="pf-eyebrow">Browse Menu</span>
            <h2 class="pf-section-title">Featured Categories</h2>
            <p class="pf-section-sub">Pick a category and start building your perfect order.</p>
        </div>

        <div class="row g-4">
            @php
                $categories = [
                    ['icon' => '🍕', 'name' => 'Pizza', 'count' => '48+ items', 'href' => '#menu'],
                    ['icon' => '🥤', 'name' => 'Drinks', 'count' => '20+ items', 'href' => '#menu'],
                    ['icon' => '🍟', 'name' => 'Sides', 'count' => '16+ items', 'href' => '#menu'],
                    ['icon' => '🍰', 'name' => 'Desserts', 'count' => '12+ items', 'href' => '#menu'],
                ];
            @endphp

            @foreach ($categories as $index => $category)
                <div class="col-6 col-md-3" data-aos="zoom-in" data-aos-delay="{{ $index * 100 }}">
                    <a href="{{ $category['href'] }}" class="pf-category-card text-decoration-none">
                        <div class="pf-category-icon">{{ $category['icon'] }}</div>
                        <h3 class="pf-category-name">{{ $category['name'] }}</h3>
                        <p class="pf-category-count">{{ $category['count'] }}</p>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

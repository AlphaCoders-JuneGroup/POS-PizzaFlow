{{-- Customer Testimonials Carousel --}}
<section class="pf-section pf-testimonials" id="testimonials">
    <div class="container">
        <div class="pf-section-header text-center" data-aos="fade-up">
            <span class="pf-eyebrow">Reviews</span>
            <h2 class="pf-section-title">What Our Customers Say</h2>
            <p class="pf-section-sub">Real stories from pizza lovers across Sri Lanka.</p>
        </div>

        <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5500" data-aos="fade-up">
            <div class="carousel-inner">
                @foreach ($testimonials as $index => $review)
                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <div class="pf-testimonial-card mx-auto">
                            <img src="{{ $review['photo'] }}"
                                 alt="{{ $review['name'] }}"
                                 class="pf-testimonial-photo"
                                 width="80" height="80"
                                 loading="lazy">

                            <div class="pf-testimonial-stars" aria-label="{{ $review['rating'] }} out of 5 stars">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $review['rating'] ? '-fill' : '' }}"></i>
                                @endfor
                            </div>

                            <blockquote class="pf-testimonial-quote">
                                “{{ $review['comment'] }}”
                            </blockquote>

                            <h3 class="pf-testimonial-name">{{ $review['name'] }}</h3>
                            <p class="pf-testimonial-role">Verified Customer</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
                <span class="pf-carousel-btn" aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
                <span class="pf-carousel-btn" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
                <span class="visually-hidden">Next</span>
            </button>

            <div class="carousel-indicators pf-carousel-dots">
                @foreach ($testimonials as $index => $review)
                    <button type="button"
                            data-bs-target="#testimonialCarousel"
                            data-bs-slide-to="{{ $index }}"
                            class="{{ $index === 0 ? 'active' : '' }}"
                            aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                            aria-label="Slide {{ $index + 1 }}"></button>
                @endforeach
            </div>
        </div>
    </div>
</section>

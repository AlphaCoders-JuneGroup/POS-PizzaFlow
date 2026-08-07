{{-- Today's Special Offers --}}
@php
    $offerCards = ($promotions ?? collect())->where('type', 'card')->values();
    $promoBanner = ($promotions ?? collect())->firstWhere('type', 'banner');
@endphp

@if ($offerCards->isNotEmpty() || $promoBanner)
<section class="pf-section pf-offers" id="offers">
    <div class="container">
        <div class="pf-section-header text-center" data-aos="fade-up">
            <span class="pf-eyebrow">Limited Time</span>
            <h2 class="pf-section-title">Today's Special Offers</h2>
            <p class="pf-section-sub">Save big on your next craving — grab these deals now.</p>
        </div>

        @if ($offerCards->isNotEmpty())
            <div class="row g-4">
                @foreach ($offerCards as $index => $offer)
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                        <div class="pf-offer-card {{ $offer->cardClass() }}">
                            <div class="pf-offer-icon">{{ $offer->icon ?: '🔥' }}</div>
                            <h3 class="pf-offer-title">{{ $offer->title }}</h3>
                            <p class="pf-offer-text">{{ $offer->description }}</p>
                            @if ($offer->ends_at)
                                <p class="pf-offer-meta small mb-2 opacity-90">
                                    <i class="bi bi-clock me-1"></i>Ends {{ $offer->ends_at->format('M j, Y') }}
                                </p>
                            @endif
                            @if ($offer->promo_code)
                                <button type="button"
                                        class="btn btn-pf-light btn-sm"
                                        data-claim-promo="{{ $offer->promo_code }}">
                                    {{ $offer->button_text ?: 'Claim Offer' }}
                                </button>
                            @else
                                <a href="{{ $offer->button_link ?: '#menu' }}" class="btn btn-pf-light btn-sm">
                                    {{ $offer->button_text ?: 'Order Now' }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($promoBanner)
            <div class="pf-promo-banner mt-5" data-aos="zoom-in">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h3 class="mb-2">{{ $promoBanner->title }}</h3>
                        <p class="mb-0 opacity-90">
                            @if ($promoBanner->promo_code)
                                {!! preg_replace(
                                    '/\b('.preg_quote($promoBanner->promo_code, '/').')\b/i',
                                    '<strong>$1</strong>',
                                    e($promoBanner->description)
                                ) !!}
                            @else
                                {{ $promoBanner->description }}
                            @endif
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        @if ($promoBanner->promo_code)
                            <button type="button"
                                    class="btn btn-pf-accent btn-lg"
                                    data-claim-promo="{{ $promoBanner->promo_code }}">
                                {{ $promoBanner->button_text ?: 'Get Started' }}
                            </button>
                        @else
                            <a href="{{ $promoBanner->button_link ?: '#menu' }}" class="btn btn-pf-accent btn-lg">
                                {{ $promoBanner->button_text ?: 'Get Started' }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
@endif

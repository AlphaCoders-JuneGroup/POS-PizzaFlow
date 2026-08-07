@extends('layouts.dashboard')

@section('title', 'Promotions & Offers')
@section('page_title', 'Promotions')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="pf-dash-heading">Promotions & Offers</h2>
        <p class="pf-dash-sub">Create homepage offer cards and the promo banner managers can update anytime.</p>
    </div>
    <button type="button" class="btn btn-pf-primary" data-bs-toggle="modal" data-bs-target="#promoModal" id="openCreatePromoBtn">
        <i class="bi bi-plus-lg me-1"></i> Add Promotion
    </button>
</div>

@if (session('error'))
    <div class="alert alert-danger pf-alert">{{ session('error') }}</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-red"><i class="bi bi-tag"></i></div>
            <div><span>TOTAL</span><strong>{{ $promotions->count() }}</strong></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-orange"><i class="bi bi-check-circle"></i></div>
            <div><span>ACTIVE</span><strong>{{ $activeCount }}</strong></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-gold"><i class="bi bi-grid"></i></div>
            <div><span>OFFER CARDS</span><strong>{{ $cards->count() }}</strong></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-red"><i class="bi bi-megaphone"></i></div>
            <div><span>BANNERS</span><strong>{{ $banners->count() }}</strong></div>
        </div>
    </div>
</div>

<div class="pf-dash-panel">
    <div class="pf-dash-panel-head">
        <h3>All promotions</h3>
        <span class="text-muted small">Shown on the homepage Offers section</span>
    </div>
    <div class="table-responsive">
        <table class="table pf-dash-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Style</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($promotions as $promo)
                    <tr>
                        <td>
                            <strong>{{ $promo->title }}</strong>
                            <div class="text-muted small">{{ \Illuminate\Support\Str::limit($promo->description, 70) }}</div>
                            @if ($promo->promo_code)
                                <span class="badge text-bg-warning mt-1">{{ $promo->promo_code }}</span>
                            @endif
                            <div class="text-muted small mt-1">{{ $promo->discountLabel() }}</div>
                            <div class="text-muted small">{{ $promo->scheduleLabel() }}</div>
                            @if ((int) $promo->usage_limit > 0)
                                <div class="text-muted small">Uses: {{ (int) $promo->used_count }}/{{ (int) $promo->usage_limit }}</div>
                            @elseif ((int) $promo->used_count > 0)
                                <div class="text-muted small">Uses: {{ (int) $promo->used_count }}</div>
                            @endif
                        </td>
                        <td>{{ $promo->typeLabel() }}</td>
                        <td>{{ $promo->styleLabel() }}</td>
                        <td>{{ $promo->sort_order }}</td>
                        <td>
                            @if ($promo->isCurrentlyValid())
                                <span class="badge text-bg-success">Live</span>
                            @elseif ($promo->is_active)
                                <span class="badge text-bg-warning">Scheduled / Limited</span>
                            @else
                                <span class="badge text-bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <button type="button"
                                    class="btn btn-sm btn-pf-outline edit-promo-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#promoModal"
                                    data-id="{{ $promo->_id }}"
                                    data-title="{{ $promo->title }}"
                                    data-description="{{ $promo->description }}"
                                    data-type="{{ $promo->type }}"
                                    data-icon="{{ $promo->icon }}"
                                    data-button-text="{{ $promo->button_text }}"
                                    data-button-link="{{ $promo->button_link }}"
                                    data-promo-code="{{ $promo->promo_code }}"
                                    data-discount-type="{{ $promo->discount_type ?: 'none' }}"
                                    data-discount-value="{{ $promo->discount_value }}"
                                    data-max-discount="{{ $promo->max_discount }}"
                                    data-min-order="{{ $promo->min_order_amount }}"
                                    data-usage-limit="{{ $promo->usage_limit }}"
                                    data-starts-at="{{ optional($promo->starts_at)->format('Y-m-d\TH:i') }}"
                                    data-ends-at="{{ optional($promo->ends_at)->format('Y-m-d\TH:i') }}"
                                    data-first-order="{{ $promo->first_order_only ? '1' : '0' }}"
                                    data-style="{{ $promo->style }}"
                                    data-sort-order="{{ $promo->sort_order }}"
                                    data-is-active="{{ $promo->is_active ? '1' : '0' }}">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('promotions.toggle', $promo->_id) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-pf-outline">
                                    {{ $promo->is_active ? 'Disable' : 'Enable' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('promotions.destroy', $promo->_id) }}" class="d-inline"
                                  onsubmit="return confirm('Delete this promotion?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No promotions yet. Click <strong>Add Promotion</strong> to create one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Create / Edit Modal --}}
<div class="modal fade" id="promoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('promotions.store') }}" class="modal-content" id="promoForm" novalidate>
            @csrf
            <input type="hidden" name="_method" id="promoMethod" value="POST">
            <div class="modal-header">
                <h5 class="modal-title" id="promoModalTitle">Add Promotion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if ($errors->any() && in_array(session('promo_form'), ['create', 'edit'], true))
                    <div class="alert alert-danger pf-alert">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label pf-required" for="promo_title">Title</label>
                        <input type="text" name="title" id="promo_title"
                               class="form-control pf-input @error('title') is-invalid @enderror"
                               value="{{ old('title') }}" required minlength="3" maxlength="120">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label pf-required" for="promo_type">Type</label>
                        <select name="type" id="promo_type" class="form-select pf-input" required>
                            <option value="card" @selected(old('type', 'card') === 'card')>Offer card</option>
                            <option value="banner" @selected(old('type') === 'banner')>Promo banner</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label pf-required" for="promo_description">Description</label>
                        <textarea name="description" id="promo_description" rows="3"
                                  class="form-control pf-input @error('description') is-invalid @enderror"
                                  required minlength="5" maxlength="400">{{ old('description') }}</textarea>
                    </div>
                    <div class="col-md-4 promo-card-only">
                        <label class="form-label" for="promo_icon">Icon / emoji</label>
                        <input type="text" name="icon" id="promo_icon" class="form-control pf-input"
                               value="{{ old('icon', '🔥') }}" maxlength="16" placeholder="🔥">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label pf-required" for="promo_button_text">Button text</label>
                        <input type="text" name="button_text" id="promo_button_text" class="form-control pf-input"
                               value="{{ old('button_text', 'Claim Offer') }}" required maxlength="40">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="promo_button_link">Button link</label>
                        <input type="text" name="button_link" id="promo_button_link" class="form-control pf-input"
                               value="{{ old('button_link', '#menu') }}" maxlength="255" placeholder="#menu">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="promo_code">Promo code</label>
                        <input type="text" name="promo_code" id="promo_code" class="form-control pf-input"
                               value="{{ old('promo_code') }}" maxlength="40" placeholder="FLOW20"
                               style="text-transform: uppercase;">
                        <div class="form-text">Required for % / fixed discounts. Leave empty for auto free delivery.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label pf-required" for="promo_discount_type">Checkout discount</label>
                        <select name="discount_type" id="promo_discount_type" class="form-select pf-input" required>
                            <option value="none" @selected(old('discount_type', 'none') === 'none')>Display only</option>
                            <option value="percent" @selected(old('discount_type') === 'percent')>Percent off</option>
                            <option value="fixed" @selected(old('discount_type') === 'fixed')>Fixed amount off</option>
                            <option value="free_delivery" @selected(old('discount_type') === 'free_delivery')>Free delivery</option>
                        </select>
                    </div>
                    <div class="col-md-4" id="promoDiscountValueWrap">
                        <label class="form-label" for="promo_discount_value">Discount value</label>
                        <input type="number" name="discount_value" id="promo_discount_value" class="form-control pf-input"
                               value="{{ old('discount_value', 0) }}" min="0" step="0.01"
                               placeholder="% or Rs. amount">
                    </div>
                    <div class="col-md-4" id="promoMaxDiscountWrap">
                        <label class="form-label" for="promo_max_discount">Max discount (Rs.)</label>
                        <input type="number" name="max_discount" id="promo_max_discount" class="form-control pf-input"
                               value="{{ old('max_discount', 0) }}" min="0" step="1"
                               placeholder="0 = no cap">
                        <div class="form-text">Optional cap for percent discounts.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="promo_min_order">Min order (Rs.)</label>
                        <input type="number" name="min_order_amount" id="promo_min_order" class="form-control pf-input"
                               value="{{ old('min_order_amount', 0) }}" min="0" step="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="promo_usage_limit">Usage limit</label>
                        <input type="number" name="usage_limit" id="promo_usage_limit" class="form-control pf-input"
                               value="{{ old('usage_limit', 0) }}" min="0" step="1"
                               placeholder="0 = unlimited">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="promo_starts_at">Starts at</label>
                        <input type="datetime-local" name="starts_at" id="promo_starts_at" class="form-control pf-input"
                               value="{{ old('starts_at') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="promo_ends_at">Ends at</label>
                        <input type="datetime-local" name="ends_at" id="promo_ends_at" class="form-control pf-input"
                               value="{{ old('ends_at') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label pf-required" for="promo_style">Card / theme style</label>
                        <select name="style" id="promo_style" class="form-select pf-input" required>
                            <option value="style_1" @selected(old('style', 'style_1') === 'style_1')>Coral / Orange</option>
                            <option value="style_2" @selected(old('style') === 'style_2')>Dark / Red</option>
                            <option value="style_3" @selected(old('style') === 'style_3')>Gold / Warm</option>
                            <option value="style_4" @selected(old('style') === 'style_4')>Berry / Pink</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="promo_sort_order">Sort order</label>
                        <input type="number" name="sort_order" id="promo_sort_order" class="form-control pf-input"
                               value="{{ old('sort_order', 0) }}" min="0" max="999">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input type="hidden" name="first_order_only" value="0">
                            <input type="checkbox" name="first_order_only" id="promo_first_order" class="form-check-input"
                                   value="1" @checked(old('first_order_only') == '1')>
                            <label class="form-check-label" for="promo_first_order">First order only</label>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="promo_is_active" class="form-check-input"
                                   value="1" @checked(old('is_active', '1') == '1')>
                            <label class="form-check-label" for="promo_is_active">Active on homepage</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-pf-primary" id="promoSubmitBtn">Save Promotion</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('promoForm');
    const modalEl = document.getElementById('promoModal');
    const methodInput = document.getElementById('promoMethod');
    const titleEl = document.getElementById('promoModalTitle');
    const typeSelect = document.getElementById('promo_type');
    const storeUrl = @json(route('promotions.store'));
    const updateUrlTemplate = @json(url('/dashboard/promotions'));

    const discountTypeSelect = document.getElementById('promo_discount_type');

    function toggleTypeFields() {
        const isBanner = typeSelect.value === 'banner';
        document.querySelectorAll('.promo-card-only').forEach(el => el.classList.toggle('d-none', isBanner));
    }

    function toggleDiscountFields() {
        const type = discountTypeSelect.value;
        document.getElementById('promoDiscountValueWrap')?.classList.toggle('d-none', type === 'none' || type === 'free_delivery');
        document.getElementById('promoMaxDiscountWrap')?.classList.toggle('d-none', type !== 'percent');
    }

    function resetCreateForm() {
        form.action = storeUrl;
        methodInput.value = 'POST';
        titleEl.textContent = 'Add Promotion';
        form.reset();
        document.getElementById('promo_icon').value = '🔥';
        document.getElementById('promo_button_text').value = 'Claim Offer';
        document.getElementById('promo_button_link').value = '#menu';
        document.getElementById('promo_sort_order').value = '0';
        document.getElementById('promo_discount_value').value = '0';
        document.getElementById('promo_max_discount').value = '0';
        document.getElementById('promo_min_order').value = '0';
        document.getElementById('promo_usage_limit').value = '0';
        document.getElementById('promo_starts_at').value = '';
        document.getElementById('promo_ends_at').value = '';
        document.getElementById('promo_is_active').checked = true;
        document.getElementById('promo_first_order').checked = false;
        document.getElementById('promo_type').value = 'card';
        document.getElementById('promo_style').value = 'style_1';
        document.getElementById('promo_discount_type').value = 'none';
        toggleTypeFields();
        toggleDiscountFields();
    }

    typeSelect.addEventListener('change', toggleTypeFields);
    discountTypeSelect.addEventListener('change', toggleDiscountFields);
    document.getElementById('openCreatePromoBtn')?.addEventListener('click', resetCreateForm);

    document.querySelectorAll('.edit-promo-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            form.action = updateUrlTemplate + '/' + this.dataset.id;
            methodInput.value = 'PUT';
            titleEl.textContent = 'Edit Promotion';
            document.getElementById('promo_title').value = this.dataset.title || '';
            document.getElementById('promo_description').value = this.dataset.description || '';
            document.getElementById('promo_type').value = this.dataset.type || 'card';
            document.getElementById('promo_icon').value = this.dataset.icon || '🔥';
            document.getElementById('promo_button_text').value = this.dataset.buttonText || 'Claim Offer';
            document.getElementById('promo_button_link').value = this.dataset.buttonLink || '#menu';
            document.getElementById('promo_code').value = this.dataset.promoCode || '';
            document.getElementById('promo_discount_type').value = this.dataset.discountType || 'none';
            document.getElementById('promo_discount_value').value = this.dataset.discountValue || '0';
            document.getElementById('promo_max_discount').value = this.dataset.maxDiscount || '0';
            document.getElementById('promo_min_order').value = this.dataset.minOrder || '0';
            document.getElementById('promo_usage_limit').value = this.dataset.usageLimit || '0';
            document.getElementById('promo_starts_at').value = this.dataset.startsAt || '';
            document.getElementById('promo_ends_at').value = this.dataset.endsAt || '';
            document.getElementById('promo_first_order').checked = this.dataset.firstOrder === '1';
            document.getElementById('promo_style').value = this.dataset.style || 'style_1';
            document.getElementById('promo_sort_order').value = this.dataset.sortOrder || '0';
            document.getElementById('promo_is_active').checked = this.dataset.isActive === '1';
            toggleTypeFields();
            toggleDiscountFields();
        });
    });

    toggleTypeFields();
    toggleDiscountFields();

    @if ($errors->any() && in_array(session('promo_form'), ['create', 'edit'], true))
    if (window.bootstrap && modalEl) {
        @if (session('promo_form') === 'edit' && session('edit_promotion_id'))
        form.action = updateUrlTemplate + '/' + @json(session('edit_promotion_id'));
        methodInput.value = 'PUT';
        titleEl.textContent = 'Edit Promotion';
        @endif
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
        toggleTypeFields();
        toggleDiscountFields();
    }
    @endif
});
</script>
@endpush

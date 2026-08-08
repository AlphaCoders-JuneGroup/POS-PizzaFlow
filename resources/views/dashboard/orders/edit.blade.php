@extends('layouts.dashboard')

@section('title', 'Modify '.$order->order_number)
@section('page_title', 'Modify Order')

@section('content')
<div class="mb-4">
    <a href="{{ route('orders.manage.show', $order) }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Back to order
    </a>
    <h2 class="pf-dash-heading mt-2">Modify {{ $order->order_number }}</h2>
    <p class="pf-dash-sub">Changes are allowed only while status is <strong>Received</strong> (before kitchen prep).</p>
</div>

@if (session('error'))
    <div class="alert alert-danger pf-alert">{{ session('error') }}</div>
@endif

<form method="POST" action="{{ route('orders.manage.update', $order) }}" class="pf-dash-panel p-4">
    @csrf
    @method('PUT')

    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label pf-required">Fulfillment</label>
            <select name="fulfillment_type" id="fulfillment_type" class="form-select pf-input" required>
                <option value="delivery" @selected(old('fulfillment_type', $order->fulfillmentType()) === 'delivery')>Delivery</option>
                <option value="pickup" @selected(old('fulfillment_type', $order->fulfillmentType()) === 'pickup')>Pickup</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label pf-required">Customer name</label>
            <input type="text" name="customer_name" class="form-control pf-input"
                   value="{{ old('customer_name', $order->customer_name) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label pf-required">Phone</label>
            <input type="text" name="customer_phone" class="form-control pf-input"
                   value="{{ old('customer_phone', $order->customer_phone) }}" required>
        </div>

        <div class="col-md-6 delivery-only">
            <label class="form-label">Delivery address</label>
            <input type="text" name="delivery_address" class="form-control pf-input"
                   value="{{ old('delivery_address', $order->isPickup() ? '' : $order->delivery_address) }}">
        </div>
        <div class="col-md-3 delivery-only">
            <label class="form-label">City</label>
            <input type="text" name="delivery_city" class="form-control pf-input"
                   value="{{ old('delivery_city', $order->delivery_city) }}">
        </div>
        <div class="col-md-3 delivery-only">
            <label class="form-label">Landmark</label>
            <input type="text" name="delivery_landmark" class="form-control pf-input"
                   value="{{ old('delivery_landmark', $order->delivery_landmark) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Delivery instructions</label>
            <input type="text" name="delivery_instructions" class="form-control pf-input"
                   value="{{ old('delivery_instructions', $order->delivery_instructions) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Notes</label>
            <input type="text" name="notes" class="form-control pf-input"
                   value="{{ old('notes', $order->notes) }}">
        </div>
    </div>

    <hr class="my-4">
    <h3 class="h6 mb-3">Line items</h3>
    <div id="orderItems">
        @foreach (old('items', $order->items ?? []) as $i => $item)
            <div class="row g-2 mb-2 order-item-row">
                <div class="col-md-6">
                    <input type="text" name="items[{{ $i }}][name]" class="form-control pf-input"
                           value="{{ $item['name'] ?? '' }}" required placeholder="Item name">
                </div>
                <div class="col-md-2">
                    <input type="number" name="items[{{ $i }}][qty]" class="form-control pf-input"
                           value="{{ $item['qty'] ?? 1 }}" min="1" max="50" required>
                </div>
                <div class="col-md-3">
                    <input type="number" name="items[{{ $i }}][price]" class="form-control pf-input"
                           value="{{ $item['price'] ?? 0 }}" min="0" required placeholder="Unit price">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger w-100 remove-item-btn">&times;</button>
                </div>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-sm btn-pf-outline mt-2" id="addItemBtn">
        <i class="bi bi-plus-lg me-1"></i> Add item
    </button>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-pf-primary">Save changes</button>
        <a href="{{ route('orders.manage.show', $order) }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('fulfillment_type');
    const items = document.getElementById('orderItems');
    let index = items.querySelectorAll('.order-item-row').length;

    function toggleDelivery() {
        const show = typeSelect.value === 'delivery';
        document.querySelectorAll('.delivery-only').forEach(el => el.classList.toggle('d-none', !show));
    }

    typeSelect.addEventListener('change', toggleDelivery);
    toggleDelivery();

    document.getElementById('addItemBtn').addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 order-item-row';
        row.innerHTML = `
            <div class="col-md-6"><input type="text" name="items[${index}][name]" class="form-control pf-input" required placeholder="Item name"></div>
            <div class="col-md-2"><input type="number" name="items[${index}][qty]" class="form-control pf-input" value="1" min="1" max="50" required></div>
            <div class="col-md-3"><input type="number" name="items[${index}][price]" class="form-control pf-input" value="0" min="0" required placeholder="Unit price"></div>
            <div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 remove-item-btn">&times;</button></div>`;
        items.appendChild(row);
        index++;
    });

    items.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item-btn')) {
            const rows = items.querySelectorAll('.order-item-row');
            if (rows.length > 1) e.target.closest('.order-item-row').remove();
        }
    });
});
</script>
@endpush

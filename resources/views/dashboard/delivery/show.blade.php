@extends('layouts.dashboard')

@section('title', 'Delivery Route '.$order->order_number)
@section('page_title', 'Route Details')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <a href="{{ route('delivery.index') }}" class="text-decoration-none small text-muted">
            <i class="bi bi-arrow-left me-1"></i> Back to Delivery
        </a>
        <h2 class="pf-dash-heading mt-2">{{ $order->order_number }}</h2>
        <p class="pf-dash-sub mb-0">
            <span class="badge text-bg-{{ $order->statusTone() }}">{{ $order->statusLabel() }}</span>
            @if ($order->driver)
                · Driver: {{ $order->driver->name }}
            @endif
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ $order->mapsUrl() }}" target="_blank" rel="noopener" class="btn btn-pf-primary">
            <i class="bi bi-compass me-1"></i> Open in Google Maps
        </a>
        @if ($isDriver || $canManage)
            @if ($order->driver_id && $order->status !== 'out_for_delivery' && $order->status !== 'delivered')
                <form method="POST" action="{{ route('delivery.start', $order) }}">
                    @csrf
                    <button type="submit" class="btn btn-pf-outline">
                        <i class="bi bi-play-fill me-1"></i> Start delivery
                    </button>
                </form>
            @elseif ($order->status === 'out_for_delivery')
                <form method="POST" action="{{ route('delivery.complete', $order) }}">
                    @csrf
                    <button type="submit" class="btn btn-pf-outline"
                            onclick="return confirm('Confirm this order was delivered?')">
                        <i class="bi bi-check2-circle me-1"></i> Mark delivered
                    </button>
                </form>
            @endif
        @endif
    </div>
</div>

@if (session('error'))
    <div class="alert alert-danger pf-alert">{{ session('error') }}</div>
@endif
@if (session('success'))
    <div class="alert alert-success pf-alert">{{ session('success') }}</div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="pf-dash-panel p-4 mb-4">
            <h3 class="h5 mb-3"><i class="bi bi-signpost-split me-2"></i>Route</h3>
            <ol class="pf-route-steps mb-0">
                <li>
                    <strong>Pickup</strong>
                    <div class="text-muted">{{ \App\Support\DeliveryDispatch::STORE_LOCATION }}</div>
                </li>
                <li>
                    <strong>Drop-off</strong>
                    <div class="fw-semibold">{{ $order->destinationLabel() }}</div>
                    @if ($order->delivery_landmark)
                        <div class="text-muted small">Landmark: {{ $order->delivery_landmark }}</div>
                    @endif
                </li>
            </ol>
            <div class="row g-3 mt-3">
                <div class="col-sm-4">
                    <div class="small text-muted">Distance</div>
                    <div class="fw-semibold">{{ $order->route_distance_km ? number_format($order->route_distance_km, 1).' km' : '—' }}</div>
                </div>
                <div class="col-sm-4">
                    <div class="small text-muted">ETA</div>
                    <div class="fw-semibold">{{ $order->route_eta_minutes ? '~'.$order->route_eta_minutes.' min' : '—' }}</div>
                </div>
                <div class="col-sm-4">
                    <div class="small text-muted">Payment</div>
                    <div class="fw-semibold">{{ $order->payment_method }} · {{ $order->payment_status }}</div>
                </div>
            </div>
            @if ($order->route_summary)
                <p class="text-muted small mt-3 mb-0">{{ $order->route_summary }}</p>
            @endif
        </div>

        <div class="pf-dash-panel p-4 mb-4">
            <h3 class="h5 mb-3"><i class="bi bi-bag-check me-2"></i>Order items</h3>
            <ul class="list-unstyled mb-0">
                @foreach ($order->items ?? [] as $item)
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span>{{ $item['qty'] ?? 1 }}× {{ $item['name'] ?? 'Item' }}</span>
                        <span class="text-muted">LKR {{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 1)) }}</span>
                    </li>
                @endforeach
                <li class="d-flex justify-content-between pt-3 fw-semibold">
                    <span>Total</span>
                    <span>LKR {{ number_format($order->total) }}</span>
                </li>
            </ul>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="pf-dash-panel p-4 mb-4">
            <h3 class="h5 mb-3"><i class="bi bi-person-lines-fill me-2"></i>Customer</h3>
            <div class="mb-2"><strong>{{ $order->customer_name ?: optional($order->user)->name ?: 'Customer' }}</strong></div>
            @if ($order->customer_phone)
                <div class="mb-2">
                    <a href="tel:{{ $order->customer_phone }}"><i class="bi bi-telephone me-1"></i>{{ $order->customer_phone }}</a>
                </div>
            @endif
            <div class="text-muted">{{ $order->destinationLabel() }}</div>
        </div>

        <div class="pf-dash-panel p-4 mb-4">
            <h3 class="h5 mb-3"><i class="bi bi-chat-left-text me-2"></i>Delivery instructions</h3>
            <p class="mb-3">{{ $order->instructionsText() }}</p>

            @if ($canManage || $isDriver)
                <form method="POST" action="{{ route('delivery.instructions', $order) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label" for="delivery_instructions">Instructions</label>
                        <textarea name="delivery_instructions" id="delivery_instructions" rows="3"
                                  class="form-control pf-input"
                                  placeholder="Gate code, call on arrival, leave with security…">{{ old('delivery_instructions', $order->delivery_instructions ?: $order->notes) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="delivery_landmark">Landmark</label>
                        <input type="text" name="delivery_landmark" id="delivery_landmark"
                               class="form-control pf-input"
                               value="{{ old('delivery_landmark', $order->delivery_landmark) }}"
                               placeholder="Near the park / blue gate">
                    </div>
                    <button type="submit" class="btn btn-pf-primary btn-sm">Save instructions</button>
                </form>
            @endif
        </div>

        @if ($canManage && $order->status !== 'delivered')
            <div class="pf-dash-panel p-4">
                <h3 class="h5 mb-3"><i class="bi bi-person-check me-2"></i>Assign / reassign</h3>
                <form method="POST" action="{{ route('delivery.assign', $order) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Driver</label>
                        <select name="driver_id" class="form-select pf-input" required>
                            <option value="">Select driver</option>
                            @foreach ($drivers as $driver)
                                <option value="{{ $driver->_id }}" @selected((string) $driver->_id === (string) $order->driver_id)>
                                    {{ $driver->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-pf-primary btn-sm">Save assignment</button>
                </form>
                @if ($order->driver_id)
                    <form method="POST" action="{{ route('delivery.unassign', $order) }}" class="mt-2"
                          onsubmit="return confirm('Unassign this driver?')">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">Unassign driver</button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.pf-route-steps {
    padding-left: 1.25rem;
}
.pf-route-steps li + li {
    margin-top: 1rem;
}
.pf-delivery-card:first-child {
    border-top: 0 !important;
}
</style>
@endpush

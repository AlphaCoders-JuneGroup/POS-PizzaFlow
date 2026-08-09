@extends('layouts.dashboard')

@section('title', 'Order '.$order->order_number)
@section('page_title', 'Order Details')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <a href="{{ route('orders.manage.index') }}" class="text-decoration-none small text-muted">
            <i class="bi bi-arrow-left me-1"></i> Back to Orders
        </a>
        <h2 class="pf-dash-heading mt-2">{{ $order->order_number }}</h2>
        <p class="pf-dash-sub mb-0">
            <span class="badge text-bg-{{ $order->statusTone() }}">{{ $order->statusLabel() }}</span>
            <span class="badge {{ $order->fulfillmentType() === 'pickup' ? 'text-bg-secondary' : 'text-bg-warning' }} ms-1">
                {{ $order->fulfillmentLabel() }}
            </span>
            · {{ optional($order->placed_at)->format('M j, Y g:i A') }}
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if ($canModify)
            <a href="{{ route('orders.manage.edit', $order) }}" class="btn btn-pf-outline">
                <i class="bi bi-pencil me-1"></i> Modify
            </a>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                Cancel order
            </button>
        @endif
        @if ($canManage && $order->canAdvance())
            <form method="POST" action="{{ route('orders.manage.advance', $order) }}">
                @csrf
                <button type="submit" class="btn btn-pf-primary">
                    Mark as {{ $order->nextStatusLabel() }}
                </button>
            </form>
        @endif
        @if ($order->isAssignable())
            <a href="{{ route('delivery.index', ['tab' => 'queue']) }}" class="btn btn-pf-outline">
                Assign driver
            </a>
        @endif
    </div>
</div>

@if (session('error'))
    <div class="alert alert-danger pf-alert">{{ session('error') }}</div>
@endif

{{-- Pipeline --}}
<div class="pf-dash-panel p-3 mb-4">
    <div class="d-flex flex-wrap gap-2 align-items-center">
        @foreach ($flowSteps as $step)
            @php
                $current = $order->normalizedStatus();
                $keys = array_column($flowSteps, 'key');
                $curIdx = array_search($current === 'pending' ? 'received' : $current, $keys, true);
                $stepIdx = array_search($step['key'], $keys, true);
                $done = $curIdx !== false && $stepIdx !== false && $stepIdx <= $curIdx && $current !== 'cancelled';
                $isCurrent = $step['key'] === $current || ($step['key'] === 'received' && $order->status === 'pending');
            @endphp
            <div class="pf-status-chip {{ $done ? 'done' : '' }} {{ $isCurrent ? 'current' : '' }}">
                {{ $step['label'] }}
            </div>
            @if (! $loop->last)
                <i class="bi bi-arrow-right text-muted"></i>
            @endif
        @endforeach
    </div>
    @if ($order->normalizedStatus() === 'cancelled')
        <div class="text-danger small mt-2">This order was cancelled{{ $order->cancelled_at ? ' '.$order->cancelled_at->diffForHumans() : '' }}.</div>
    @endif
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="pf-dash-panel p-4 mb-4">
            <h3 class="h5 mb-3">Items</h3>
            <ul class="list-unstyled mb-0">
                @foreach ($order->items ?? [] as $item)
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span>{{ $item['qty'] ?? 1 }}× {{ $item['name'] ?? 'Item' }}</span>
                        <span class="text-muted">Rs. {{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 1)) }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="mt-3 small">
                <div class="d-flex justify-content-between"><span>Subtotal</span><span>Rs. {{ number_format($order->subtotal) }}</span></div>
                @if ($order->discount)
                    <div class="d-flex justify-content-between text-success"><span>Discount</span><span>- Rs. {{ number_format($order->discount) }}</span></div>
                @endif
                <div class="d-flex justify-content-between"><span>Delivery fee</span><span>{{ $order->delivery_fee ? 'Rs. '.number_format($order->delivery_fee) : 'FREE' }}</span></div>
                <div class="d-flex justify-content-between fw-bold mt-2"><span>Total</span><span>Rs. {{ number_format($order->total) }}</span></div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="pf-dash-panel p-4 mb-4">
            <h3 class="h5 mb-3">Customer</h3>
            <div class="mb-2"><strong>{{ $order->customer_name ?: optional($order->user)->name ?: 'Customer' }}</strong></div>
            @if ($order->customer_phone)
                <div class="mb-2"><a href="tel:{{ $order->customer_phone }}">{{ $order->customer_phone }}</a></div>
            @endif
            <div class="text-muted">{{ $order->destinationLabel() }}</div>
            @if ($order->delivery_landmark)
                <div class="small text-muted mt-1">Landmark: {{ $order->delivery_landmark }}</div>
            @endif
            <div class="small mt-2">{{ $order->instructionsText() }}</div>
        </div>
        <div class="pf-dash-panel p-4 mb-4">
            <h3 class="h5 mb-3">Payment</h3>
            <div>{{ $order->payment_method }} · {{ $order->payment_status }}</div>
            @if ($order->promo_code)
                <div class="small mt-1">Promo: <strong>{{ $order->promo_code }}</strong></div>
            @endif
            @if ($order->driver)
                <div class="small mt-2">Driver: {{ $order->driver->name }}</div>
            @endif
        </div>
    </div>
</div>

@if ($canModify)
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('orders.manage.cancel', $order) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Cancel order {{ $order->order_number }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Only allowed before kitchen preparation starts.</p>
                <label class="form-label" for="cancel_reason">Reason (optional)</label>
                <input type="text" name="reason" id="cancel_reason" class="form-control pf-input" maxlength="255">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep order</button>
                <button type="submit" class="btn btn-outline-danger">Confirm cancel</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.pf-status-chip {
    padding: 0.4rem 0.75rem;
    border-radius: 999px;
    border: 1px solid var(--dash-border);
    font-size: 0.8rem;
    color: var(--dash-muted);
    background: #fff;
}
.pf-status-chip.done { border-color: rgba(34,197,94,.4); color: #15803d; background: rgba(34,197,94,.08); }
.pf-status-chip.current { border-color: var(--dash-primary); color: var(--dash-primary); font-weight: 700; background: rgba(230,57,70,.08); }
</style>
@endpush

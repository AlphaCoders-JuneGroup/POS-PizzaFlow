@extends('layouts.dashboard')

@section('title', 'Order Management')
@section('page_title', 'Orders')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="pf-dash-heading">Order Management</h2>
        <p class="pf-dash-sub">
            Receive and organize pickup/delivery orders. Status flow:
            Received → Preparing → Baking → Ready → Out for Delivery.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2 text-muted small" id="ordersLiveBadge">
        <span class="pf-live-dot"></span> Live updates
    </div>
</div>

@if (session('error'))
    <div class="alert alert-danger pf-alert">{{ session('error') }}</div>
@endif

{{-- Status pipeline --}}
<div class="pf-order-flow mb-4">
    @foreach ($flowSteps as $step)
        @php
            $countKey = $step['key'];
            $count = $counts[$countKey] ?? 0;
            $active = $filters['status'] === $step['key'];
        @endphp
        <a href="{{ route('orders.manage.index', ['status' => $step['key'], 'type' => $filters['type'], 'q' => $filters['q']]) }}"
           class="pf-order-flow-step {{ $active ? 'active' : '' }}">
            <span>{{ $step['label'] }}</span>
            <strong>{{ $count }}</strong>
        </a>
        @if (! $loop->last)
            <span class="pf-order-flow-arrow"><i class="bi bi-chevron-right"></i></span>
        @endif
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-orange"><i class="bi bi-inbox"></i></div>
            <div><span>OPEN</span><strong id="ordersOpenCount">{{ $counts['open'] }}</strong></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-gold"><i class="bi bi-fire"></i></div>
            <div><span>IN KITCHEN</span><strong>{{ ($counts['preparing'] ?? 0) + ($counts['baking'] ?? 0) }}</strong></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-red"><i class="bi bi-bag-check"></i></div>
            <div><span>READY</span><strong>{{ $counts['ready'] ?? 0 }}</strong></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-red"><i class="bi bi-truck"></i></div>
            <div><span>OUT</span><strong>{{ $counts['out_for_delivery'] ?? 0 }}</strong></div>
        </div>
    </div>
</div>

<div class="pf-dash-panel mb-4">
    <form method="GET" action="{{ route('orders.manage.index') }}" id="ordersFilterForm" class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label" for="orderSearch">Search</label>
            <input type="search" name="q" id="orderSearch" value="{{ $filters['q'] }}"
                   class="form-control pf-input" placeholder="Order #, customer, phone"
                   autocomplete="off">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" id="orderStatusFilter" class="form-select pf-input">
                <option value="open" @selected($filters['status'] === 'open')>Open orders</option>
                <option value="all" @selected($filters['status'] === 'all')>All</option>
                @foreach ($flowSteps as $step)
                    <option value="{{ $step['key'] }}" @selected($filters['status'] === $step['key'])>{{ $step['label'] }}</option>
                @endforeach
                <option value="cancelled" @selected($filters['status'] === 'cancelled')>Cancelled</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Type</label>
            <select name="type" id="orderTypeFilter" class="form-select pf-input">
                <option value="" @selected($filters['type'] === '')>Pickup & Delivery</option>
                <option value="pickup" @selected($filters['type'] === 'pickup')>Pickup</option>
                <option value="delivery" @selected($filters['type'] === 'delivery')>Delivery</option>
            </select>
        </div>
    </form>
</div>

<div class="pf-dash-panel">
    <div class="pf-dash-panel-head">
        <h3>{{ $isDriver ? 'My assigned orders' : 'Orders' }}</h3>
        <span class="text-muted small">{{ $orders->count() }} shown</span>
    </div>
    <div class="table-responsive">
        <table class="table pf-dash-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>Items</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>
                            <strong>{{ $order->order_number }}</strong>
                            <div class="text-muted small">{{ optional($order->placed_at)->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div>{{ $order->customer_name ?: '—' }}</div>
                            <div class="text-muted small">{{ $order->customer_phone ?: '—' }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $order->fulfillmentType() === 'pickup' ? 'text-bg-secondary' : 'text-bg-warning' }}">
                                {{ $order->fulfillmentLabel() }}
                            </span>
                            <div class="text-muted small mt-1">{{ \Illuminate\Support\Str::limit($order->destinationLabel(), 28) }}</div>
                        </td>
                        <td class="small">{{ \Illuminate\Support\Str::limit($order->itemSummary(), 40) }}</td>
                        <td>
                            <span class="badge text-bg-{{ $order->statusTone() }}">{{ $order->statusLabel() }}</span>
                        </td>
                        <td class="fw-semibold">Rs. {{ number_format($order->total) }}</td>
                        <td class="text-end">
                            <a href="{{ route('orders.manage.show', $order) }}" class="btn btn-sm btn-pf-outline">View</a>
                            @if ($canManage && $order->canAdvance())
                                <form method="POST" action="{{ route('orders.manage.advance', $order) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-pf-primary"
                                            title="Mark as {{ $order->nextStatusLabel() }}">
                                        → {{ $order->nextStatusLabel() }}
                                    </button>
                                </form>
                            @endif
                            @if ($canManage && $order->canModifyOrCancel() && ! $isDriver)
                                <a href="{{ route('orders.manage.edit', $order) }}" class="btn btn-sm btn-pf-outline">Edit</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <div>No orders match this filter.</div>
                            @if ($filters['type'] !== '' || ($filters['status'] !== 'open' && $filters['status'] !== 'all') || $filters['q'] !== '')
                                <a href="{{ route('orders.manage.index', ['status' => 'open']) }}" class="btn btn-sm btn-pf-outline mt-2">
                                    Clear filters (show all open orders)
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('styles')
<style>
.pf-order-flow {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.35rem;
}
.pf-order-flow-step {
    display: inline-flex;
    flex-direction: column;
    gap: 0.15rem;
    padding: 0.65rem 0.9rem;
    border-radius: 0.85rem;
    border: 1px solid var(--dash-border);
    background: #fff;
    text-decoration: none;
    color: var(--dash-text);
    min-width: 96px;
}
.pf-order-flow-step span { font-size: 0.75rem; color: var(--dash-muted); }
.pf-order-flow-step strong { font-size: 1.1rem; }
.pf-order-flow-step.active {
    border-color: var(--dash-primary);
    background: rgba(230, 57, 70, 0.08);
    color: var(--dash-primary);
}
.pf-order-flow-arrow { color: #9CA3AF; }
.pf-live-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #22c55e; display: inline-block;
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.5);
    animation: pf-live-pulse 1.6s infinite;
}
@keyframes pf-live-pulse {
    0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.45); }
    70% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
    100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('ordersFilterForm');
    const search = document.getElementById('orderSearch');
    const status = document.getElementById('orderStatusFilter');
    const type = document.getElementById('orderTypeFilter');
    let searchTimer = null;

    status?.addEventListener('change', () => form?.submit());
    type?.addEventListener('change', () => form?.submit());
    search?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => form?.submit(), 400);
    });
    search?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimer);
            form?.submit();
        }
    });

    const pollUrl = @json($pollUrl);
    let stamp = null;

    async function poll() {
        try {
            const res = await fetch(pollUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();
            const openEl = document.getElementById('ordersOpenCount');
            if (openEl) openEl.textContent = data.open;
            if (stamp !== null && data.stamp !== stamp) {
                window.location.reload();
                return;
            }
            stamp = data.stamp;
        } catch (e) {}
    }

    poll();
    setInterval(poll, 8000);
});
</script>
@endpush

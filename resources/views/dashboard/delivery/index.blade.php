@extends('layouts.dashboard')

@section('title', 'Delivery & Dispatch')
@section('page_title', 'Delivery')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="pf-dash-heading">Delivery & Dispatch</h2>
        <p class="pf-dash-sub">
            @if ($isDriver)
                Your assigned routes, drop-off details, and delivery instructions.
            @else
                Assign drivers by location and workload, then track active deliveries.
            @endif
        </p>
    </div>
</div>

@if (session('error'))
    <div class="alert alert-danger pf-alert">{{ session('error') }}</div>
@endif
@if (session('success'))
    <div class="alert alert-success pf-alert">{{ session('success') }}</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-orange"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <span>{{ $isDriver ? 'MY ACTIVE' : 'DISPATCH QUEUE' }}</span>
                <strong>{{ $isDriver ? $stats['active'] : $stats['queue'] }}</strong>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-red"><i class="bi bi-truck"></i></div>
            <div>
                <span>{{ $isDriver ? 'OUT NOW' : 'OUT FOR DELIVERY' }}</span>
                <strong>{{ $stats['out'] }}</strong>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-gold"><i class="bi {{ $isDriver ? 'bi-check2-circle' : 'bi-people' }}"></i></div>
            <div>
                <span>{{ $isDriver ? 'DONE TODAY' : 'ACTIVE DRIVERS' }}</span>
                <strong>{{ $isDriver ? $completedToday : $stats['drivers'] }}</strong>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-red"><i class="bi bi-box-seam"></i></div>
            <div>
                <span>{{ $isDriver ? 'MY DELIVERED' : 'DELIVERED' }}</span>
                <strong>{{ $stats['delivered'] ?? 0 }}</strong>
            </div>
        </div>
    </div>
</div>

@if ($isDriver)
    <div class="pf-dash-panel mb-4 p-2">
        <ul class="nav nav-pills pf-dash-pills" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'mine' ? 'active' : '' }}" href="{{ route('delivery.index', ['tab' => 'mine']) }}">
                    <i class="bi bi-truck me-1"></i> My Active
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'delivered' ? 'active' : '' }}" href="{{ route('delivery.index', ['tab' => 'delivered']) }}">
                    <i class="bi bi-check2-circle me-1"></i> Delivered
                </a>
            </li>
        </ul>
    </div>

    @if ($tab === 'delivered')
        <div class="pf-dash-panel">
            <div class="pf-dash-panel-head">
                <h3>My completed deliveries</h3>
                <span class="text-muted small">{{ $deliveredOrders->count() }} shown</span>
            </div>
            <div class="table-responsive">
                <table class="table pf-dash-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Drop-off</th>
                            <th>Delivered</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($deliveredOrders as $order)
                            <tr>
                                <td>
                                    <strong>{{ $order->order_number }}</strong>
                                    <div class="text-muted small">{{ $order->itemSummary() }}</div>
                                </td>
                                <td>
                                    <div>{{ $order->customer_name ?: '—' }}</div>
                                    <div class="text-muted small">{{ $order->customer_phone ?: '—' }}</div>
                                </td>
                                <td>{{ $order->destinationLabel() }}</td>
                                <td>
                                    <span class="badge text-bg-success">Delivered</span>
                                    <div class="text-muted small">
                                        {{ optional($order->delivered_at)->diffForHumans() ?? '—' }}
                                    </div>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('delivery.show', $order) }}" class="btn btn-sm btn-pf-outline">Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No delivered orders yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
    <div class="pf-dash-panel">
        <div class="pf-dash-panel-head">
            <h3>My Delivery Routes</h3>
            <span class="text-muted small">{{ $myDeliveries->count() }} assigned</span>
        </div>

        @forelse ($myDeliveries as $order)
            <div class="pf-delivery-card border-top p-3 p-md-4">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                    <div>
                        <strong class="fs-5">{{ $order->order_number }}</strong>
                        <span class="badge text-bg-{{ $order->statusTone() }} ms-2">{{ $order->statusLabel() }}</span>
                    </div>
                    <div class="text-muted small">
                        @if ($order->route_eta_minutes)
                            ETA ~{{ $order->route_eta_minutes }} min
                            @if ($order->route_distance_km)
                                · {{ number_format($order->route_distance_km, 1) }} km
                            @endif
                        @endif
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted mb-1"><i class="bi bi-geo-alt me-1"></i>Drop-off</div>
                        <div class="fw-semibold">{{ $order->destinationLabel() }}</div>
                        @if ($order->delivery_landmark)
                            <div class="text-muted small">Landmark: {{ $order->delivery_landmark }}</div>
                        @endif
                        <div class="mt-2 small">
                            <i class="bi bi-person me-1"></i>{{ $order->customer_name ?: 'Customer' }}
                            @if ($order->customer_phone)
                                · <a href="tel:{{ $order->customer_phone }}">{{ $order->customer_phone }}</a>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted mb-1"><i class="bi bi-chat-left-text me-1"></i>Instructions</div>
                        <div>{{ $order->instructionsText() }}</div>
                        <div class="small text-muted mt-2">{{ $order->itemSummary() }}</div>
                        @if ($order->route_summary)
                            <div class="small text-muted mt-1"><i class="bi bi-signpost-2 me-1"></i>{{ $order->route_summary }}</div>
                        @endif
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a href="{{ route('delivery.show', $order) }}" class="btn btn-sm btn-pf-outline">
                        <i class="bi bi-map me-1"></i> Route details
                    </a>
                    <a href="{{ $order->mapsUrl() }}" target="_blank" rel="noopener" class="btn btn-sm btn-pf-outline">
                        <i class="bi bi-compass me-1"></i> Open maps
                    </a>
                    @if ($order->status !== 'out_for_delivery')
                        <form method="POST" action="{{ route('delivery.start', $order) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-pf-primary">
                                <i class="bi bi-play-fill me-1"></i> Start delivery
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('delivery.complete', $order) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-pf-primary"
                                    onclick="return confirm('Confirm this order was delivered?')">
                                <i class="bi bi-check2-circle me-1"></i> Mark delivered
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-4 text-center text-muted">
                No active deliveries assigned to you right now.
            </div>
        @endforelse
    </div>
    @endif
@else
    <div class="pf-dash-panel mb-4 p-2">
        <ul class="nav nav-pills pf-dash-pills" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'queue' ? 'active' : '' }}" href="{{ route('delivery.index', ['tab' => 'queue']) }}">
                    <i class="bi bi-inbox me-1"></i> Dispatch Queue
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'active' ? 'active' : '' }}" href="{{ route('delivery.index', ['tab' => 'active']) }}">
                    <i class="bi bi-truck me-1"></i> Active Deliveries
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'delivered' ? 'active' : '' }}" href="{{ route('delivery.index', ['tab' => 'delivered']) }}">
                    <i class="bi bi-check2-circle me-1"></i> Delivered
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'drivers' ? 'active' : '' }}" href="{{ route('delivery.index', ['tab' => 'drivers']) }}">
                    <i class="bi bi-person-badge me-1"></i> Drivers
                </a>
            </li>
        </ul>
    </div>

    @if ($tab === 'queue')
        <div class="pf-dash-panel">
            <div class="pf-dash-panel-head">
                <h3>Orders awaiting driver assignment</h3>
                <span class="text-muted small">Matched by city/zone and current workload</span>
            </div>
            <div class="table-responsive">
                <table class="table pf-dash-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Location</th>
                            <th>Customer</th>
                            <th>Suggested driver</th>
                            <th class="text-end">Assign</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($queueOrders as $order)
                            @php
                                $suggested = $suggestedDrivers[(string) $order->_id] ?? null;
                                $route = \App\Support\DeliveryDispatch::estimateRoute($order->delivery_city, $order->delivery_address);
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $order->order_number }}</strong>
                                    <div class="text-muted small">{{ $order->statusLabel() }} · {{ optional($order->placed_at)->diffForHumans() }}</div>
                                    <div class="text-muted small">{{ $order->itemSummary() }}</div>
                                </td>
                                <td>
                                    <div>{{ $order->destinationLabel() }}</div>
                                    <div class="text-muted small">~{{ number_format($route['distance_km'], 1) }} km · ~{{ $route['eta_minutes'] }} min</div>
                                    @if ($order->instructionsText() !== 'No special instructions')
                                        <div class="small mt-1"><i class="bi bi-info-circle me-1"></i>{{ \Illuminate\Support\Str::limit($order->instructionsText(), 60) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $order->customer_name ?: '—' }}</div>
                                    <div class="text-muted small">{{ $order->customer_phone ?: '—' }}</div>
                                </td>
                                <td>
                                    @if ($suggested)
                                        <strong>{{ $suggested->name }}</strong>
                                        <div class="text-muted small">
                                            Zone: {{ $driverLoads[(string) $suggested->_id]['zone'] ?? '—' }}
                                            · {{ $driverLoads[(string) $suggested->_id]['active_count'] ?? 0 }} active
                                        </div>
                                    @else
                                        <span class="text-muted">No drivers online</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('delivery.assign', $order) }}" class="d-inline-flex flex-wrap gap-2 justify-content-end">
                                        @csrf
                                        <select name="driver_id" class="form-select form-select-sm pf-input" style="min-width: 160px;" required>
                                            <option value="">Select driver</option>
                                            @foreach ($drivers as $driver)
                                                <option value="{{ $driver->_id }}" @selected($suggested && (string) $driver->_id === (string) $suggested->_id)>
                                                    {{ $driver->name }}
                                                    ({{ $driverLoads[(string) $driver->_id]['active_count'] ?? 0 }} active)
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-pf-primary">Assign</button>
                                    </form>
                                    <form method="POST" action="{{ route('delivery.auto-assign', $order) }}" class="mt-2">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-pf-outline" @disabled($drivers->isEmpty())>
                                            <i class="bi bi-magic me-1"></i> Auto-assign
                                        </button>
                                    </form>
                                    <a href="{{ route('delivery.show', $order) }}" class="btn btn-sm btn-link">Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Dispatch queue is clear.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif ($tab === 'active')
        <div class="pf-dash-panel">
            <div class="pf-dash-panel-head">
                <h3>Active deliveries</h3>
                <span class="text-muted small">{{ $activeDeliveries->count() }} in progress</span>
            </div>
            <div class="table-responsive">
                <table class="table pf-dash-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Driver</th>
                            <th>Route</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activeDeliveries as $order)
                            <tr>
                                <td>
                                    <strong>{{ $order->order_number }}</strong>
                                    <div class="text-muted small">{{ $order->customer_name ?: 'Customer' }}</div>
                                </td>
                                <td>{{ optional($order->driver)->name ?? '—' }}</td>
                                <td>
                                    <div>{{ $order->destinationLabel() }}</div>
                                    <div class="text-muted small">
                                        @if ($order->route_eta_minutes)
                                            ~{{ $order->route_eta_minutes }} min
                                        @endif
                                        @if ($order->route_distance_km)
                                            · {{ number_format($order->route_distance_km, 1) }} km
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $order->statusTone() }}">{{ $order->statusLabel() }}</span>
                                    @if ($order->assigned_at)
                                        <div class="text-muted small">Assigned {{ $order->assigned_at->diffForHumans() }}</div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('delivery.show', $order) }}" class="btn btn-sm btn-pf-outline">Route</a>
                                    <form method="POST" action="{{ route('delivery.unassign', $order) }}" class="d-inline"
                                          onsubmit="return confirm('Unassign this driver?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Unassign</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No active deliveries.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif ($tab === 'delivered')
        <div class="pf-dash-panel">
            <div class="pf-dash-panel-head">
                <h3>Delivered orders</h3>
                <span class="text-muted small">{{ $deliveredOrders->count() }} recent · {{ $stats['delivered'] ?? 0 }} total</span>
            </div>
            <div class="table-responsive">
                <table class="table pf-dash-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Driver</th>
                            <th>Drop-off</th>
                            <th>Delivered at</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($deliveredOrders as $order)
                            <tr>
                                <td>
                                    <strong>{{ $order->order_number }}</strong>
                                    <div class="text-muted small">{{ \Illuminate\Support\Str::limit($order->itemSummary(), 40) }}</div>
                                </td>
                                <td>
                                    <div>{{ $order->customer_name ?: '—' }}</div>
                                    <div class="text-muted small">{{ $order->customer_phone ?: '—' }}</div>
                                </td>
                                <td>{{ optional($order->driver)->name ?? '—' }}</td>
                                <td>
                                    <div>{{ $order->destinationLabel() }}</div>
                                    @if ($order->route_distance_km)
                                        <div class="text-muted small">{{ number_format($order->route_distance_km, 1) }} km</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge text-bg-success">Delivered</span>
                                    <div class="text-muted small">
                                        {{ optional($order->delivered_at)->format('M j, g:i A') ?? '—' }}
                                    </div>
                                    <div class="text-muted small">{{ optional($order->delivered_at)->diffForHumans() }}</div>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('delivery.show', $order) }}" class="btn btn-sm btn-pf-outline">Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No delivered orders yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div class="text-muted small">Zone coverage and current load</div>
            <button type="button" class="btn btn-pf-primary" data-bs-toggle="modal" data-bs-target="#addDriverModal">
                <i class="bi bi-plus-lg me-1"></i> Add Driver
            </button>
        </div>

        <div class="pf-dash-panel">
            <div class="pf-dash-panel-head">
                <h3>Driver availability</h3>
                <span class="text-muted small">{{ $drivers->count() }} drivers</span>
            </div>
            <div class="table-responsive">
                <table class="table pf-dash-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Driver</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Service zone</th>
                            <th>Active deliveries</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($drivers as $driver)
                            @php $load = $driverLoads[(string) $driver->_id] ?? ['active_count' => 0, 'zone' => '—']; @endphp
                            <tr>
                                <td><strong>{{ $driver->name }}</strong></td>
                                <td class="text-muted small">{{ $driver->email }}</td>
                                <td>{{ $driver->phone ?: '—' }}</td>
                                <td>{{ $load['zone'] }}</td>
                                <td>
                                    <span class="badge {{ $load['active_count'] === 0 ? 'text-bg-success' : 'text-bg-warning' }}">
                                        {{ $load['active_count'] }} active
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No active delivery drivers found. Click <strong>Add Driver</strong> to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Add Driver Modal --}}
        <div class="modal fade" id="addDriverModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="{{ route('delivery.drivers.store') }}" class="modal-content" id="addDriverForm" novalidate>
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Delivery Driver</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body d-flex flex-column gap-3">
                        @if ($errors->any() && old('_form') === 'add_driver')
                            <div class="alert alert-danger pf-alert mb-0">
                                Please fix the highlighted fields and try again.
                            </div>
                        @endif
                        <input type="hidden" name="_form" value="add_driver">
                        <div>
                            <label class="form-label pf-required" for="driver_name">Full name</label>
                            <input type="text" name="name" id="driver_name"
                                   class="form-control pf-input @error('name') is-invalid @enderror"
                                   value="{{ old('_form') === 'add_driver' ? old('name') : '' }}"
                                   required minlength="2" maxlength="120" autocomplete="name">
                            @error('name')<div class="pf-field-error">{{ $message }}</div>@enderror
                            <div class="invalid-feedback">Enter a valid name (letters only, min 2 characters).</div>
                        </div>
                        <div>
                            <label class="form-label pf-required" for="driver_email">Email</label>
                            <input type="email" name="email" id="driver_email"
                                   class="form-control pf-input @error('email') is-invalid @enderror"
                                   value="{{ old('_form') === 'add_driver' ? old('email') : '' }}"
                                   required maxlength="255" autocomplete="email">
                            @error('email')<div class="pf-field-error">{{ $message }}</div>@enderror
                            <div class="invalid-feedback">Enter a valid email address.</div>
                        </div>
                        <div>
                            <label class="form-label pf-required" for="driver_phone">Phone</label>
                            <input type="tel" name="phone" id="driver_phone"
                                   class="form-control pf-input @error('phone') is-invalid @enderror"
                                   value="{{ old('_form') === 'add_driver' ? old('phone') : '' }}"
                                   required maxlength="15" inputmode="tel"
                                   placeholder="0771234567 or +94771234567" autocomplete="tel">
                            @error('phone')<div class="pf-field-error">{{ $message }}</div>@enderror
                            <div class="invalid-feedback">Use a Sri Lankan number (0771234567 or +94771234567).</div>
                        </div>
                        <div>
                            <label class="form-label pf-required" for="driver_zone">Service zone / city</label>
                            <input type="text" name="service_zone" id="driver_zone"
                                   class="form-control pf-input @error('service_zone') is-invalid @enderror"
                                   value="{{ old('_form') === 'add_driver' ? old('service_zone', 'Colombo') : 'Colombo' }}"
                                   required minlength="2" maxlength="80"
                                   placeholder="Colombo, Nugegoda, Dehiwala…">
                            @error('service_zone')<div class="pf-field-error">{{ $message }}</div>@enderror
                            <div class="form-text">Used to auto-match nearby delivery orders.</div>
                            <div class="invalid-feedback">Enter a valid city/zone name.</div>
                        </div>
                        <div>
                            <label class="form-label pf-required" for="driver_password">Password</label>
                            <input type="password" name="password" id="driver_password"
                                   class="form-control pf-input @error('password') is-invalid @enderror"
                                   required minlength="8" autocomplete="new-password">
                            @error('password')<div class="pf-field-error">{{ $message }}</div>@enderror
                            <div class="form-text">At least 8 characters, with letters and numbers.</div>
                            <div class="invalid-feedback">Password must be at least 8 characters with letters and numbers.</div>
                        </div>
                        <div>
                            <label class="form-label pf-required" for="driver_password_confirmation">Confirm password</label>
                            <input type="password" name="password_confirmation" id="driver_password_confirmation"
                                   class="form-control pf-input" required minlength="8" autocomplete="new-password">
                            <div class="invalid-feedback">Passwords must match.</div>
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="driver_is_active" class="form-check-input"
                                   value="1" @checked(old('_form') !== 'add_driver' || old('is_active', '1') == '1')>
                            <label class="form-check-label" for="driver_is_active">Active (can receive assignments)</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-pf-primary" id="addDriverSubmit">Add Driver</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endif
@endsection

@if (! $isDriver && $tab === 'drivers')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('addDriverModal');
    const form = document.getElementById('addDriverForm');
    if (!modalEl || !form) return;

    @if ($errors->any() && old('_form') === 'add_driver')
    if (window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
    @endif

    const phoneRe = /^(\+94|0)?[1-9]\d{8}$/;
    const nameRe = /^[\p{L}\s'\-.]+$/u;
    const passRe = /^(?=.*[A-Za-z])(?=.*\d).{8,}$/;

    function setInvalid(input, invalid) {
        input.classList.toggle('is-invalid', invalid);
        return !invalid;
    }

    form.addEventListener('submit', function (e) {
        let ok = true;
        const name = form.name;
        const email = form.email;
        const phone = form.phone;
        const zone = form.service_zone;
        const password = form.password;
        const confirm = form.password_confirmation;

        ok = setInvalid(name, !nameRe.test(name.value.trim()) || name.value.trim().length < 2) && ok;
        ok = setInvalid(email, !email.value.trim() || !email.checkValidity()) && ok;
        ok = setInvalid(phone, !phoneRe.test(phone.value.replace(/\s+/g, ''))) && ok;
        ok = setInvalid(zone, !nameRe.test(zone.value.trim()) || zone.value.trim().length < 2) && ok;
        ok = setInvalid(password, !passRe.test(password.value)) && ok;
        ok = setInvalid(confirm, confirm.value !== password.value || !confirm.value) && ok;

        if (!ok) {
            e.preventDefault();
            e.stopPropagation();
        }
    });
});
</script>
@endpush
@endif

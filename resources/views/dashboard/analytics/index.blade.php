@extends('layouts.dashboard')

@section('title', 'Analytics & Reports')
@section('page_title', 'Analytics')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="pf-dash-heading">Sales Reports & Analytics</h2>
        <p class="pf-dash-sub">View daily revenue, total orders, and download reports.</p>
    </div>
    <a href="{{ route('admin.analytics.export') }}" class="btn btn-pf-primary">
        <i class="bi bi-download me-1"></i> Download PDF Report
    </a>
</div>

<ul class="nav nav-tabs mb-4" id="analyticsTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales" type="button" role="tab" aria-controls="sales" aria-selected="true">Sales & Revenue</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab" aria-controls="orders" aria-selected="false">Order Analytics</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="products-tab" data-bs-toggle="tab" data-bs-target="#products" type="button" role="tab" aria-controls="products" aria-selected="false">Product Performance</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="customers-tab" data-bs-toggle="tab" data-bs-target="#customers" type="button" role="tab" aria-controls="customers" aria-selected="false">Customer Analytics</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="delivery-tab" data-bs-toggle="tab" data-bs-target="#delivery" type="button" role="tab" aria-controls="delivery" aria-selected="false">Delivery & Operations</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="marketing-tab" data-bs-toggle="tab" data-bs-target="#marketing" type="button" role="tab" aria-controls="marketing" aria-selected="false">Marketing</button>
    </li>
</ul>

<div class="tab-content" id="analyticsTabsContent">
    <div class="tab-pane fade show active" id="sales" role="tabpanel" aria-labelledby="sales-tab">
        
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="pf-dash-summary h-100">
                    <div class="pf-dash-summary-icon tone-green"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <span>TOTAL REVENUE</span>
                        <strong>Rs. {{ number_format($totalRevenue) }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="pf-dash-summary h-100">
                    <div class="pf-dash-summary-icon tone-blue"><i class="bi bi-graph-up"></i></div>
                    <div>
                        <span>AVG ORDER VALUE</span>
                        <strong>Rs. {{ number_format($averageOrderValue, 2) }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="pf-dash-summary h-100">
                    <div class="pf-dash-summary-icon tone-purple"><i class="bi bi-credit-card"></i></div>
                    <div>
                        <span>PAYMENT METHODS</span>
                        <div class="small fw-semibold mt-1" style="font-size: 0.85rem; line-height: 1.2;">
                            @forelse($paymentMethodsBreakdown as $method)
                                <div class="text-truncate">{{ $method->_id ?: 'Unknown' }}: {{ $method->count }}</div>
                            @empty
                                <div>No data available</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="pf-dash-summary h-100">
                    <div class="pf-dash-summary-icon tone-red"><i class="bi bi-arrow-return-left"></i></div>
                    <div>
                        <span>REFUNDS & CANCELLATIONS</span>
                        <strong>{{ $refundsAndCancellationsCount }} orders</strong>
                        <div class="text-danger small fw-semibold mt-1" style="font-size: 0.85rem;">Lost: Rs. {{ number_format($refundsAndCancellationsTotal) }}</div>
                    </div>
                </div>
            </div>
        </div>

<div class="pf-dash-panel">
    <div class="pf-dash-panel-head">
        <h3>Last 30 Days Daily Revenue</h3>
        <span class="text-muted small">Overview of recent sales</span>
    </div>
    <div class="table-responsive">
        <table class="table pf-dash-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Number of Orders</th>
                    <th class="text-end">Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dailyRevenue as $day)
                    <tr>
                        <td>
                            <strong>{{ \Carbon\Carbon::parse($day->_id)->format('M d, Y') }}</strong>
                        </td>
                        <td>{{ $day->order_count }}</td>
                        <td class="text-end fw-semibold text-success">Rs. {{ number_format($day->total_revenue) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">
                            No revenue data found for the last 30 days.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="row g-3 mt-4">
    <div class="col-md-4">
        <div class="pf-dash-panel h-100" data-bs-toggle="modal" data-bs-target="#topPizzasModal" style="cursor: pointer; transition: box-shadow 0.3s ease;" onmouseover="this.classList.add('shadow-lg')" onmouseout="this.classList.remove('shadow-lg')">
            <div class="pf-dash-panel-head">
                <h3>Top Selling Pizzas</h3>
                <span class="text-muted small">Most ordered items</span>
            </div>
            <ul class="list-group list-group-flush mt-3">
                @forelse ($topPizzas as $pizza)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        {{ $pizza->_id }}
                        <span class="badge text-bg-primary rounded-pill">{{ $pizza->total_quantity }} sold</span>
                    </li>
                @empty
                    <li class="list-group-item px-0 text-muted">No data available</li>
                @endforelse
            </ul>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="pf-dash-panel h-100" data-bs-toggle="modal" data-bs-target="#statusBreakdownModal" style="cursor: pointer; transition: box-shadow 0.3s ease;" onmouseover="this.classList.add('shadow-lg')" onmouseout="this.classList.remove('shadow-lg')">
            <div class="pf-dash-panel-head">
                <h3>Order Status Breakdown</h3>
                <span class="text-muted small">Pending vs Paid</span>
            </div>
            <div class="mt-3 d-flex flex-column gap-3">
                @forelse ($statusBreakdown as $status)
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-semibold">{{ $status->_id }}</span>
                            <span>{{ $status->count }} orders</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar {{ $status->_id === 'Paid' ? 'bg-success' : ($status->_id === 'Pending' ? 'bg-warning' : 'bg-secondary') }}" 
                                 role="progressbar" 
                                 style="width: {{ $totalOrdersCount > 0 ? ($status->count / $totalOrdersCount) * 100 : 0 }}%" 
                                 aria-valuenow="{{ $status->count }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="{{ $totalOrdersCount }}"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">No data available</p>
                @endforelse
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="pf-dash-panel h-100" data-bs-toggle="modal" data-bs-target="#topCustomersModal" style="cursor: pointer; transition: box-shadow 0.3s ease;" onmouseover="this.classList.add('shadow-lg')" onmouseout="this.classList.remove('shadow-lg')">
            <div class="pf-dash-panel-head">
                <h3>Recent Top Customers</h3>
                <span class="text-muted small">By total spent</span>
            </div>
            <ul class="list-group list-group-flush mt-3">
                @forelse ($topCustomers as $customer)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        {{ $customer->_id ?: 'Guest' }}
                        <span class="fw-semibold text-success">Rs. {{ number_format($customer->total_spent) }}</span>
                    </li>
                @empty
                    <li class="list-group-item px-0 text-muted">No data available</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

    </div>
    
    <div class="tab-pane fade" id="orders" role="tabpanel" aria-labelledby="orders-tab">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="pf-dash-summary h-100" data-bs-toggle="modal" data-bs-target="#todayOrdersModal" style="cursor: pointer; transition: box-shadow 0.3s ease;" onmouseover="this.classList.add('shadow-lg')" onmouseout="this.classList.remove('shadow-lg')">
                    <div class="pf-dash-summary-icon tone-blue"><i class="bi bi-basket"></i></div>
                    <div>
                        <span>TODAY ORDER VOLUME</span>
                        <strong>{{ number_format($todayOrderVolume) }}</strong>
                        <div class="text-muted small mt-1" style="font-size: 0.85rem;">All-Time: {{ number_format($totalOrdersCount) }} orders</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-dash-summary h-100" data-bs-toggle="modal" data-bs-target="#peakHoursModal" style="cursor: pointer; transition: box-shadow 0.3s ease;" onmouseover="this.classList.add('shadow-lg')" onmouseout="this.classList.remove('shadow-lg')">
                    <div class="pf-dash-summary-icon tone-purple"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <span>PEAK ORDER HOUR</span>
                        <strong>{{ $peakOrderHour }}</strong>
                        <div class="text-muted small mt-1" style="font-size: 0.85rem;">Busiest time of the day</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-dash-summary h-100" data-bs-toggle="modal" data-bs-target="#statusBreakdownModal" style="cursor: pointer; transition: box-shadow 0.3s ease;" onmouseover="this.classList.add('shadow-lg')" onmouseout="this.classList.remove('shadow-lg')">
                    <div class="pf-dash-summary-icon tone-green"><i class="bi bi-activity"></i></div>
                    <div class="w-100">
                        <span>ORDER STATUS TRACKING</span>
                        <div class="mt-2 d-flex flex-column gap-2">
                            @forelse($statusBreakdown as $status)
                                <div>
                                    <div class="d-flex justify-content-between mb-1" style="font-size: 0.8rem; font-weight: 600;">
                                        <span>{{ $status->_id }}</span>
                                        <span>{{ $status->count }}</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar {{ $status->_id === 'Paid' || $status->_id === 'Delivered' ? 'bg-success' : ($status->_id === 'Pending' ? 'bg-warning text-dark' : 'bg-primary') }}" 
                                             role="progressbar" 
                                             style="width: {{ $totalOrdersCount > 0 ? ($status->count / $totalOrdersCount) * 100 : 0 }}%" 
                                             aria-valuenow="{{ $status->count }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="{{ $totalOrdersCount }}"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted small">No data available</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="products" role="tabpanel" aria-labelledby="products-tab">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="pf-dash-summary h-100" data-bs-toggle="modal" data-bs-target="#bestSellersModal" style="cursor: pointer; transition: box-shadow 0.3s ease;" onmouseover="this.classList.add('shadow-lg')" onmouseout="this.classList.remove('shadow-lg')">
                    <div class="pf-dash-summary-icon tone-blue"><i class="bi bi-star-fill"></i></div>
                    <div>
                        <span>BEST SELLERS</span>
                        <strong>{{ $bestSellers->first()->_id ?? 'N/A' }}</strong>
                        <div class="text-muted small mt-1" style="font-size: 0.85rem;">Top performing item</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-dash-summary h-100" data-bs-toggle="modal" data-bs-target="#slowMoversModal" style="cursor: pointer; transition: box-shadow 0.3s ease;" onmouseover="this.classList.add('shadow-lg')" onmouseout="this.classList.remove('shadow-lg')">
                    <div class="pf-dash-summary-icon tone-purple"><i class="bi bi-arrow-down-circle"></i></div>
                    <div>
                        <span>SLOW MOVERS</span>
                        <strong>{{ $slowMovers->first()->_id ?? 'N/A' }}</strong>
                        <div class="text-muted small mt-1" style="font-size: 0.85rem;">Lowest performing item</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-dash-summary h-100">
                    <div class="pf-dash-summary-icon tone-green"><i class="bi bi-box-seam"></i></div>
                    <div>
                        <span>TOTAL PRODUCTS SOLD</span>
                        <strong>{{ number_format($totalProductsSold) }}</strong>
                        <div class="text-muted small mt-1" style="font-size: 0.85rem;">Across all paid orders</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="customers" role="tabpanel" aria-labelledby="customers-tab">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="pf-dash-summary h-100">
                    <div class="pf-dash-summary-icon tone-blue"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <span>TOTAL UNIQUE CUSTOMERS</span>
                        <strong>{{ number_format($totalUniqueCustomers) }}</strong>
                        <div class="text-muted small mt-1" style="font-size: 0.85rem;">Registered & Guest</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-dash-summary h-100">
                    <div class="pf-dash-summary-icon tone-purple"><i class="bi bi-person-check-fill"></i></div>
                    <div class="w-100">
                        <span>CUSTOMER RETENTION</span>
                        <div class="mt-2 d-flex flex-column gap-2">
                            <div>
                                <div class="d-flex justify-content-between mb-1" style="font-size: 0.8rem; font-weight: 600;">
                                    <span>New (1 order)</span>
                                    <span>{{ $newCustomers }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $totalUniqueCustomers > 0 ? ($newCustomers / $totalUniqueCustomers) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="d-flex justify-content-between mb-1" style="font-size: 0.8rem; font-weight: 600;">
                                    <span>Returning (2+ orders)</span>
                                    <span>{{ $returningCustomers }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $totalUniqueCustomers > 0 ? ($returningCustomers / $totalUniqueCustomers) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-dash-summary h-100" data-bs-toggle="modal" data-bs-target="#topCustomersModal" style="cursor: pointer; transition: box-shadow 0.3s ease;" onmouseover="this.classList.add('shadow-lg')" onmouseout="this.classList.remove('shadow-lg')">
                    <div class="pf-dash-summary-icon tone-green"><i class="bi bi-award-fill"></i></div>
                    <div>
                        <span>TOP CUSTOMER</span>
                        <strong>{{ $topCustomers->first()->_id ?? 'N/A' }}</strong>
                        <div class="text-muted small mt-1" style="font-size: 0.85rem;">Highest all-time spender</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="delivery" role="tabpanel" aria-labelledby="delivery-tab">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="pf-dash-summary h-100">
                    <div class="pf-dash-summary-icon tone-blue"><i class="bi bi-stopwatch"></i></div>
                    <div>
                        <span>AVG. PROCESSING TIME</span>
                        <strong>{{ number_format($avgProcessingTime) }} mins</strong>
                        <div class="text-muted small mt-1" style="font-size: 0.85rem;">From order to delivery</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-dash-summary h-100" data-bs-toggle="modal" data-bs-target="#topLocationsModal" style="cursor: pointer; transition: box-shadow 0.3s ease;" onmouseover="this.classList.add('shadow-lg')" onmouseout="this.classList.remove('shadow-lg')">
                    <div class="pf-dash-summary-icon tone-purple"><i class="bi bi-geo-alt-fill"></i></div>
                    <div>
                        <span>TOP DELIVERY AREA</span>
                        <strong>{{ collect($topLocations)->first()->_id ?? 'N/A' }}</strong>
                        <div class="text-muted small mt-1" style="font-size: 0.85rem;">Highest order volume</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-dash-summary h-100">
                    <div class="pf-dash-summary-icon tone-green"><i class="bi bi-bicycle"></i></div>
                    <div>
                        <span>ACTIVE OPERATIONS</span>
                        @php
                            $pendingStatus = collect($statusBreakdown)->firstWhere('_id', 'Pending');
                            $pendingCount = $pendingStatus ? $pendingStatus->count : 0;
                        @endphp
                        <strong>{{ number_format($pendingCount) }}</strong>
                        <div class="text-muted small mt-1" style="font-size: 0.85rem;">Orders currently pending</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="marketing" role="tabpanel" aria-labelledby="marketing-tab">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="pf-dash-summary h-100">
                    <div class="pf-dash-summary-icon tone-blue"><i class="bi bi-tag-fill"></i></div>
                    <div>
                        <span>ORDERS WITH PROMO</span>
                        <strong>{{ number_format($ordersWithPromo) }}</strong>
                        <div class="text-muted small mt-1" style="font-size: 0.85rem;">Discounted orders</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-dash-summary h-100">
                    <div class="pf-dash-summary-icon tone-purple"><i class="bi bi-percent"></i></div>
                    <div>
                        <span>TOTAL DISCOUNT GIVEN</span>
                        <strong>Rs. {{ number_format($totalDiscountGiven) }}</strong>
                        <div class="text-muted small mt-1" style="font-size: 0.85rem;">Across all paid orders</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="pf-dash-summary h-100" data-bs-toggle="modal" data-bs-target="#promoCodesModal" style="cursor: pointer; transition: box-shadow 0.3s ease;" onmouseover="this.classList.add('shadow-lg')" onmouseout="this.classList.remove('shadow-lg')">
                    <div class="pf-dash-summary-icon tone-green"><i class="bi bi-star-fill"></i></div>
                    <div>
                        <span>TOP PROMO CODE</span>
                        <strong>{{ collect($topPromoCodes)->first()->_id ?? 'N/A' }}</strong>
                        <div class="text-muted small mt-1" style="font-size: 0.85rem;">Most frequently used</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Pizzas Modal -->
<div class="modal fade" id="topPizzasModal" tabindex="-1" aria-labelledby="topPizzasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="topPizzasModalLabel">Top Selling Pizzas (Detailed)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Pizza Name</th>
                                <th class="text-end">Total Quantity Sold</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topPizzas as $pizza)
                                <tr>
                                    <td>{{ $pizza->_id }}</td>
                                    <td class="text-end">{{ $pizza->total_quantity }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Breakdown Modal -->
<div class="modal fade" id="statusBreakdownModal" tabindex="-1" aria-labelledby="statusBreakdownModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusBreakdownModalLabel">Order Status Breakdown (Detailed)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th class="text-end">Number of Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($statusBreakdown as $status)
                                <tr>
                                    <td>
                                        <span class="badge {{ $status->_id === 'Paid' ? 'bg-success' : ($status->_id === 'Pending' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                            {{ $status->_id }}
                                        </span>
                                    </td>
                                    <td class="text-end">{{ $status->count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Customers Modal -->
<div class="modal fade" id="topCustomersModal" tabindex="-1" aria-labelledby="topCustomersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="topCustomersModalLabel">Recent Top Customers (Detailed)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Customer Name</th>
                                <th class="text-end">Total Spent</th>
                                <th class="text-end">Orders Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topCustomers as $customer)
                                <tr>
                                    <td>{{ $customer->_id ?: 'Guest' }}</td>
                                    <td class="text-end text-success fw-semibold">Rs. {{ number_format($customer->total_spent) }}</td>
                                    <td class="text-end">{{ $customer->order_count ?? 0 }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Today Orders Modal -->
<div class="modal fade" id="todayOrdersModal" tabindex="-1" aria-labelledby="todayOrdersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="todayOrdersModalLabel">Today's Orders (Detailed)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer Name</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Payment Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($todayOrdersList as $order)
                                <tr>
                                    <td>{{ $order->order_number ?? $order->_id }}</td>
                                    <td>{{ $order->customer_name ?: 'Guest' }}</td>
                                    <td class="text-end fw-semibold">Rs. {{ number_format($order->total) }}</td>
                                    <td class="text-end">
                                        <span class="badge {{ $order->payment_status === 'Paid' ? 'bg-success' : ($order->payment_status === 'Failed' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                            {{ $order->payment_status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No orders placed today.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Peak Hours Modal -->
<div class="modal fade" id="peakHoursModal" tabindex="-1" aria-labelledby="peakHoursModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="peakHoursModalLabel">Hourly Order Breakdown</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Time Block</th>
                                <th class="text-end">Total Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($hourlyBreakdown as $hour => $count)
                                <tr>
                                    <td>
                                        @php
                                            $h = (int)$hour;
                                            $nextH = $h + 1;
                                            if ($nextH === 24) $nextH = 0;
                                        @endphp
                                        {{ sprintf('%02d:00 - %02d:00', $h, $nextH) }}
                                    </td>
                                    <td class="text-end fw-semibold">{{ $count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">No data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Best Sellers Modal -->
<div class="modal fade" id="bestSellersModal" tabindex="-1" aria-labelledby="bestSellersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bestSellersModalLabel">Best Sellers (Detailed)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Pizza Name</th>
                                <th class="text-end">Total Quantity Sold</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bestSellers as $item)
                                <tr>
                                    <td>{{ $item->_id }}</td>
                                    <td class="text-end fw-semibold">{{ $item->total_quantity }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">No data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Slow Movers Modal -->
<div class="modal fade" id="slowMoversModal" tabindex="-1" aria-labelledby="slowMoversModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="slowMoversModalLabel">Slow Movers (Detailed)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Pizza Name</th>
                                <th class="text-end">Total Quantity Sold</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($slowMovers as $item)
                                <tr>
                                    <td>{{ $item->_id }}</td>
                                    <td class="text-end fw-semibold text-danger">{{ $item->total_quantity }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">No data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Top Locations Modal -->
<div class="modal fade" id="topLocationsModal" tabindex="-1" aria-labelledby="topLocationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="topLocationsModalLabel">Top Delivery Areas (Detailed)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Location Name</th>
                                <th class="text-end">Total Orders Delivered</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topLocations as $location)
                                <tr>
                                    <td>{{ $location->_id ?: 'Unknown' }}</td>
                                    <td class="text-end fw-semibold">{{ $location->order_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">No data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Promo Codes Modal -->
<div class="modal fade" id="promoCodesModal" tabindex="-1" aria-labelledby="promoCodesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="promoCodesModalLabel">Top Promo Codes (Detailed)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Promo Code</th>
                                <th class="text-end">Total Usage Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topPromoCodes as $promo)
                                <tr>
                                    <td><span class="badge bg-primary">{{ $promo->_id }}</span></td>
                                    <td class="text-end fw-semibold">{{ $promo->usage_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">No data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

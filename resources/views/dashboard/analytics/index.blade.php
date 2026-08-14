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

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-green"><i class="bi bi-cash-stack"></i></div>
            <div>
                <span>ALL-TIME REVENUE</span>
                <strong>Rs. {{ number_format($totalRevenueAllTime) }}</strong>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="pf-dash-summary">
            <div class="pf-dash-summary-icon tone-blue"><i class="bi bi-receipt"></i></div>
            <div>
                <span>TOTAL ORDERS</span>
                <strong>{{ number_format($totalOrdersCount) }}</strong>
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
@endsection

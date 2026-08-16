@extends('layouts.dashboard')

@section('title', 'Billing & Payment')
@section('page_title', 'Billing')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="pf-dash-heading">Billing & Payment</h2>
        <p class="pf-dash-sub">View recent transactions and handle billing details.</p>
    </div>
</div>

<div class="pf-dash-panel">
    <div class="pf-dash-panel-head">
        <h3>Transactions</h3>
        <span class="text-muted small">Recent billing activity</span>
    </div>

    <!-- 1. Create a responsive HTML table to display the orders -->
    <div class="table-responsive">
        <table class="table pf-dash-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Payment Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <!-- 2. Iterate over the orders array -->
                @forelse ($orders as $order)
                    <tr>
                        <td>
                            <strong>{{ $order->order_number }}</strong>
                        </td>
                        <td>{{ $order->customer_name }}</td>
                        <td>Rs. {{ number_format($order->total) }}</td>
                        <td>{{ $order->payment_method }}</td>
                        <td>
                            <!-- 3. Implement dynamic CSS badge classes for Payment Status -->
                            @if ($order->payment_status === 'Paid')
                                <span class="badge text-bg-success">Paid</span>
                            @elseif ($order->payment_status === 'Pending')
                                <span class="badge text-bg-warning">Pending</span>
                            @elseif ($order->payment_status === 'Failed' || $order->payment_status === 'Cancelled')
                                <span class="badge text-bg-danger">{{ $order->payment_status }}</span>
                            @else
                                <span class="badge text-bg-secondary">{{ $order->payment_status }}</span>
                            @endif
                        </td>
                        <td>{{ $order->placed_at->format('M d, Y h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- 4. Add Pagination links below the table container -->
    @if($orders->hasPages())
        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection

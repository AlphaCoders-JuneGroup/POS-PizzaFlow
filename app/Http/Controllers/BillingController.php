<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SharesDashboardData;
use App\Models\Order;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    use SharesDashboardData;

    public function index()
    {
        $orders = Order::orderBy('created_at', 'desc')->paginate(15);

        return view('dashboard.billing.index', array_merge(
            $this->dashboardData(),
            compact('orders')
        ));
    }
}

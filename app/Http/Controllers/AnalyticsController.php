<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Concerns\SharesDashboardData;
use App\Models\Order;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    use SharesDashboardData;

    public function index()
    {
        
        $dailyRevenue = Order::raw(function ($collection) {
            return $collection->aggregate([
                
                [
                    '$match' => [
                        'payment_status' => ['$ne' => 'Failed'] 
                    ]
                ],
                
                [
                    '$group' => [
                        '_id' => [
                            '$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$created_at']
                        ],
                        'total_revenue' => ['$sum' => '$total'],
                        'order_count' => ['$sum' => 1]
                    ]
                ],
                
                [
                    '$sort' => ['_id' => -1]
                ],
                
                [
                    '$limit' => 30
                ]
            ]);
        });

        
        $totalRevenueAllTime = Order::sum('total');
        $totalOrdersCount = Order::count();

        
        return view('dashboard.analytics.index', array_merge(
            $this->dashboardData(),
            compact('dailyRevenue', 'totalRevenueAllTime', 'totalOrdersCount')
        ));
    }

    public function exportPDF()
    {
        $dailyRevenue = Order::raw(function ($collection) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'payment_status' => ['$ne' => 'Failed'] 
                    ]
                ],
                [
                    '$group' => [
                        '_id' => [
                            '$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$created_at']
                        ],
                        'total_revenue' => ['$sum' => '$total'],
                        'order_count' => ['$sum' => 1]
                    ]
                ],
                [
                    '$sort' => ['_id' => -1]
                ],
                [
                    '$limit' => 30
                ]
            ]);
        });

        $totalRevenueAllTime = Order::sum('total');
        $totalOrdersCount = Order::count();

        $pdf = Pdf::loadView('dashboard.analytics.pdf', compact('dailyRevenue', 'totalRevenueAllTime', 'totalOrdersCount'));
        
        return $pdf->download('PizzaFlow_Sales_Report.pdf');
    }
}
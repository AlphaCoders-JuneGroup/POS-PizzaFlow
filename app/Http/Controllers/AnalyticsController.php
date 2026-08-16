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
                ['$match' => ['payment_status' => ['$ne' => 'Failed']]],
                ['$group' => [
                    '_id' => ['$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$created_at']],
                    'total_revenue' => ['$sum' => '$total'],
                    'order_count' => ['$sum' => 1]
                ]],
                ['$sort' => ['_id' => -1]],
                ['$limit' => 30]
            ]);
        });

        $topPizzas = Order::raw(function ($collection) {
            return $collection->aggregate([
                ['$match' => ['payment_status' => ['$ne' => 'Failed']]],
                ['$unwind' => '$items'],
                ['$group' => [
                    '_id' => '$items.base_name',
                    'total_quantity' => ['$sum' => '$items.qty'],
                ]],
                ['$sort' => ['total_quantity' => -1]],
                ['$limit' => 5]
            ]);
        });

        $statusBreakdown = Order::raw(function ($collection) {
            return $collection->aggregate([
                ['$group' => [
                    '_id' => '$payment_status',
                    'count' => ['$sum' => 1]
                ]],
                ['$sort' => ['count' => -1]]
            ]);
        });

        $topCustomers = Order::raw(function ($collection) {
            return $collection->aggregate([
                ['$match' => ['payment_status' => ['$ne' => 'Failed']]],
                ['$group' => [
                    '_id' => '$customer_name',
                    'total_spent' => ['$sum' => '$total'],
                    'order_count' => ['$sum' => 1]
                ]],
                ['$sort' => ['total_spent' => -1]],
                ['$limit' => 5]
            ]);
        });

        $totalRevenueAllTime = Order::sum('total');
        $totalOrdersCount = Order::count();

        $totalRevenue = Order::where('payment_status', '!=', 'Failed')->sum('total');
        $paidOrdersCount = Order::where('payment_status', '!=', 'Failed')->count();
        $averageOrderValue = $paidOrdersCount > 0 ? $totalRevenue / $paidOrdersCount : 0;

        $paymentMethodsBreakdown = Order::raw(function ($collection) {
            return $collection->aggregate([
                ['$group' => [
                    '_id' => '$payment_method',
                    'count' => ['$sum' => 1]
                ]]
            ]);
        });

        $refundsAndCancellationsCount = Order::whereIn('status', ['cancelled', 'refunded'])->count();
        $refundsAndCancellationsTotal = Order::whereIn('status', ['cancelled', 'refunded'])->sum('total');

        $todayOrderVolume = Order::where('created_at', '>=', \Carbon\Carbon::today())->count();
        $todayOrdersList = Order::where('created_at', '>=', \Carbon\Carbon::today())->get();
        
        $ordersCountByHour = Order::pluck('created_at')->map(function ($date) {
            return \Carbon\Carbon::parse($date)->format('H');
        })->countBy();
        
        $hourlyBreakdown = $ordersCountByHour->sortKeys()->toArray();
        
        $peakOrderHour = 'N/A';
        if ($ordersCountByHour->isNotEmpty()) {
            $peakHourKey = $ordersCountByHour->sortDesc()->keys()->first();
            $hourInt = (int)$peakHourKey;
            $nextHour = $hourInt + 1;
            if ($nextHour === 24) $nextHour = 0;
            $peakOrderHour = sprintf('%02d:00 - %02d:00', $hourInt, $nextHour);
        }

        $bestSellersCursor = Order::raw(function ($collection) {
            return $collection->aggregate([
                ['$match' => ['payment_status' => ['$ne' => 'Failed']]],
                ['$unwind' => '$items'],
                ['$group' => [
                    '_id' => '$items.base_name',
                    'total_quantity' => ['$sum' => '$items.qty'],
                ]],
                ['$sort' => ['total_quantity' => -1]],
                ['$limit' => 5]
            ]);
        });
        $bestSellers = collect(iterator_to_array($bestSellersCursor));

        $slowMoversCursor = Order::raw(function ($collection) {
            return $collection->aggregate([
                ['$match' => ['payment_status' => ['$ne' => 'Failed']]],
                ['$unwind' => '$items'],
                ['$group' => [
                    '_id' => '$items.base_name',
                    'total_quantity' => ['$sum' => '$items.qty'],
                ]],
                ['$match' => ['total_quantity' => ['$gt' => 0]]],
                ['$sort' => ['total_quantity' => 1]],
                ['$limit' => 5]
            ]);
        });
        $slowMovers = collect(iterator_to_array($slowMoversCursor));

        $totalProductsSoldAggr = Order::raw(function ($collection) {
            return $collection->aggregate([
                ['$match' => ['payment_status' => ['$ne' => 'Failed']]],
                ['$unwind' => '$items'],
                ['$group' => [
                    '_id' => null,
                    'total' => ['$sum' => '$items.qty'],
                ]]
            ]);
        });
        $totalProductsSoldArray = iterator_to_array($totalProductsSoldAggr);
        $totalProductsSold = !empty($totalProductsSoldArray) ? $totalProductsSoldArray[0]['total'] : 0;

        $customerGroupsCursor = Order::raw(function ($collection) {
            return $collection->aggregate([
                ['$match' => ['payment_status' => ['$ne' => 'Failed'], 'customer_name' => ['$ne' => null]]],
                ['$group' => [
                    '_id' => '$customer_name',
                    'order_count' => ['$sum' => 1]
                ]]
            ]);
        });
        $customerGroupsArray = iterator_to_array($customerGroupsCursor);
        
        $totalUniqueCustomers = count($customerGroupsArray);
        $newCustomers = 0;
        $returningCustomers = 0;
        
        foreach ($customerGroupsArray as $customer) {
            if ($customer['order_count'] === 1) {
                $newCustomers++;
            } else {
                $returningCustomers++;
            }
        }

        $topLocationsCursor = Order::raw(function ($collection) {
            return $collection->aggregate([
                ['$match' => ['payment_status' => ['$ne' => 'Failed'], 'delivery_city' => ['$ne' => null, '$ne' => '']]],
                ['$group' => [
                    '_id' => '$delivery_city',
                    'order_count' => ['$sum' => 1]
                ]],
                ['$sort' => ['order_count' => -1]],
                ['$limit' => 5]
            ]);
        });
        $topLocations = collect(iterator_to_array($topLocationsCursor));

        $paidOrders = Order::where('payment_status', '!=', 'Failed')->get();
        $totalMinutes = 0;
        $processedCount = 0;
        foreach ($paidOrders as $order) {
            if ($order->created_at && $order->updated_at) {
                $totalMinutes += $order->created_at->diffInMinutes($order->updated_at);
                $processedCount++;
            }
        }
        $avgProcessingTime = $processedCount > 0 ? round($totalMinutes / $processedCount) : 0;

        $ordersWithPromo = Order::where('payment_status', '!=', 'Failed')->whereNotNull('promo_code')->where('promo_code', '!=', '')->count();
        $totalDiscountGiven = Order::where('payment_status', '!=', 'Failed')->sum('discount');

        $topPromoCodesCursor = Order::raw(function ($collection) {
            return $collection->aggregate([
                ['$match' => ['payment_status' => ['$ne' => 'Failed'], 'promo_code' => ['$ne' => null, '$ne' => '']]],
                ['$group' => [
                    '_id' => '$promo_code',
                    'usage_count' => ['$sum' => 1]
                ]],
                ['$sort' => ['usage_count' => -1]],
                ['$limit' => 5]
            ]);
        });
        $topPromoCodes = collect(iterator_to_array($topPromoCodesCursor));

        return view('dashboard.analytics.index', array_merge(
            $this->dashboardData(),
            compact(
                'dailyRevenue', 'totalRevenueAllTime', 'totalOrdersCount', 'topPizzas', 'statusBreakdown', 'topCustomers',
                'totalRevenue', 'averageOrderValue', 'paymentMethodsBreakdown', 'refundsAndCancellationsCount', 'refundsAndCancellationsTotal',
                'todayOrderVolume', 'peakOrderHour', 'todayOrdersList', 'hourlyBreakdown',
                'bestSellers', 'slowMovers', 'totalProductsSold', 'totalUniqueCustomers', 'newCustomers', 'returningCustomers',
                'avgProcessingTime', 'topLocations', 'ordersWithPromo', 'totalDiscountGiven', 'topPromoCodes'
            )
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

        $topPizzas = Order::raw(function ($collection) {
            return $collection->aggregate([
                ['$match' => ['payment_status' => ['$ne' => 'Failed']]],
                ['$unwind' => '$items'],
                ['$group' => [
                    '_id' => '$items.base_name',
                    'total_quantity' => ['$sum' => '$items.qty'],
                ]],
                ['$sort' => ['total_quantity' => -1]],
                ['$limit' => 5]
            ]);
        });

        $statusBreakdown = Order::raw(function ($collection) {
            return $collection->aggregate([
                ['$group' => [
                    '_id' => '$payment_status',
                    'count' => ['$sum' => 1]
                ]],
                ['$sort' => ['count' => -1]]
            ]);
        });

        $topCustomers = Order::raw(function ($collection) {
            return $collection->aggregate([
                ['$match' => ['payment_status' => ['$ne' => 'Failed']]],
                ['$group' => [
                    '_id' => '$customer_name',
                    'total_spent' => ['$sum' => '$total'],
                    'order_count' => ['$sum' => 1]
                ]],
                ['$sort' => ['total_spent' => -1]],
                ['$limit' => 5]
            ]);
        });

        $totalRevenueAllTime = Order::sum('total');
        $totalOrdersCount = Order::count();

        $pdf = Pdf::loadView('dashboard.analytics.pdf', compact('dailyRevenue', 'totalRevenueAllTime', 'totalOrdersCount', 'topPizzas', 'statusBreakdown', 'topCustomers'));
        
        return $pdf->download('PizzaFlow_Sales_Report.pdf');
    }
}
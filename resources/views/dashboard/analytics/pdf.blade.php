<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PizzaFlow Sales Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 20px;
        }
        h1 {
            color: #E63946;
            border-bottom: 2px solid #E63946;
            padding-bottom: 10px;
            text-align: center;
        }
        .summary-box {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            width: 45%;
            display: inline-block;
            box-sizing: border-box;
        }
        .summary-box h3 {
            margin-top: 0;
            color: #555;
            font-size: 14px;
        }
        .summary-box p {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
            color: #000;
        }
        .right-box {
            float: right;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f1f1f1;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>

    <h1>PizzaFlow Sales Report</h1>

    <div class="clearfix">
        <div class="summary-box">
            <h3>All-Time Revenue</h3>
            <p>Rs. {{ number_format($totalRevenueAllTime) }}</p>
        </div>
        <div class="summary-box right-box">
            <h3>Total Orders</h3>
            <p>{{ number_format($totalOrdersCount) }}</p>
        </div>
    </div>

    <h2>Last 30 Days Daily Revenue</h2>
    
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Number of Orders</th>
                <th class="text-right">Total Revenue</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dailyRevenue as $day)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($day->_id)->format('M d, Y') }}</td>
                    <td>{{ $day->order_count }}</td>
                    <td class="text-right">Rs. {{ number_format($day->total_revenue) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">No revenue data found for the last 30 days.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('M d, Y H:i:s') }}
    </div>

</body>
</html>

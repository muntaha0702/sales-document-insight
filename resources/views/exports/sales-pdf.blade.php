<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; }
        .subtitle { font-size: 16px; color: #555; }
        .report-date { font-size: 14px; margin-bottom: 30px; }
        .section { margin-bottom: 30px; }
        .section-title { 
            font-size: 18px; 
            font-weight: bold; 
            border-bottom: 1px solid #ddd; 
            padding-bottom: 5px; 
            margin-bottom: 10px;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #f2f2f2; text-align: left; padding: 8px; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        .kpi { margin-bottom: 15px; }
        .kpi-label { font-weight: bold; display: inline-block; width: 200px; }
        .footer { margin-top: 50px; font-size: 12px; text-align: center; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Sales Report</div>
        <div class="subtitle">Generated from your sales data</div>
        <div class="report-date">
            Report Date: {{ now()->format('F j, Y') }}<br>
            @if($filters['start_date'] && $filters['end_date'])
                Period: {{ \Carbon\Carbon::parse($filters['start_date'])->format('M j, Y') }} - {{ \Carbon\Carbon::parse($filters['end_date'])->format('M j, Y') }}
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Key Performance Indicators</div>
        <div class="kpi">
            <span class="kpi-label">Total Revenue:</span> ${{ number_format($totalRevenue, 2) }}
        </div>
        <div class="kpi">
            <span class="kpi-label">Average Revenue:</span> ${{ number_format($avgRevenue, 2) }}
        </div>
        <div class="kpi">
            <span class="kpi-label">Total Units Sold:</span> {{ number_format($totalUnitsSold) }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">Revenue by Product</div>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Revenue</th>
                    <th>Returns</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($revenueByProduct as $product)
                <tr>
                    <td>{{ $product->product_name }}</td>
                    <td>${{ number_format($product->total_revenue, 2) }}</td>
                    <td>{{ number_format($product->total_returns) }}</td>
                    <td>{{ number_format($product->total_quantity) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Revenue by Category</div>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($revenueByCategory as $category)
                <tr>
                    <td>{{ $category->category }}</td>
                    <td>${{ number_format($category->total_revenue, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Return Rates</div>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Return Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($returnRates as $rate)
                <tr>
                    <td>{{ $rate->product_name }}</td>
                    <td>{{ number_format($rate->return_rate, 2) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        Report generated on {{ now()->format('F j, Y \a\t H:i') }}
    </div>
</body>
</html>
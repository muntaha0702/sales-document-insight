<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
        .card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-4px);
        }
        .kpi-card {
            border-left: 4px solid #3b82f6;
        }
        .insights-card {
            border-left: 4px solid #10b981;
        }
        .chart-card {
            border-left: 4px solid #f59e0b;
        }
        .kpi-list li {
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 1.1rem;
            color: #1f2937;
        }
        .kpi-list li:last-child {
            border-bottom: none;
        }
        .chart-container {
            max-width: 800px;
            margin: 0 auto;
            min-height: 300px;
            position: relative;
        }
        .insights p {
            margin-bottom: 1rem;
            color: #4b5563;
            font-size: 1rem;
        }
        .section-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #d1d5db;
            padding-bottom: 0.5rem;
        }
        .list-item li {
            padding: 0.75rem 0;
            color: #374151;
            font-size: 1.1rem;
        }
        .highlight-bg {
            background-color: #f3f4f6;
            padding: 1rem;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container mx-auto px-4 py-10">
        <h1 class="text-4xl font-bold text-gray-800 mb-8 text-center">Sales Dashboard</h1>


      <a href="{{ route('csv.export.pdf', [
    'start_date' => request('start_date'),
    'end_date' => request('end_date'),
    'region' => request('region'),
    'categories' => request('categories')
]) }}" 
   class="btn btn-primary"
   download="sales-report.pdf">
   Download PDF
</a>

        <!-- Filter Form -->
        <div class="card kpi-card mb-6">
            <h3 class="section-title">Filters</h3>
            <div class="highlight-bg">
                <form action="{{ route('csv.visualize') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="region" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Region</label>
                        <select name="region" id="region" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                            <option value="">All Regions</option>
                            @foreach ($allRegions as $region)
                                <option value="{{ $region }}" {{ $region == request('region') ? 'selected' : '' }}>{{ $region }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Categories</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($allCategories as $category)
                                <div class="form-check">
                                    <input type="checkbox" name="categories[]" value="{{ $category }}" {{ in_array($category, request('categories', [])) ? 'checked' : '' }} class="form-check-input h-4 w-4 text-blue-600 border-gray-300 rounded dark:bg-gray-700 dark:border-gray-600">
                                    <label class="form-check-label text-gray-700 dark:text-gray-300">{{ $category }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="md:col-span-3 flex justify-end space-x-4">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Apply Filters</button>
                        <!-- <button type="button" id="exportData" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Export as CSV</button> -->
                    </div>
                </form>
                <div id="loadingSpinner" class="hidden mt-4 text-center">
                    <svg class="animate-spin h-5 w-5 text-blue-500 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="ml-2 text-gray-700 dark:text-gray-300">Loading...</span>
                </div>
            </div>
        </div>

        <!-- Key Performance Indicators -->
        <div class="card kpi-card">
            <h3 class="section-title">Key Performance Indicators</h3>
            <div class="highlight-bg">
                <ul class="kpi-list">
                    <li>Total Revenue: <span class="font-semibold text-blue-600">${{ number_format($totalRevenue, 2) }}</span></li>
                    <li>Average Revenue per Transaction: <span class="font-semibold text-blue-600">${{ number_format($avgRevenue, 2) }}</span></li>
                    <li>Total Units Sold: <span class="font-semibold text-blue-600">{{ $totalUnitsSold }}</span></li>
                </ul>
            </div>
        </div>

        <!-- Insights -->
        <div class="card insights-card">
            <h3 class="section-title">Insights</h3>
            <div class="highlight-bg">
                <div class="insights">
                    <p><strong>Top Products:</strong> {{ $insights['top_products'] }}</p>
                    <p><strong>Return Anomalies:</strong> {{ $insights['return_anomalies'] }}</p>
                    <p><strong>Seasonal Trend:</strong> {{ $insights['seasonal_trend'] }}</p>
                </div>
            </div>
        </div>

        <!-- Revenue by Product -->
        <div class="card chart-card">
            <h3 class="section-title">Revenue by Product</h3>
            <div class="chart-container">
                <canvas id="revenueChart" 
                        width="400" 
                        height="300"
                        data-labels='@json($labels)'
                        data-revenues='@json($revenues)'
                        data-average='@json($averageLine)'></canvas>
            </div>
        </div>

        <!-- Revenue by Category -->
        <div class="card chart-card">
            <h3 class="section-title">Revenue by Category</h3>
            <div class="chart-container">
                <canvas id="categoryChart" 
                        width="400" 
                        height="300"
                        data-categories='@json($revenueByCategory->pluck("category"))'
                        data-cat-revenues='@json($revenueByCategory->pluck("total_revenue"))'></canvas>
            </div>
            <div class="highlight-bg mt-4">
                <ul class="list-item">
                    @foreach ($revenueByCategory as $category)
                        <li>{{ $category->category }}: <span class="font-semibold text-amber-600">${{ number_format($category->total_revenue, 2) }}</span></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Revenue by Region -->
        <div class="card chart-card">
            <h3 class="section-title">Revenue by Region</h3>
            <div class="chart-container">
                <canvas id="regionChart" 
                        width="400" 
                        height="300"
                        data-regions='@json($revenueByRegion->pluck("region"))'
                        data-region-revenues='@json($revenueByRegion->pluck("total_revenue"))'></canvas>
            </div>
            <div class="highlight-bg mt-4">
                <ul class="list-item">
                    @foreach ($revenueByRegion as $region)
                        <li>{{ $region->region }}: <span class="font-semibold text-amber-600">${{ number_format($region->total_revenue, 2) }}</span></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Revenue by Store -->
        <div class="card chart-card">
            <h3 class="section-title">Revenue by Store</h3>
            <div class="chart-container">
                <canvas id="storeChart" 
                        width="400" 
                        height="300"
                        data-stores='@json($revenueByStore->pluck("store_id"))'
                        data-store-revenues='@json($revenueByStore->pluck("total_revenue"))'></canvas>
            </div>
            <div class="highlight-bg mt-4">
                <ul class="list-item">
                    @foreach ($revenueByStore as $store)
                        <li>{{ $store->store_id }}: <span class="font-semibold text-amber-600">${{ number_format($store->total_revenue, 2) }}</span></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Revenue Trend by Month -->
        <div class="card chart-card">
            <h3 class="section-title">Revenue Trend by Month</h3>
            <div class="chart-container">
                <canvas id="monthlyRevenueChart" 
                        width="400" 
                        height="300"
                        data-month-labels='@json($monthLabels)'
                        data-month-revenues='@json($monthRevenues)'></canvas>
            </div>
        </div>
    </div>

    <script>
        // Helper function to get chart data
        function getChartData(id) {
            const el = document.getElementById(id);
            return el ? el.dataset : {};
        }

        // Initialize all charts
        document.addEventListener('DOMContentLoaded', function() {
            // Revenue by Product Chart
            const revenueData = getChartData('revenueChart');
            if (revenueData.labels) {
                new Chart(document.getElementById('revenueChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: JSON.parse(revenueData.labels),
                        datasets: [
                            {
                                label: 'Revenue by Product',
                                data: JSON.parse(revenueData.revenues),
                                backgroundColor: 'rgba(75, 192, 192, 0.4)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Average Revenue',
                                data: JSON.parse(revenueData.average),
                                type: 'line',
                                borderColor: 'rgba(239, 68, 68, 1)',
                                borderWidth: 2,
                                fill: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Revenue ($)' }
                            },
                            x: { title: { display: true, text: 'Product' } }
                        },
                        plugins: {
                            legend: { position: 'top' }
                        }
                    }
                });
            }

            // Revenue by Category Chart
            const categoryData = getChartData('categoryChart');
            if (categoryData.categories) {
                new Chart(document.getElementById('categoryChart').getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: JSON.parse(categoryData.categories),
                        datasets: [{
                            label: 'Revenue by Category',
                            data: JSON.parse(categoryData.catRevenues),
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.6)',
                                'rgba(16, 185, 129, 0.6)',
                                'rgba(245, 158, 11, 0.6)',
                                'rgba(239, 68, 68, 0.6)',
                                'rgba(139, 92, 246, 0.6)'
                            ],
                            borderColor: [
                                'rgba(59, 130, 246, 1)',
                                'rgba(16, 185, 129, 1)',
                                'rgba(245, 158, 11, 1)',
                                'rgba(239, 68, 68, 1)',
                                'rgba(139, 92, 246, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'right' },
                            title: { display: true, text: 'Revenue Distribution by Category' }
                        }
                    }
                });
            }

            // Revenue by Region Chart
            const regionData = getChartData('regionChart');
            if (regionData.regions) {
                new Chart(document.getElementById('regionChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: JSON.parse(regionData.regions),
                        datasets: [{
                            label: 'Revenue by Region',
                            data: JSON.parse(regionData.regionRevenues),
                            backgroundColor: 'rgba(16, 185, 129, 0.4)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Revenue ($)' }
                            },
                            x: { title: { display: true, text: 'Region' } }
                        },
                        plugins: {
                            legend: { position: 'top' }
                        }
                    }
                });
            }

            // Revenue by Store Chart
            const storeData = getChartData('storeChart');
            if (storeData.stores) {
                new Chart(document.getElementById('storeChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: JSON.parse(storeData.stores),
                        datasets: [{
                            label: 'Revenue by Store',
                            data: JSON.parse(storeData.storeRevenues),
                            backgroundColor: 'rgba(139, 92, 246, 0.4)',
                            borderColor: 'rgba(139, 92, 246, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Revenue ($)' }
                            },
                            x: { title: { display: true, text: 'Store ID' } }
                        },
                        plugins: {
                            legend: { position: 'top' }
                        }
                    }
                });
            }

            // Revenue Trend by Month Chart
            const monthlyData = getChartData('monthlyRevenueChart');
            if (monthlyData.monthLabels) {
                new Chart(document.getElementById('monthlyRevenueChart').getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: JSON.parse(monthlyData.monthLabels),
                        datasets: [{
                            label: 'Revenue by Month',
                            data: JSON.parse(monthlyData.monthRevenues),
                            backgroundColor: 'rgba(59, 130, 246, 0.2)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Revenue ($)' }
                            },
                            x: { title: { display: true, text: 'Month' } }
                        },
                        plugins: {
                            legend: { position: 'top' },
                            title: { display: true, text: 'Revenue Trend Over Time' }
                        }
                    }
                });
            }

           
        });
    </script>
</body>
</html>
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CsvImport;
use Spatie\SimpleExcel\SimpleExcelReader;
use Illuminate\Support\Facades\DB;
use App\Models\CsvData;
use App\Models\Sales;
use PhpParser\Node\Expr\FuncCall;
use Barryvdh\DomPDF\Facade\Pdf;

class CsvProcessorController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }
       public function upload(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        try {
            // Store the file temporarily
            $path = $request->file('csv_file')->store('temp');
            $fullPath = storage_path('app/' . $path);

            // Read the CSV file using Spatie SimpleExcel
            $reader = SimpleExcelReader::create($fullPath);

            // Expected columns in the CSV
            $expectedColumns = [
                'transaction_id',
                'date',
                'product_name',
                'category',
                'quantity',
                'unit_price',
                'revenue',
                'customer_id',
                'region',
                'returns',
                'discount',
                'payment_method',
                'store_id'
            ];

            // Get the header row to validate columns
            $csvHeaders = $reader->getHeaders();

            // Validate that all required columns are present
            $missingColumns = array_diff(array_intersect($expectedColumns, ['transaction_id', 'date', 'product_name', 'quantity', 'unit_price', 'revenue']), $csvHeaders);
            if (!empty($missingColumns)) {
                throw new \Exception('Missing required columns: ' . implode(', ', $missingColumns));
            }

            // Process each row and insert into the sales table
            DB::transaction(function () use ($reader, $csvHeaders) {
                foreach ($reader->getRows() as $row) {
                    // Map CSV row to sales table fields
                    $data = [
                        'transaction_id' => $row['transaction_id'],
                        'date' => \Carbon\Carbon::parse($row['date'])->format('Y-m-d'),
                        'product_name' => $row['product_name'],
                        'category' => $row['category'] ?? null,
                        'quantity' => (int) $row['quantity'],
                        'unit_price' => (float) $row['unit_price'],
                        'revenue' => (float) $row['revenue'],
                        'customer_id' => $row['customer_id'] ?? null,
                        'region' => $row['region'] ?? null,
                        'returns' => isset($row['returns']) ? (int) $row['returns'] : 0,
                        'discount' => isset($row['discount']) ? (float) $row['discount'] : 0.00,
                        'payment_method' => $row['payment_method'] ?? null,
                        'store_id' => $row['store_id'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Insert into the sales table
                    DB::table('sales')->insert($data);
                }
            });

            // Delete the temporary file
            unlink($fullPath);

            return redirect()->route('csv.visualize')->with('success', 'CSV uploaded and data imported successfully.');
        } catch (\Exception $e) {
            // Delete the temporary file if it exists
            if (isset($fullPath) && file_exists($fullPath)) {
                unlink($fullPath);
            }
            return back()->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }


 // Assuming dom禁止

public function visualize(Request $request)
{
    // Validate input parameters
    $validated = $request->validate([
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'region' => 'nullable|string|exists:sales,region',
        'categories' => 'nullable|array',
        'categories.*' => 'string|exists:sales,category',
    ]);

    // Extract validated inputs
    $startDate = $validated['start_date'] ?? null;
    $endDate = $validated['end_date'] ?? null;
    $region = $validated['region'] ?? null;
    $categories = $validated['categories'] ?? [];

    // Get all distinct regions and categories for the form
    $allRegions = Sales::select('region')->distinct()->pluck('region')->toArray();
    $allCategories = Sales::select('category')->distinct()->pluck('category')->toArray();

    // Base query
    $baseQuery = Sales::query();

    // Apply date range filter
    if ($startDate && $endDate) {
        $baseQuery->whereBetween('date', [$startDate, $endDate]);
    }

    // Apply region filter
    if ($region) {
        $baseQuery->where('region', $region);
    }

    // Apply categories filter
    if (!empty($categories)) {
        $baseQuery->whereIn('category', $categories);
    }

     if ($request->input('export') === 'pdf') {
            return redirect()->route('csv.export.pdf', $request->query());
        }

    // Clone the base query for each KPI
    $totalRevenue = (clone $baseQuery)->sum('revenue') ?? 0;

    $revenueByProductQuery = (clone $baseQuery)->select('product_name')
        ->selectRaw('SUM(revenue) as total_revenue')
        ->selectRaw('SUM(returns) as total_returns')
        ->selectRaw('SUM(quantity) as total_quantity')
        ->groupBy('product_name');
    $revenueByProduct = $revenueByProductQuery->get();

    $revenueByCategory = (clone $baseQuery)->select('category')
        ->selectRaw('SUM(revenue) as total_revenue')
        ->groupBy('category')
        ->get();

    $avgRevenue = (clone $baseQuery)->avg('revenue') ?? 0;

    $totalUnitsSold = (clone $baseQuery)->sum('quantity') ?? 0;

    $returnRates = (clone $baseQuery)->select('product_name')
        ->selectRaw('SUM(returns) / NULLIF(SUM(quantity), 0) * 100 as return_rate')
        ->groupBy('product_name')
        ->havingRaw('SUM(quantity) > 0')
        ->get();

    $revenueByRegion = (clone $baseQuery)->select('region')
        ->selectRaw('SUM(revenue) as total_revenue')
        ->groupBy('region')
        ->get();

    $revenueByStore = (clone $baseQuery)->select('store_id')
        ->selectRaw('SUM(revenue) as total_revenue')
        ->groupBy('store_id')
        ->get();

    $customerFrequency = (clone $baseQuery)->select('customer_id')
        ->selectRaw('COUNT(transaction_id) as purchase_count')
        ->groupBy('customer_id')
        ->having('purchase_count', '>', 1)
        ->get();

    $monthlyRevenue = (clone $baseQuery)->selectRaw('DATE_FORMAT(date, "%Y-%m") as month')
        ->selectRaw('SUM(revenue) as total_revenue')
        ->groupByRaw('DATE_FORMAT(date, "%Y-%m")')
        ->orderBy('month')
        ->get();

    // Prepare data for Chart.js
    $labels = $revenueByProduct->pluck('product_name')->toArray();
    $revenues = $revenueByProduct->pluck('total_revenue')->toArray();
    $averageRevenue = !empty($revenues) ? array_sum($revenues) / count($revenues) : 0;
    $averageLine = array_fill(0, count($revenues), $averageRevenue);

    // Monthly revenue data
    $monthLabels = $monthlyRevenue->pluck('month')->toArray();
    $monthRevenues = $monthlyRevenue->pluck('total_revenue')->toArray();

    // Detect anomalies in returns
    $returns = $revenueByProduct->pluck('total_returns')->toArray();
    $avgReturns = !empty($returns) ? array_sum($returns) / count($returns) : 0;
    $stdDevReturns = $this->calculateStandardDeviation($returns);
    $anomalies = $this->detectAnomalies($returns, $avgReturns, $stdDevReturns);

    // Generate AI Insights
    $topProducts = $revenueByProduct->sortByDesc('total_revenue')->take(2)->pluck('product_name')->toArray();
    $highReturnProducts = $returnRates->filter(fn($item) => $item->return_rate > 20)->pluck('product_name')->toArray();
    $insights = [
        'top_products' => 'Top products: ' . implode(', ', $topProducts),
        'return_anomalies' => !empty($highReturnProducts) ? 'High returns detected for: ' . implode(', ', $highReturnProducts) : 'No significant return anomalies detected.',
        'seasonal_trend' => $this->getSeasonalTrend(clone $baseQuery),
    ];

    // Handle PDF export
    // if ($request->input('export') === 'pdf') {
    //     // Load LaTeX template
    //     $latexTemplate = file_get_contents(resource_path('latex/sales_report.tex'));
    //     echo "hfd";
    //     die();
    //     // Replace placeholders with actual data
    //     $latexContent = $latexTemplate;
    //     $latexContent = str_replace('%.2f|totalRevenue', number_format($totalRevenue, 2), $latexContent);
    //     $latexContent = str_replace('%.2f|avgRevenue', number_format($avgRevenue, 2), $latexContent);
    //     $latexContent = str_replace('%d|totalUnitsSold', $totalUnitsSold, $latexContent);
    //     $latexContent = str_replace('%s|insights.top_products', $insights['top_products'], $latexContent);
    //     $latexContent = str_replace('%s|insights.return_anomalies', $insights['return_anomalies'], $latexContent);
    //     $latexContent = str_replace('%s|insights.seasonal_trend', $insights['seasonal_trend'], $latexContent);

    //     // Generate rows for revenueByProduct
    //     $productRows = '';
    //     foreach ($revenueByProduct as $row) {
    //         $productRows .= sprintf(
    //             "%s & \\num{%.2f} & \\num{%.2f} & \\num{%d} \\\\\n",
    //             addslashes($row->product_name),
    //             $row->total_revenue,
    //             $row->total_returns,
    //             $row->total_quantity
    //         );
    //     }
    //     $latexContent = str_replace('%rows|revenueByProduct', $productRows, $latexContent);

    //     // Generate rows for revenueByCategory
    //     $categoryRows = '';
    //     foreach ($revenueByCategory as $row) {
    //         $categoryRows .= sprintf(
    //             "%s & \\num{%.2f} \\\\\n",
    //             addslashes($row->category),
    //             $row->total_revenue
    //         );
    //     }
    //     $latexContent = str_replace('%rows|revenueByCategory', $categoryRows, $latexContent);

    //     // Generate rows for revenueByRegion
    //     $regionRows = '';
    //     foreach ($revenueByRegion as $row) {
    //         $regionRows .= sprintf(
    //             "%s & \\num{%.2f} \\\\\n",
    //             addslashes($row->region),
    //             $row->total_revenue
    //         );
    //     }
    //     $latexContent = str_replace('%rows|revenueByRegion', $regionRows, $latexContent);

    //     // Generate rows for revenueByStore
    //     $storeRows = '';
    //     foreach ($revenueByStore as $row) {
    //         $storeRows .= sprintf(
    //             "%s & \\num{%.2f} \\\\\n",
    //             addslashes($row->store_id),
    //             $row->total_revenue
    //         );
    //     }
    //     $latexContent = str_replace('%rows|revenueByStore', $storeRows, $latexContent);

    //     // Generate rows for returnRates
    //     $returnRateRows = '';
    //     foreach ($returnRates as $row) {
    //         $returnRateRows .= sprintf(
    //             "%s & \\num{%.2f} \\\\\n",
    //             addslashes($row->product_name),
    //             $row->return_rate
    //         );
    //     }
    //     $latexContent = str_replace('%rows|returnRates', $returnRateRows, $latexContent);

    //     // Generate rows for monthlyRevenue
    //     $monthlyRows = '';
    //     foreach ($monthlyRevenue as $row) {
    //         $monthlyRows .= sprintf(
    //             "%s & \\num{%.2f} \\\\\n",
    //             addslashes($row->month),
    //             $row->total_revenue
    //         );
    //     }
    //     $latexContent = str_replace('%rows|monthlyRevenue', $monthlyRows, $latexContent);

    //     // Generate PDF
    //     $pdf = Pdf::loadLatex($latexContent); // Hypothetical method; use actual PDF rendering logic
    //     return $pdf->download('sales_report.pdf');
    // }

    // Handle AJAX response
    if ($request->ajax()) {
        return response()->json([
            'labels' => $labels,
            'revenues' => $revenues,
            'averageLine' => $averageLine,
            'categories' => $revenueByCategory->pluck('category')->toArray(),
            'catRevenues' => $revenueByCategory->pluck('total_revenue')->toArray(),
            'regions' => $revenueByRegion->pluck('region')->toArray(),
            'regionRevenues' => $revenueByRegion->pluck('total_revenue')->toArray(),
            'stores' => $revenueByStore->pluck('store_id')->toArray(),
            'storeRevenues' => $revenueByStore->pluck('total_revenue')->toArray(),
            'monthLabels' => $monthLabels,
            'monthRevenues' => $monthRevenues,
            'insights' => $insights,
            'kpis' => [
                'totalRevenue' => $totalRevenue,
                'avgRevenue' => $avgRevenue,
                'totalUnitsSold' => $totalUnitsSold,
            ],
        ], 200);
    }

    // Return view for non-AJAX requests
    return view('csv.visualize', compact(
        'labels',
        'revenues',
        'averageRevenue',
        'averageLine',
        'anomalies',
        'insights',
        'totalRevenue',
        'revenueByCategory',
        'avgRevenue',
        'totalUnitsSold',
        'returnRates',
        'revenueByRegion',
        'revenueByStore',
        'customerFrequency',
        'monthLabels',
        'monthRevenues',
        'allRegions',
        'allCategories'
    ));
}



 public function exportPdf(Request $request)
    {
        // Validate input parameters (same as visualize method)
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'region' => 'nullable|string',
            'categories' => 'nullable|array',
            'categories.*' => 'string',
        ]);

        // Extract validated inputs
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $region = $validated['region'] ?? null;
        $categories = $validated['categories'] ?? [];

        // Base query
        $baseQuery = Sales::query();

        // Apply filters (same as visualize method)
        if ($startDate && $endDate) {
            $baseQuery->whereBetween('date', [$startDate, $endDate]);
        }
        if ($region) {
            $baseQuery->where('region', $region);
        }
        if (!empty($categories)) {
            $baseQuery->whereIn('category', $categories);
        }

        // Get data for PDF (similar to visualize method but optimized for PDF)
        $data = [
            'totalRevenue' => (clone $baseQuery)->sum('revenue') ?? 0,
            'avgRevenue' => (clone $baseQuery)->avg('revenue') ?? 0,
            'totalUnitsSold' => (clone $baseQuery)->sum('quantity') ?? 0,
            'revenueByProduct' => (clone $baseQuery)->select('product_name')
                ->selectRaw('SUM(revenue) as total_revenue')
                ->selectRaw('SUM(returns) as total_returns')
                ->selectRaw('SUM(quantity) as total_quantity')
                ->groupBy('product_name')
                ->get(),
            'revenueByCategory' => (clone $baseQuery)->select('category')
                ->selectRaw('SUM(revenue) as total_revenue')
                ->groupBy('category')
                ->get(),
            'revenueByRegion' => (clone $baseQuery)->select('region')
                ->selectRaw('SUM(revenue) as total_revenue')
                ->groupBy('region')
                ->get(),
            'revenueByStore' => (clone $baseQuery)->select('store_id')
                ->selectRaw('SUM(revenue) as total_revenue')
                ->groupBy('store_id')
                ->get(),
            'returnRates' => (clone $baseQuery)->select('product_name')
                ->selectRaw('SUM(returns) / NULLIF(SUM(quantity), 0) * 100 as return_rate')
                ->groupBy('product_name')
                ->havingRaw('SUM(quantity) > 0')
                ->get(),
            'monthlyRevenue' => (clone $baseQuery)->selectRaw('DATE_FORMAT(date, "%Y-%m") as month')
                ->selectRaw('SUM(revenue) as total_revenue')
                ->groupByRaw('DATE_FORMAT(date, "%Y-%m")')
                ->orderBy('month')
                ->get(),
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'region' => $region,
                'categories' => $categories
            ]
        ];

         $pdf = Pdf::loadView('exports.sales-pdf', $data);
    
    return $pdf->download('sales-report-'.now()->format('Y-m-d').'.pdf', [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="sales-report-'.now()->format('Y-m-d').'.pdf"'
    ]);
    }

/**
 * Calculate standard deviation for anomaly detection
 * @param array $data
 * @return float
 */
private function calculateStandardDeviation(array $data): float
{
    if (empty($data)) {
        return 0;
    }
    $mean = array_sum($data) / count($data);
    $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $data)) / count($data);
    return sqrt($variance);
}

/**
 * Detect anomalies in data
 * @param array $data
 * @param float $mean
 * @param float $stdDev
 * @return array
 */
private function detectAnomalies(array $data, float $mean, float $stdDev): array
{
    if ($stdDev == 0) {
        return [];
    }
    $anomalies = [];
    foreach ($data as $index => $value) {
        if (abs($value - $mean) > 2 * $stdDev) {
            $anomalies[] = $index;
        }
    }
    return $anomalies;
}

/**
 * Analyze seasonal trends
 * @param \Illuminate\Database\Eloquent\Builder $query
 * @return string
 */
private function getSeasonalTrend($query): string
{
    $monthlyData = $query->selectRaw('DATE_FORMAT(date, "%m") as month, SUM(revenue) as total_revenue')
        ->groupByRaw('DATE_FORMAT(date, "%m")')
        ->get()
        ->pluck('total_revenue', 'month')
        ->toArray();

    $peakMonth = array_key_exists('12', $monthlyData) && max($monthlyData) == $monthlyData['12']
        ? 'December'
        : (array_key_exists('06', $monthlyData) && max($monthlyData) == $monthlyData['06']
            ? 'June'
            : 'No clear seasonal trend');

    return "Peak sales observed in {$peakMonth}.";
}
    //WHOLE DATA
    public function showData()
    {
        $data = Sales::all();
        return view('csv.data',compact('data'));
        

    }
}

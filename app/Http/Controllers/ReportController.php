<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\SaleItem; // for sales report items

class ReportController extends Controller
{
    /**
     * Display the report generation form.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('report');
    }

    /**
     * Generate and display the requested report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generate(Request $request)
    {
        // Base validation rules
        $rules = [
            'report_type' => 'required|in:sales,inventory,expiry',
        ];
        
        // Add date validation rules conditionally
        if ($request->input('report_type') !== 'inventory') {
            $rules['start_date'] = 'required|date';
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
        }
        
        $validated = $request->validate($rules);

        $reportType = $validated['report_type'];
        
        // Set dates if provided or use defaults for inventory reports
        $startDate = isset($validated['start_date']) ? Carbon::parse($validated['start_date']) : Carbon::now()->subDays(30);
        $endDate = isset($validated['end_date']) ? Carbon::parse($validated['end_date']) : Carbon::now();

        switch ($reportType) {
            case 'sales':
                $data = $this->generateSalesReport($startDate, $endDate);
                break;
            case 'inventory':
                $data = $this->generateInventoryReport();
                break;
            case 'expiry':
                $data = $this->generateExpiryReport($endDate);
                break;
            default:
                return redirect()->back()->withErrors(['report_type' => 'Invalid report type.']);
        }

        // Check if export is requested
        if ($request->has('export') && $request->export === 'csv') {
            return $this->exportToCsv($data, $reportType);
        }

        return view('reports.show', compact('data', 'reportType', 'startDate', 'endDate'));
    }

    /**
     * Generate sales report.
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    private function generateSalesReport($startDate, $endDate)
    {
        // Fetch individual sale items in date range
        $saleItems = SaleItem::with(['product', 'sale.customer'])
            ->whereHas('sale', function($q) use ($startDate, $endDate) {
                $q->whereBetween('sale_date', [$startDate, $endDate]);
            })
            ->get();

        // Map to report-friendly objects
        $sales = $saleItems->map(function($item) {
            return (object)[
                'sale_date' => $item->sale->sale_date,
                'product' => $item->product,
                'quantity' => $item->quantity,
                'client' => $item->sale->customer,
            ];
        });

        // Summary counts: unique sales and total quantity
        $totalSales = $saleItems->pluck('sale_id')->unique()->count();
        $totalQuantity = $saleItems->sum('quantity');

        return [
            'sales' => $sales,
            'totalSales' => $totalSales,
            'totalQuantity' => $totalQuantity,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    /**
     * Generate inventory report.
     *
     * @return array
     */
    private function generateInventoryReport()
    {
        $products = Product::orderBy('name')->get();

        $totalProducts = $products->count();
        $totalStock = $products->sum('quantity');
        $lowStockCount = $products->filter(function ($product) {
            return $product->isLowStock();
        })->count();

        return [
            'products' => $products,
            'totalProducts' => $totalProducts,
            'totalStock' => $totalStock,
            'lowStockCount' => $lowStockCount,
        ];
    }

    /**
     * Generate expiry report.
     *
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    private function generateExpiryReport($endDate)
    {
        $products = Product::whereDate('expiry_date', '<=', $endDate)
                          ->orderBy('expiry_date')
                          ->get();

        $expiredCount = $products->filter(function ($product) {
            return $product->isExpired();
        })->count();

        $nearExpiryCount = $products->filter(function ($product) {
            return !$product->isExpired() && $product->isNearExpiry();
        })->count();

        return [
            'products' => $products,
            'expiredCount' => $expiredCount,
            'nearExpiryCount' => $nearExpiryCount,
            'endDate' => $endDate,
        ];
    }

    /**
     * Export data to CSV.
     *
     * @param  array  $data
     * @param  string  $reportType
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    private function exportToCsv($data, $reportType)
    {
        $filename = $reportType . '_report_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($data, $reportType) {
            $file = fopen('php://output', 'w');
            
            // Add headers based on report type
            switch ($reportType) {
                case 'sales':
                    fputcsv($file, ['Date', 'Product', 'Quantity', 'Client']);
                    foreach ($data['sales'] as $sale) {
                        fputcsv($file, [
                            $sale->sale_date->format('Y-m-d'),
                            $sale->product->name,
                            $sale->quantity,
                            $sale->client ? $sale->client->name : 'N/A',
                        ]);
                    }
                    break;
                
                case 'inventory':
                    fputcsv($file, ['Name', 'Quantity', 'Supplier', 'Expiry Date', 'Status']);
                    foreach ($data['products'] as $product) {
                        $status = '';
                        if ($product->isExpired()) {
                            $status = 'Expired';
                        } elseif ($product->isNearExpiry()) {
                            $status = 'Near Expiry';
                        } elseif ($product->isLowStock()) {
                            $status = 'Low Stock';
                        }
                        
                        fputcsv($file, [
                            $product->name,
                            $product->quantity,
                            $product->supplier,
                            $product->expiry_date->format('Y-m-d'),
                            $status,
                        ]);
                    }
                    break;
                
                case 'expiry':
                    fputcsv($file, ['Name', 'Quantity', 'Expiry Date', 'Status', 'Supplier']);
                    foreach ($data['products'] as $product) {
                        $status = $product->isExpired() ? 'Expired' : 'Near Expiry';
                        
                        fputcsv($file, [
                            $product->name,
                            $product->quantity,
                            $product->expiry_date->format('Y-m-d'),
                            $status,
                            $product->supplier,
                        ]);
                    }
                    break;
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
} 
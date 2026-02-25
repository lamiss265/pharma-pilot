<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Category;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show the dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get total products count
        $totalProducts = Product::count();
        
        // Get low stock products count
        $lowStockCount = Product::where('quantity', '<=', 10)->count();
        
        // Get near expiry products count
        $nearExpiryCount = Product::whereDate('expiry_date', '>=', Carbon::now())
                                 ->whereDate('expiry_date', '<=', Carbon::now()->addDays(30))
                                 ->count();
        
        // Get expired products count
        $expiredCount = Product::whereDate('expiry_date', '<', Carbon::now())->count();
        
        // Get today's sales count
        $todaySalesCount = Sale::whereDate('sale_date', Carbon::today())->count();
        
        // Get top 5 selling products
        $topProducts = DB::table('sale_items')
                        ->join('products', 'sale_items.product_id', '=', 'products.id')
                        ->select('products.name', DB::raw('SUM(sale_items.quantity) as total_sold'))
                        ->groupBy('products.name')
                        ->orderBy('total_sold', 'desc')
                        ->limit(5)
                        ->get();
        
        // Format data for chart
        $chartLabels = $topProducts->pluck('name')->toJson();
        $chartData = $topProducts->pluck('total_sold')->toJson();
        
        return view('dashboard', compact(
            'totalProducts',
            'lowStockCount',
            'nearExpiryCount',
            'expiredCount',
            'todaySalesCount',
            'topProducts',
            'chartLabels',
            'chartData'
        ));
    }
} 
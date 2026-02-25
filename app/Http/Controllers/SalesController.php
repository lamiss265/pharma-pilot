<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Client;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SalesController extends Controller
{
    /**
     * Display a listing of the sales.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // If user is worker, only show their sales
        if (auth()->user()->isWorker()) {
            $sales = Sale::with(['saleItems.product', 'client', 'user'])
                         ->where('user_id', auth()->id())
                         ->orderBy('sale_date', 'desc')
                         ->get();
        } else {
            // Admin can see all sales
            $sales = Sale::with(['saleItems.product', 'client', 'user'])
                         ->orderBy('sale_date', 'desc')
                         ->get();
        }
        
        return view('sales.index', compact('sales'));
    }

    /**
     * Show the form for creating a new sale.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $products = Product::where('quantity', '>', 0)
                          ->orderBy('name')
                          ->get();
        $clients = Client::orderBy('name')->get();
        
        return view('add_sale', compact('products', 'clients'));
    }

    /**
     * Store a newly created sale in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'client_id' => 'nullable|exists:clients,id',
            'sale_date' => 'required|date',
        ]);

        // Check if there's enough stock
        $product = Product::findOrFail($validated['product_id']);
        if ($product->quantity < $validated['quantity']) {
            return redirect()->back()
                            ->withErrors(['quantity' => __('messages.not_enough_stock')])
                            ->withInput();
        }

        // Set sale date to today if not provided
        if (!isset($validated['sale_date'])) {
            $validated['sale_date'] = Carbon::now();
        }

        // Add current user ID and product price
        $validated['user_id'] = auth()->id();
        $validated['price'] = $product->price;
        // Calculate subtotal (quantity * price)
        $validated['subtotal'] = $validated['quantity'] * $validated['price'];
        $validated['total_amount'] = $validated['subtotal'];

        // Set default values for new fields to prevent SQL errors
        $validated['final_amount'] = $validated['total_amount']; // Default final_amount to total_amount
        $validated['discount_amount'] = 0;
        $validated['tax_amount'] = 0;
        $validated['payment_method'] = 'cash'; // Default payment method
        $validated['status'] = 'completed'; // Default status

        // Create the sale
        $sale = Sale::create($validated);

        return redirect()->route('dashboard')
                         ->with('success', __('messages.sale_recorded'));
    }

    /**
     * Display the specified sale.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function show(Sale $sale)
    {
        // If user is worker, they can only view their own sales
        if (auth()->user()->isWorker() && $sale->user_id != auth()->id()) {
            return redirect()->route('sales.index')
                            ->with('error', __('messages.unauthorized'));
        }
        
        $sale->load(['product', 'client', 'user']);
        
        return view('sales.show', compact('sale'));
    }

    /**
     * Remove the specified sale from storage.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sale $sale)
    {
        // Only admin can delete sales, or workers can delete their own sales if made within last 24 hours
        if (auth()->user()->isWorker()) {
            if ($sale->user_id != auth()->id()) {
                return redirect()->route('sales.index')
                                ->with('error', __('messages.unauthorized'));
            }
            
            // Check if sale was made within last 24 hours
            if (Carbon::parse($sale->created_at)->diffInHours(Carbon::now()) > 24) {
                return redirect()->route('sales.index')
                                ->with('error', __('messages.sale_too_old'));
            }
        }
        
        // Restore stock for POS sale items or basic sale
        if ($sale->saleItems->isNotEmpty()) {
            foreach ($sale->saleItems as $item) {
                if ($item->product) {
                    $item->product->increment('quantity', $item->quantity);
                }
                if ($item->batch_id) {
                    $batch = \App\Models\Batch::find($item->batch_id);
                    if ($batch) {
                        $batch->increment('quantity_remaining', $item->quantity);
                    }
                }
            }
        } else {
            $product = $sale->product;
            if ($product) {
                $product->increment('quantity', $sale->quantity);
            }
        }
        
        $sale->delete();

        return redirect()->route('sales.index')
                         ->with('success', __('messages.sale_deleted'));
    }
} 
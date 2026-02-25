<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Product::query();
        
        // Apply search filter if provided
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('supplier', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('batch_number', 'like', "%{$search}%")
                  ->orWhere('dci', 'like', "%{$search}%")
                  ->orWhere('therapeutic_class', 'like', "%{$search}%");
            });
        }
        
        // Apply category filter if provided
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        
        // Apply expiry filter if provided
        if ($request->has('expiry_filter')) {
            $filter = $request->expiry_filter;
            
            if ($filter === 'expired') {
                $query->whereDate('expiry_date', '<', Carbon::now());
            } elseif ($filter === 'near_expiry') {
                $query->whereDate('expiry_date', '>=', Carbon::now())
                      ->whereDate('expiry_date', '<=', Carbon::now()->addDays(30));
            }
        }
        
        // Apply stock filter if provided
        if ($request->has('stock_filter')) {
            $filter = $request->stock_filter;
            
            if ($filter === 'low_stock') {
                $query->whereRaw('quantity <= reorder_point');
            } elseif ($filter === 'out_of_stock') {
                $query->where('quantity', 0);
            }
        }
        
        // Apply therapeutic class filter if provided
        if ($request->has('therapeutic_class') && $request->therapeutic_class) {
            $query->where('therapeutic_class', $request->therapeutic_class);
        }
        
        // Apply dosage form filter if provided
        if ($request->has('dosage_form') && $request->dosage_form) {
            $query->where('dosage_form', $request->dosage_form);
        }
        
        $products = $query->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        
        // Get unique therapeutic classes and dosage forms for filters
        $therapeuticClasses = Product::distinct()->whereNotNull('therapeutic_class')->pluck('therapeutic_class');
        $dosageForms = Product::distinct()->whereNotNull('dosage_form')->pluck('dosage_form');
        
        return view('inventory', compact('products', 'categories', 'therapeuticClasses', 'dosageForms'));
    }

    /**
     * Show the form for creating a new product.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = \App\Models\Supplier::orderBy('name')->get();
        return view('products.create', compact('categories', 'suppliers'));
    }

    /**
     * Store a newly created product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dci' => 'nullable|string|max:255',
            'dosage_form' => 'nullable|string|max:100',
            'therapeutic_class' => 'nullable|string|max:100',
            'composition' => 'nullable|string',
            'indications' => 'nullable|string',
            'contraindications' => 'nullable|string',
            'side_effects' => 'nullable|string',
            'storage_conditions' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|size:13|unique:products',
            'batch_number' => 'nullable|string|max:50',
            'manufacturing_date' => 'nullable|date|before_or_equal:today',
            'quantity' => 'required|integer|min:0',
            'reorder_point' => 'nullable|integer|min:0',
            'optimal_stock_level' => 'nullable|integer|min:0',
            'expiry_date' => 'required|date',
            'suppliers' => 'required|array|min:1',
'suppliers.*' => 'exists:suppliers,id',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        // Validate barcode if provided
        if (!empty($validated['barcode']) && !Product::validateBarcode($validated['barcode'])) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['barcode' => __('messages.invalid_barcode')]);
        }

        // Ensure reorder_point is less than optimal_stock_level
        if (isset($validated['reorder_point']) && isset($validated['optimal_stock_level']) && 
            $validated['reorder_point'] >= $validated['optimal_stock_level']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['reorder_point' => __('messages.reorder_point_must_be_less_than_optimal')]);
        }

        $data = collect($validated)->except(['suppliers'])->toArray();
        // legacy single supplier column, store first selected supplier id
        if ($request->has('suppliers')) {
            $data['supplier'] = $request->input('suppliers')[0];
        }
        $product = Product::create($data);

        // Attach suppliers (many-to-many)
        if ($request->has('suppliers')) {
            $product->suppliers()->sync($request->input('suppliers'));
        }

        return redirect()->route('inventory')
                         ->with('success', __('messages.product_created'));
    }

    /**
     * Show the form for editing the specified product.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'dci' => 'nullable|string|max:255',
            'dosage_form' => 'nullable|string|max:100',
            'therapeutic_class' => 'nullable|string|max:100',
            'composition' => 'nullable|string',
            'indications' => 'nullable|string',
            'contraindications' => 'nullable|string',
            'side_effects' => 'nullable|string',
            'storage_conditions' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|size:13|unique:products,barcode,' . $product->id,
            'batch_number' => 'nullable|string|max:50',
            'manufacturing_date' => 'nullable|date|before_or_equal:today',
            'quantity' => 'required|integer|min:0',
            'reorder_point' => 'nullable|integer|min:0',
            'optimal_stock_level' => 'nullable|integer|min:0',
            'expiry_date' => 'required|date',
            'supplier' => 'required|string|max:255',

            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        // Validate barcode if provided
        if (!empty($validated['barcode']) && !Product::validateBarcode($validated['barcode'])) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['barcode' => __('messages.invalid_barcode')]);
        }

        // Ensure reorder_point is less than optimal_stock_level
        if (isset($validated['reorder_point']) && isset($validated['optimal_stock_level']) && 
            $validated['reorder_point'] >= $validated['optimal_stock_level']) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['reorder_point' => __('messages.reorder_point_must_be_less_than_optimal')]);
        }

        // Update existing product
        $product->update($validated);

        return redirect()->route('inventory')
                         ->with('success', __('messages.product_created'));
    }

    /**
     * Remove the specified product from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        // Check if product has sales
        if ($product->saleItems()->count() > 0) {
            return redirect()->route('inventory')
                ->with('error', __('messages.product_has_sales'));
        }
        
        $product->delete();

        return redirect()->route('inventory')
                         ->with('success', __('messages.product_deleted'));
    }
    
    /**
     * Search for a product by barcode
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function searchByBarcode(Request $request)
    {
        $barcode = $request->barcode;
        
        if (empty($barcode)) {
            return response()->json(['error' => __('messages.barcode_required')], 400);
        }
        
        $product = Product::where('barcode', $barcode)->first();
        
        if (!$product) {
            return response()->json(['error' => __('messages.product_not_found')], 404);
        }
        
        return response()->json($product);
    }

    /**
     * Display purchase suggestions for products that need reordering
     *
     * @return \Illuminate\Http\Response
     */
    public function purchaseSuggestions()
    {
        $suggestions = Product::getPurchaseSuggestions();
        return view('products.purchase_suggestions', compact('suggestions'));
    }

    /**
     * Display detailed product information
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    /**
     * Batch update product quantities
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function batchUpdate(Request $request)
    {
        $validated = $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:0',
        ]);

        foreach ($validated['products'] as $productData) {
            $product = Product::find($productData['id']);
            $product->quantity = $productData['quantity'];
            $product->save();
        }

        return redirect()->route('inventory')
                        ->with('success', __('messages.products_updated'));
    }
} 
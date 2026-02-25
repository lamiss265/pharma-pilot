<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    /**
     * Display a listing of promotions.
     */
    public function index()
    {
        $promotions = Promotion::orderBy('created_at', 'desc')->paginate(20);
        return view('promotions.index', compact('promotions'));
    }

    /**
     * Show the form for creating a new promotion.
     */
    public function create()
    {
        $products = Product::orderBy('name')->get(['id', 'name']);
        $categories = Category::orderBy('name')->get(['id', 'name']);
        
        return view('promotions.create', compact('products', 'categories'));
    }

    /**
     * Store a newly created promotion.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:promotions',
            'type' => 'required|in:percentage,fixed,bogo,loyalty',
            'value' => 'required|numeric|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'minimum_items' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'applicable_products' => 'nullable|array',
            'applicable_categories' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        // Validate value based on type
        if ($validated['type'] === 'percentage' && $validated['value'] > 100) {
            return back()->withErrors(['value' => __('messages.percentage_cannot_exceed_100')]);
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['usage_count'] = 0;

        Promotion::create($validated);

        return redirect()->route('promotions.index')
                        ->with('success', __('messages.promotion_created_successfully'));
    }

    /**
     * Display the specified promotion.
     */
    public function show(Promotion $promotion)
    {
        $promotion->load(['applicable_products', 'applicable_categories']);
        
        $products = [];
        $categories = [];
        
        if ($promotion->applicable_products) {
            $products = Product::whereIn('id', $promotion->applicable_products)->get(['id', 'name']);
        }
        
        if ($promotion->applicable_categories) {
            $categories = Category::whereIn('id', $promotion->applicable_categories)->get(['id', 'name']);
        }

        return view('promotions.show', compact('promotion', 'products', 'categories'));
    }

    /**
     * Show the form for editing the specified promotion.
     */
    public function edit(Promotion $promotion)
    {
        $products = Product::orderBy('name')->get(['id', 'name']);
        $categories = Category::orderBy('name')->get(['id', 'name']);
        
        return view('promotions.edit', compact('promotion', 'products', 'categories'));
    }

    /**
     * Update the specified promotion.
     */
    public function update(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:promotions,code,' . $promotion->id,
            'type' => 'required|in:percentage,fixed,bogo,loyalty',
            'value' => 'required|numeric|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'minimum_items' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'applicable_products' => 'nullable|array',
            'applicable_categories' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        // Validate value based on type
        if ($validated['type'] === 'percentage' && $validated['value'] > 100) {
            return back()->withErrors(['value' => __('messages.percentage_cannot_exceed_100')]);
        }

        $validated['is_active'] = $request->has('is_active');

        $promotion->update($validated);

        return redirect()->route('promotions.show', $promotion)
                        ->with('success', __('messages.promotion_updated_successfully'));
    }

    /**
     * Remove the specified promotion.
     */
    public function destroy(Promotion $promotion)
    {
        $promotion->delete();

        return redirect()->route('promotions.index')
                        ->with('success', __('messages.promotion_deleted_successfully'));
    }

    /**
     * Toggle promotion active status.
     */
    public function toggleStatus(Promotion $promotion)
    {
        $promotion->update(['is_active' => !$promotion->is_active]);

        $status = $promotion->is_active ? __('messages.activated') : __('messages.deactivated');
        
        return redirect()->back()
                        ->with('success', __('messages.promotion') . ' ' . $status);
    }

    /**
     * Validate promotion code via AJAX.
     */
    public function validateCode(Request $request)
    {
        $code = $request->get('code');
        $cartTotal = $request->get('cart_total', 0);
        $cartItems = $request->get('cart_items', []);

        $promotion = Promotion::where('code', $code)->active()->first();

        if (!$promotion) {
            return response()->json([
                'valid' => false,
                'message' => __('messages.invalid_promotion_code')
            ]);
        }

        // Check if applicable to current cart
        $products = Product::whereIn('id', collect($cartItems)->pluck('product_id'))->get();
        
        if (!$promotion->appliesTo($products)) {
            return response()->json([
                'valid' => false,
                'message' => __('messages.promotion_not_applicable')
            ]);
        }

        // Calculate potential discount
        $discount = $promotion->calculateDiscount($cartItems, $cartTotal);

        return response()->json([
            'valid' => true,
            'promotion' => [
                'id' => $promotion->id,
                'name' => $promotion->name,
                'type' => $promotion->type,
                'discount_amount' => $discount
            ],
            'message' => __('messages.promotion_applied_successfully')
        ]);
    }

    /**
     * Get active promotions for sales interface.
     */
    public function getActivePromotions()
    {
        $promotions = Promotion::active()
                              ->select(['id', 'name', 'code', 'type', 'value', 'minimum_amount'])
                              ->get();

        return response()->json($promotions);
    }

    /**
     * Get promotion statistics.
     */
    public function statistics(Promotion $promotion)
    {
        // This would require a sales_promotions pivot table to track usage
        // For now, we'll return basic stats
        
        $stats = [
            'total_usage' => $promotion->usage_count,
            'remaining_usage' => $promotion->usage_limit ? $promotion->usage_limit - $promotion->usage_count : 'Unlimited',
            'success_rate' => $promotion->usage_count > 0 ? '100%' : '0%', // Would calculate from attempted vs successful usage
            'total_discount_given' => 0, // Would calculate from actual sales
            'average_order_value' => 0, // Would calculate from sales with this promotion
        ];

        return response()->json($stats);
    }

    /**
     * Duplicate a promotion.
     */
    public function duplicate(Promotion $promotion)
    {
        $newPromotion = $promotion->replicate();
        $newPromotion->name = $promotion->name . ' (Copy)';
        $newPromotion->code = null; // Remove code to avoid conflicts
        $newPromotion->usage_count = 0;
        $newPromotion->is_active = false;
        $newPromotion->save();

        return redirect()->route('promotions.edit', $newPromotion)
                        ->with('success', __('messages.promotion_duplicated_successfully'));
    }
}

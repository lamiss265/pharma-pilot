<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Promotion;
use App\Models\Receipt;
use App\Models\BarcodeScan;
use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdvancedSalesController extends Controller
{
    /**
     * Show the advanced sales interface.
     */
    public function index()
    {
        $promotions = Promotion::active()->get();
        $customers = Customer::orderBy('name')->get();
        
        return view('sales.advanced', compact('promotions', 'customers'));
    }

    /**
     * Search products by barcode, name, or active ingredient.
     */
    public function searchProducts(Request $request)
    {
        $query = $request->get('query');
        $searchType = $request->get('type', 'name'); // name, barcode, ingredient
        
        $products = Product::with(['category', 'batches' => function($q) {
            $q->where('expiry_date', '>', now())->orderBy('expiry_date');
        }])
        ->where('quantity', '>', 0);

        switch ($searchType) {
            case 'barcode':
                $products->where('barcode', 'like', "%{$query}%");
                break;
            case 'ingredient':
                $products->where('active_ingredient', 'like', "%{$query}%");
                break;
            default:
                $products->where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('brand', 'like', "%{$query}%")
                      ->orWhereHas('category', function($cat) use ($query) {
                          $cat->where('name', 'like', "%{$query}%");
                      });
                });
        }

        $results = $products->limit(20)->get()->map(function($product) {
            $nearExpiry = $product->batches->where('expiry_date', '<=', now()->addDays(30))->count() > 0;
            
            return [
                'id' => $product->id,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'price' => $product->price,
                'stock' => $product->quantity,
                'category' => $product->category ? $product->category->name : 'N/A',
                'near_expiry' => $nearExpiry,
                'batches' => $product->batches->map(function($batch) {
                    return [
                        'id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                        'quantity' => $batch->quantity,
                        'is_near_expiry' => $batch->expiry_date <= now()->addDays(30)
                    ];
                })
            ];
        });

        return response()->json($results);
    }

    /**
     * Scan barcode and return product details.
     */
    public function scanBarcode(Request $request)
    {
        $barcode = $request->get('barcode');
        $product = Product::where('barcode', $barcode)->first();
        
        // Record the scan
        BarcodeScan::recordScan($barcode, auth()->id(), $product ? $product->id : null);
        
        if ($product) {
            $nearExpiry = $product->batches()->where('expiry_date', '<=', now()->addDays(30))->exists();
            
            return response()->json([
                'found' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'barcode' => $product->barcode,
                    'price' => $product->price,
                    'stock' => $product->quantity,
                    'category' => $product->category ? $product->category->name : 'N/A',
                    'near_expiry' => $nearExpiry,
                    'prescription_required' => $product->prescription_required ?? false,
                    'batches' => $product->batches()->where('expiry_date', '>', now())
                                               ->orderBy('expiry_date')
                                               ->get()
                                               ->map(function($batch) {
                        return [
                            'id' => $batch->id,
                            'batch_number' => $batch->batch_number,
                            'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                            'quantity' => $batch->quantity,
                            'is_near_expiry' => $batch->expiry_date <= now()->addDays(30)
                        ];
                    })
                ]
            ]);
        }
        
        return response()->json([
            'found' => false,
            'message' => __('messages.product_not_found')
        ]);
    }

    /**
     * Calculate cart totals with promotions.
     */
    public function calculateCart(Request $request)
    {
        $items = $request->get('items', []);
        $customerId = $request->get('customer_id');
        $promotionCode = $request->get('promotion_code');
        
        $subtotal = 0;
        $cartItems = [];
        
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;
            
            $itemSubtotal = $product->price * $item['quantity'];
            $subtotal += $itemSubtotal;
            
            $cartItems[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'subtotal' => $itemSubtotal,
                'batch_id' => $item['batch_id'] ?? null
            ];
        }
        
        $discountAmount = 0;
        $discountType = '';
        $appliedPromotion = null;
        
        // Apply promotion if provided
        if ($promotionCode) {
            $promotion = Promotion::where('code', $promotionCode)->active()->first();
            if ($promotion && $promotion->appliesTo(collect($cartItems)->pluck('product'))) {
                $discountAmount = $promotion->calculateDiscount($cartItems, $subtotal);
                $discountType = $promotion->type;
                $appliedPromotion = $promotion;
            }
        }
        
        // Apply customer loyalty discount
        if ($customerId) {
            $customer = Customer::find($customerId);
            if ($customer && $customer->tier_discount > 0) {
                $loyaltyDiscount = ($subtotal * $customer->tier_discount) / 100;
                if ($loyaltyDiscount > $discountAmount) {
                    $discountAmount = $loyaltyDiscount;
                    $discountType = 'loyalty';
                    $appliedPromotion = null;
                }
            }
        }
        
        $taxAmount = ($subtotal - $discountAmount) * 0.1; // 10% tax
        $finalAmount = $subtotal - $discountAmount + $taxAmount;
        
        return response()->json([
            'subtotal' => number_format($subtotal, 2),
            'discount_amount' => number_format($discountAmount, 2),
            'discount_type' => $discountType,
            'tax_amount' => number_format($taxAmount, 2),
            'final_amount' => number_format($finalAmount, 2),
            'applied_promotion' => $appliedPromotion ? $appliedPromotion->name : null,
            'items' => $cartItems
        ]);
    }

    /**
     * Process advanced sale with multiple items.
     */
    public function processAdvancedSale(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.batch_id' => 'nullable|exists:batches,id',
            'customer_id' => 'nullable|exists:customers,id',
            'promotion_code' => 'nullable|string',
            'payment_method' => 'required|in:cash,card,mobile',
            'notes' => 'nullable|string',
            'is_prescription' => 'boolean',
            'prescription_number' => 'nullable|string'
        ]);

        DB::beginTransaction();
        
        try {
            // Calculate totals
            $cartData = $this->calculateCart($request);
            $cartResponse = $cartData->getData(true);
            
            // Create main sale record
            $sale = Sale::create([
                'receipt_number' => Sale::generateReceiptNumber(),
                'user_id' => auth()->id(),
                'client_id' => $validated['customer_id'] ?? null,
                'subtotal' => str_replace(',', '', $cartResponse['subtotal']),
                'total_amount' => str_replace(',', '', $cartResponse['subtotal']),
                'discount_amount' => str_replace(',', '', $cartResponse['discount_amount']),
                'discount_type' => $cartResponse['discount_type'],
                'tax_amount' => str_replace(',', '', $cartResponse['tax_amount']),
                'final_amount' => str_replace(',', '', $cartResponse['final_amount']),
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'is_prescription' => $validated['is_prescription'] ?? false,
                'prescription_number' => $validated['prescription_number'] ?? null,
                'sale_date' => now(),
                'status' => 'completed'
            ]);

            // Create sale items
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                
                // Check stock availability
                $productName = optional($product)->name ?? __('messages.deleted_product');
                if (!$product || $product->quantity < $item['quantity']) {
                    throw new \Exception(__('messages.insufficient_stock_for') . ' ' . $productName);
                }
                
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $product->price * $item['quantity']
                ]);
                
                // Update product stock
                $product->decrement('quantity', $item['quantity']);
                
                // Update batch stock if specified
                if (isset($item['batch_id'])) {
                    $batch = Batch::find($item['batch_id']);
                    if ($batch) {
                        $batch->decrement('quantity_remaining', $item['quantity']);
                    }
                }
            }
            
            // Update customer data
            if ($validated['customer_id']) {
                $customer = Customer::find($validated['customer_id']);
                $customer->increment('total_spent', str_replace(',', '', $cartResponse['final_amount']));
                $customer->increment('total_purchases');
                $customer->addLoyaltyPoints(str_replace(',', '', $cartResponse['final_amount']));
            }
            
            // Update promotion usage
            if ($request->promotion_code) {
                $promotion = Promotion::where('code', $request->promotion_code)->first();
                if ($promotion) {
                    $promotion->incrementUsage();
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'receipt_number' => $sale->receipt_number,
                'message' => __('messages.sale_completed_successfully')
            ]);
            
        } catch (\Throwable $e) {
            DB::rollback();
            Log::error('Advanced sale processing error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Generate and return receipt.
     */
    public function generateReceipt($saleId)
    {
        $sale = Sale::with(['saleItems.product', 'customer', 'user'])->findOrFail($saleId);
        
        $receiptContent = view('receipts.standard', compact('sale'))->render();
        
        $receipt = Receipt::create([
            'sale_id' => $sale->id,
            'receipt_number' => Receipt::generateReceiptNumber(),
            'receipt_content' => $receiptContent,
            'format' => 'html'
        ]);
        
        return response()->json([
            'receipt_id' => $receipt->id,
            'receipt_number' => $receipt->receipt_number,
            'content' => $receiptContent
        ]);
    }

    /**
     * Print receipt.
     */
    public function printReceipt($receiptId)
    {
        $receipt = Receipt::findOrFail($receiptId);
        $receipt->markAsPrinted();
        
        return view('receipts.print', compact('receipt'));
    }

    /**
     * Email receipt to customer.
     */
    public function emailReceipt(Request $request, $receiptId)
    {
        $receipt = Receipt::with('sale.customer')->findOrFail($receiptId);
        $email = $request->get('email') ?: $receipt->sale->customer?->email;
        
        if (!$email) {
            return response()->json(['success' => false, 'message' => __('messages.no_email_provided')]);
        }
        
        // Here you would implement email sending logic
        // For now, we'll just mark as emailed
        $receipt->markAsEmailed();
        
        return response()->json(['success' => true, 'message' => __('messages.receipt_emailed')]);
    }
}

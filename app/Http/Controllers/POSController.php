<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Promotion;
use App\Models\Receipt;
use App\Models\BarcodeScan;
use App\Models\LoyaltyTransaction;
use App\Models\OfflineSale;
use App\Models\ProductBarcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use PDF;

class POSController extends Controller
{
    /**
     * Show the main POS interface.
     */
    public function index()
    {
        $promotions = Promotion::where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();
            
        $customers = Customer::orderBy('name')->limit(100)->get();
        
        return view('pos.index', compact('promotions', 'customers'));
    }

    /**
     * Process a complete sale transaction.
     */
    public function processSale(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.batch_id' => 'nullable|exists:batches,id',
            'payment_method' => 'required|in:cash,card,mobile,loyalty_points,mixed',
            'customer_id' => 'nullable|exists:customers,id',
            'promotion_code' => 'nullable|string',
            'loyalty_points_used' => 'nullable|numeric|min:0',
            'quick_discount_type' => 'nullable|in:amount,percentage',
            'quick_discount_value' => 'nullable|numeric|min:0',
            'device_id' => 'nullable|string',
            'offline_mode' => 'boolean',
            'sale_date' => 'nullable|date'
        ]);

        DB::beginTransaction();
        
        try {
            $items = $request->input('items');
            $customerId = $request->input('customer_id');
            $promotionCode = $request->input('promotion_code');
            $loyaltyPointsUsed = $request->input('loyalty_points_used', 0);
            $quickDiscountType = $request->input('quick_discount_type');
            $quickDiscountValue = floatval($request->input('quick_discount_value', 0));
            $paymentMethod = $request->input('payment_method');
            $deviceId = $request->input('device_id');
            $isOffline = $request->boolean('offline_mode');
            $saleDateInput = $request->input('sale_date');
            $saleDate = $saleDateInput ? Carbon::parse($saleDateInput) : now();

            // Validate stock availability
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                $productName = optional($product)->name ?? __('messages.unknown_product');
                if (!$product || !$product->canSell($item['quantity'])) {
                    throw new \Exception("Insufficient stock for {$productName}");
                }
            }

            // Calculate totals
            $calculation = $this->calculateSaleTotal($items, $customerId, $promotionCode, $loyaltyPointsUsed, $quickDiscountType, $quickDiscountValue);

            if ($isOffline) {
                // Store offline sale
                $offlineSaleData = [
                    'items' => $items,
                    'customer_id' => $customerId,
                    'promotion_code' => $promotionCode,
                    'loyalty_points_used' => $loyaltyPointsUsed,
                    'payment_method' => $paymentMethod,
                    'device_id' => $deviceId,
                    'total_amount' => $calculation['subtotal'],
                    'discount_amount' => $calculation['discount_amount'],
                    'tax_amount' => $calculation['tax_amount'],
                    'final_amount' => $calculation['final_amount'],
                    'loyalty_points_earned' => $calculation['loyalty_points_earned'],
                    'receipt_number' => Sale::generateReceiptNumber()
                ];

                $offlineSale = OfflineSale::storeOfflineSale($offlineSaleData, Auth::id());

                DB::commit();

                return response()->json([
                    'success' => true,
                    'offline_sale_id' => $offlineSale->offline_id,
                    'message' => __('messages.sale_stored_offline'),
                    'calculation' => $calculation
                ]);
            }

            // Create main sale
            // Compute total number of items in sale
            $totalQuantity = array_sum(array_column($items, 'quantity'));
            $sale = Sale::create([
                'receipt_number' => Sale::generateReceiptNumber(),
                'subtotal' => $calculation['subtotal'],
                
                'quantity' => $totalQuantity,
                'total_amount' => $calculation['subtotal'],
                'discount_amount' => $calculation['discount_amount'],
                'tax_amount' => $calculation['tax_amount'],
                'final_amount' => $calculation['final_amount'],
                'payment_method' => $paymentMethod,
                'status' => 'completed',
                'sale_date' => $saleDate,
                'client_id' => $customerId,
                'user_id' => Auth::id(),
                'promotion_id' => $calculation['promotion_id'],
                'loyalty_points_used' => $loyaltyPointsUsed,
                'loyalty_points_earned' => $calculation['loyalty_points_earned'],
                'device_id' => $deviceId
            ]);

            // Create sale items and update stock
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                $unitPrice = $product->price;
                $itemDiscount = $calculation['item_discounts'][$item['product_id']] ?? 0;
                $subtotal = ($unitPrice * $item['quantity']) - $itemDiscount;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'discount_amount' => $itemDiscount,
                    'subtotal' => $subtotal,
                        'total' => $subtotal
                ]);

                // Update product stock
                $product->decrement('quantity', $item['quantity']);

                // Update batch stock if specified
                if (isset($item['batch_id'])) {
                    $batch = \App\Models\Batch::find($item['batch_id']);
                    if ($batch) {
                        $batch->decrement('quantity_remaining', $item['quantity']);
                    }
                }
            }

            // Handle customer loyalty points
            if ($customerId) {
                $customer = Customer::find($customerId);
                
                // Deduct used points
                if ($loyaltyPointsUsed > 0) {
                    LoyaltyTransaction::recordRedeemed(
                        $customerId,
                        $sale->id,
                        $loyaltyPointsUsed,
                        'Points redeemed for purchase'
                    );
                }

                // Award new points
                if ($calculation['loyalty_points_earned'] > 0) {
                    LoyaltyTransaction::recordEarned(
                        $customerId,
                        $sale->id,
                        $calculation['loyalty_points_earned'],
                        'Points earned from purchase'
                    );
                }

                // Update customer totals
                $customer->increment('total_spent', $calculation['final_amount']);
                $customer->increment('total_purchases');
            }

            // Update promotion usage
            if ($calculation['promotion_id']) {
                $promotion = Promotion::find($calculation['promotion_id']);
                $promotion->increment('usage_count');
            }

            // Generate receipt
            $receipt = $this->generateReceipt($sale);

            DB::commit();

            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'receipt_number' => $sale->receipt_number,
                'receipt_url' => $receipt['url'],
                'calculation' => $calculation,
                'message' => __('messages.sale_completed')
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Sale processing error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate sale totals with promotions and discounts.
     */
    public function calculateTotal(Request $request)
    {
        $items = $request->input('items', []);
        $customerId = $request->input('customer_id');
        $promotionCode = $request->input('promotion_code');
        $loyaltyPointsUsed = $request->input('loyalty_points_used', 0);
        $quickDiscountType = $request->input('quick_discount_type');
        $quickDiscountValue = floatval($request->input('quick_discount_value', 0));

        try {
            $calculation = $this->calculateSaleTotal($items, $customerId, $promotionCode, $loyaltyPointsUsed, $quickDiscountType, $quickDiscountValue);
            
            return response()->json([
                'success' => true,
                'calculation' => $calculation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Internal method to calculate sale totals.
     */
    private function calculateSaleTotal($items, $customerId = null, $promotionCode = null, $loyaltyPointsUsed = 0, $quickDiscountType = null, $quickDiscountValue = 0)
    {
        $subtotal = 0;
        $itemDiscounts = [];
        $promotion = null;
        $loyaltyPointsEarned = 0;

        // Calculate subtotal
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $subtotal += $product->price * $item['quantity'];
            }
        }

        // Apply promotion if provided
        $discountAmount = 0;
        $promotionId = null;

        if ($promotionCode) {
            $promotion = Promotion::where('code', $promotionCode)
                ->where('is_active', true)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();

            if ($promotion && $promotion->canApply($subtotal, count($items))) {
                // Calculate discount using Promotion model (items then subtotal)
                $result = $promotion->calculateDiscount($items, $subtotal);
                if (is_array($result)) {
                    $discountAmount = $result['discount'] ?? 0;
                    $itemDiscounts = $result['item_discounts'] ?? [];
                } else {
                    $discountAmount = $result;
                    $itemDiscounts = [];
                }
                $promotionId = $promotion->id;
            }
        }

        // Apply quick discount if provided
        $quickDiscountAmount = 0;
        if ($quickDiscountValue > 0) {
            if ($quickDiscountType === 'percentage') {
                $quickDiscountAmount = ($subtotal - $discountAmount) * ($quickDiscountValue / 100);
            } else {
                $quickDiscountAmount = $quickDiscountValue;
            }
            $discountAmount += $quickDiscountAmount;
        }

        // Apply loyalty points discount
        $loyaltyDiscount = 0;
        if ($loyaltyPointsUsed > 0 && $customerId) {
            $customer = Customer::find($customerId);
            if ($customer && $customer->loyalty_points >= $loyaltyPointsUsed) {
                $loyaltyDiscount = $loyaltyPointsUsed * 0.01; // 1 point = 0.01 currency unit
            } else {
                throw new \Exception('Insufficient loyalty points');
            }
        }

        // Calculate tax (assuming 10% tax rate)
        $taxableAmount = $subtotal - $discountAmount - $loyaltyDiscount;
        $taxAmount = $taxableAmount * 0.10;

        // Calculate final amount
        $finalAmount = $taxableAmount + $taxAmount;

        // Calculate loyalty points earned (1 point per currency unit spent)
        if ($customerId) {
            $loyaltyPointsEarned = floor($finalAmount);
        }

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'loyalty_discount' => round($loyaltyDiscount, 2),
            'tax_amount' => round($taxAmount, 2),
            'final_amount' => round($finalAmount, 2),
            'loyalty_points_earned' => $loyaltyPointsEarned,
            'promotion_id' => $promotionId,
            'item_discounts' => $itemDiscounts
        ];
    }

    /**
     * Generate receipt for a sale.
     */
    private function generateReceipt($sale)
    {
        $sale->load(['saleItems.product', 'customer', 'user']);

        $receiptData = [
            'sale' => $sale,
            'company' => [
                'name' => config('app.name', 'PharmaPilot'),
                'address' => 'Your Pharmacy Address',
                'phone' => 'Your Phone Number',
                'email' => 'your@email.com'
            ],
            'generated_at' => now()
        ];

        // Store receipt data
        $receipt = Receipt::create([
            'sale_id' => $sale->id,
            'receipt_number' => $sale->receipt_number,
            'receipt_data' => $receiptData,
            'format' => 'pdf'
        ]);

        return [
            'id' => $receipt->id,
            'url' => route('pos.receipt', $receipt->id),
            'data' => $receiptData
        ];
    }

    /**
     * Display receipt.
     */
    public function showReceipt($receiptId)
    {
        $receipt = Receipt::with(['sale.saleItems.product', 'sale.customer', 'sale.user'])
            ->findOrFail($receiptId);

        return view('pos.receipt', compact('receipt'));
    }

    /**
     * Download receipt as PDF.
     */
    public function downloadReceipt($receiptId)
    {
        $receipt = Receipt::with(['sale.saleItems.product', 'sale.customer', 'sale.user'])
            ->findOrFail($receiptId);

        $pdf = PDF::loadView('pos.receipt-pdf', compact('receipt'));
        
        return $pdf->download('receipt-' . $receipt->receipt_number . '.pdf');
    }

    /**
     * Sync offline sales.
     */
   /* public function syncOfflineSales()
    {
        try {
            $result = OfflineSale::syncOfflineSales();
            
            return response()->json([
                'success' => true,
                'synced_count' => $result['synced_count'],
                'errors' => $result['errors'],
                'message' => __('messages.offline_sales_synced', ['count' => $result['synced_count']])
            ]);
        } catch (\Exception $e) {
            Log::error('Offline sync error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => __('messages.sync_error'),
                'error' => $e->getMessage()
            ], 500);
        }
    }*/

    /**
     * Get offline sales status.
     */
    public function offlineStatus()
    {
        $unsyncedCount = OfflineSale::getUnsyncedCount();
        $userOfflineSales = OfflineSale::getUserOfflineSales(Auth::id(), false);

        return response()->json([
            'success' => true,
            'unsynced_count' => $unsyncedCount,
            'user_unsynced' => $userOfflineSales->count(),
            'last_sync' => $userOfflineSales->first() ? $userOfflineSales->first()->created_at : null
        ]);
    }

    /**
     * Apply quick discount.
     */
    public function applyQuickDiscount(Request $request)
    {
        $request->validate([
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0'
        ]);

        $type = $request->input('type');
        $value = $request->input('value');
        $subtotal = $request->input('subtotal');

        if ($type === 'percentage') {
            $discount = ($subtotal * $value) / 100;
        } else {
            $discount = min($value, $subtotal);
        }

        return response()->json([
            'success' => true,
            'discount_amount' => round($discount, 2),
            'final_amount' => round($subtotal - $discount, 2)
        ]);
    }

    /**
     * Get low stock alerts for POS.
     */
    public function getLowStockAlerts()
    {
        $lowStockProducts = Product::where('low_stock_alert', true)
            ->whereRaw('quantity <= reorder_point')
            ->with('category')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'alerts' => $lowStockProducts->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category ? $product->category->name : null,
                    'current_stock' => $product->quantity,
                    'reorder_point' => $product->reorder_point,
                    'status' => $product->quantity == 0 ? 'out_of_stock' : 'low_stock'
                ];
            })
        ]);
    }

    /**
     * Email receipt to customer.
     */
    public function emailReceipt(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id'
        ]);

        try {
            $sale = Sale::with(['customer', 'receipt'])->findOrFail($request->sale_id);
            
            if (!$sale->customer || !$sale->customer->email) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.customer_email_not_available')
                ]);
            }

            // Here you would integrate with your email service
            // For example, using Laravel Mail:
            // Mail::to($sale->customer->email)->send(new ReceiptMail($sale));
            
            // For now, we'll simulate sending
            // In production, replace this with actual email sending logic
            
            return response()->json([
                'success' => true,
                'message' => __('messages.email_sent_successfully')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.email_send_failed')
            ]);
        }
    }

    /**
     * Send receipt via SMS to customer.
     */
    public function smsReceipt(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id'
        ]);

        try {
            $sale = Sale::with(['customer', 'receipt'])->findOrFail($request->sale_id);
            
            if (!$sale->customer || !$sale->customer->phone) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.customer_phone_not_available')
                ]);
            }

            // Here you would integrate with your SMS service
            // For example, using Twilio, Nexmo, or local SMS gateway
            
            // Create SMS message
            $message = __('messages.sms_receipt_message', [
                'receipt_number' => $sale->receipt->receipt_number,
                'amount' => number_format($sale->final_amount, 2),
                'date' => $sale->created_at->format('Y-m-d H:i')
            ]);
            
            // For now, we'll simulate sending
            // In production, replace this with actual SMS sending logic
            
            return response()->json([
                'success' => true,
                'message' => __('messages.sms_sent_successfully')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.sms_send_failed')
            ]);
        }
    }
    
    /**
     * Sync offline sales to the server.
     */
    public function syncOfflineSales(Request $request)
    {
        try {
            // Get all offline sales that haven't been synced
            $offlineSales = OfflineSale::where('synced', false)->get();
            
            $syncedCount = 0;
            
            DB::beginTransaction();
            
            foreach ($offlineSales as $offlineSale) {
                // Convert offline sale to regular sale
                $sale = Sale::create([
                    'customer_id' => $offlineSale->customer_id,
                    'user_id' => $offlineSale->user_id,
                    'subtotal' => $offlineSale->subtotal,
                    'tax_amount' => $offlineSale->tax_amount,
                    'discount_amount' => $offlineSale->discount_amount,
                    'final_amount' => $offlineSale->final_amount,
                    'payment_method' => $offlineSale->payment_method,
                    'created_at' => $offlineSale->created_at,
                    'updated_at' => now()
                ]);
                
                // Create sale items
                $saleItemsData = json_decode($offlineSale->sale_items, true);
                foreach ($saleItemsData as $itemData) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'total_price' => $itemData['total_price']
                    ]);
                }
                
                // Mark as synced
                $offlineSale->update(['synced' => true]);
                $syncedCount++;
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => __('messages.offline_sales_synced'),
                'synced_count' => $syncedCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Offline sync error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => __('messages.sync_error')
            ]);
        }
    }

    /**
     * Search products for POS system.
     */
    public function searchProducts(Request $request)
    {
        $query = $request->input('query');
        
        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'products' => []
            ]);
        }

        $products = Product::where('name', 'LIKE', "%{$query}%")
            ->orWhere('barcode', 'LIKE', "%{$query}%")
            ->orWhere('brand', 'LIKE', "%{$query}%")
            ->where('quantity', '>', 0)
            ->with('category')
            ->limit(20)
            ->get();

        $formattedProducts = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'brand' => $product->brand,
                'price' => $product->price,
                'quantity' => $product->quantity,
                'category' => $product->category ? $product->category->name : null,
                'supplier' => $product->supplier,
                'image' => $product->image ? asset('storage/' . $product->image) : null
            ];
        });

        return response()->json([
            'success' => true,
            'products' => $formattedProducts
        ]);
    }

    /**
     * Scan barcode and return product information.
     */
    public function scanBarcode(Request $request)
    {
        $request->validate([
        'barcode' => 'required|string'
    ]);

    $barcode = $request->input('barcode');

    // Prepare possible barcode variations (include trimmed leading zeros)
    $possibleBarcodes = [$barcode];
    $trimmed = ltrim($barcode, '0');
    if ($trimmed !== $barcode) {
        $possibleBarcodes[] = $trimmed;
    }

    // First try to find by main barcode (with variations)
    $product = Product::whereIn('barcode', $possibleBarcodes)
        ->where('quantity', '>', 0)
        ->with(['category', 'batches' => function($q) {
            $q->where('quantity_remaining', '>', 0)
                ->where('expiry_date', '>', now())
                ->orderBy('expiry_date', 'asc');
        }])
        ->first();

    // If not found, try product_barcodes table
    if (!$product) {
        $productBarcode = ProductBarcode::whereIn('barcode', $possibleBarcodes)->first();
        if ($productBarcode) {
            $product = Product::where('id', $productBarcode->product_id)
                ->where('quantity', '>', 0)
                ->with(['category', 'batches' => function($q) {
                    $q->where('quantity_remaining', '>', 0)
                        ->where('expiry_date', '>', now())
                        ->orderBy('expiry_date', 'asc');
                }])
                ->first();
        }
    }

    if (!$product) {
        return response()->json([
            'success' => false,
            'message' => __('messages.product_not_found')
        ], 404);
    }

    // Log barcode scan
    BarcodeScan::create([
        'barcode' => $barcode,
        'product_id' => $product->id,
        'user_id' => Auth::id(),
        'scanned_at' => now(),
        'device_info' => $request->header('User-Agent')
    ]);

    $formattedProduct = [
        'id' => $product->id,
        'name' => $product->name,
        'barcode' => $product->barcode,
        'brand' => $product->brand,
        'price' => $product->price,
        'quantity' => $product->quantity,
        'category' => $product->category ? $product->category->name : null,
        'supplier' => $product->supplier,
        'image' => $product->image ? asset('storage/' . $product->image) : null,
        'batches' => $product->batches->map(function ($batch) {
            return [
                'id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'quantity' => $batch->quantity,
                'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                'manufacturing_date' => $batch->manufacturing_date ? $batch->manufacturing_date->format('Y-m-d') : null
            ];
        })
    ];

    return response()->json([
        'success' => true,
        'product' => $formattedProduct
    ]);
}

/**
 * Get product by ID for POS.
 */
public function getProduct($productId)
{
    $product = Product::where('id', $productId)
        ->where('quantity', '>', 0)
        ->with(['category', 'batches' => function($q) {
            $q->where('quantity_remaining', '>', 0)
                ->where('expiry_date', '>', now())
                ->orderBy('expiry_date', 'asc');
        }])
        ->first();

    if (!$product) {
        return response()->json([
            'success' => false,
            'message' => __('messages.product_not_found')
        ], 404);
    }

    $formattedProduct = [
        'id' => $product->id,
        'name' => $product->name,
        'barcode' => $product->barcode,
        'brand' => $product->brand,
        'price' => $product->price,
        'quantity' => $product->quantity,
        'category' => $product->category ? $product->category->name : null,
        'supplier' => $product->supplier,
        'image' => $product->image ? asset('storage/' . $product->image) : null,
        'batches' => $product->batches->map(function ($batch) {
            return [
                'id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'quantity' => $batch->quantity,
                'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                'manufacturing_date' => $batch->manufacturing_date ? $batch->manufacturing_date->format('Y-m-d') : null
            ];
        })
    ];

    return response()->json([
        'success' => true,
        'product' => $formattedProduct
    ]);
}

}
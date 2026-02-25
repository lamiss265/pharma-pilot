<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\BarcodeScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BarcodeController extends Controller
{
    /**
     * Scan a barcode and return product information.
     */
    public function scan(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);

        $barcode = $request->input('barcode');
        $userId = Auth::id();

        try {
            // Search for product by barcode
            $product = Product::searchByBarcode($barcode);

            if ($product) {
                // Record successful scan
                BarcodeScan::recordScan($barcode, $userId, $product->id);

                // Load relationships
                $product->load(['category', 'primaryBarcode', 'batches' => function($q) {
                    $q->where('expiry_date', '>', now())->orderBy('expiry_date');
                }]);

                return response()->json([
                    'success' => true,
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'brand' => $product->brand,
                        'price' => $product->price,
                        'quantity' => $product->quantity,
                        'available_stock' => $product->getAvailableStock(),
                        'category' => $product->category ? $product->category->name : null,
                        'barcode' => $barcode,
                        'is_low_stock' => $product->isLowStock(),
                        'is_near_expiry' => $product->isNearExpiry(),
                        'batches' => $product->batches->map(function($batch) {
                            return [
                                'id' => $batch->id,
                                'batch_number' => $batch->batch_number,
                                'quantity' => $batch->quantity,
                                'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                                'days_to_expiry' => $batch->expiry_date->diffInDays(now())
                            ];
                        })
                    ]
                ]);
            } else {
                // Record failed scan
                BarcodeScan::recordScan($barcode, $userId);

                return response()->json([
                    'success' => false,
                    'message' => __('messages.product_not_found'),
                    'barcode' => $barcode,
                    'suggestions' => $this->getSuggestions($barcode)
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Barcode scan error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => __('messages.scan_error'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search products with autocomplete.
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2'
        ]);

        $query = $request->input('query');
        $limit = $request->input('limit', 10);

        try {
            $products = Product::fuzzySearch($query, $limit);

            return response()->json([
                'success' => true,
                'products' => $products->map(function($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'brand' => $product->brand,
                        'price' => $product->price,
                        'quantity' => $product->quantity,
                        'available_stock' => $product->getAvailableStock(),
                        'category' => $product->category ? $product->category->name : null,
                        'barcode' => $product->primaryBarcode ? $product->primaryBarcode->barcode : $product->barcode,
                        'is_low_stock' => $product->isLowStock(),
                        'is_near_expiry' => $product->isNearExpiry()
                    ];
                })
            ]);
        } catch (\Exception $e) {
            Log::error('Product search error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => __('messages.search_error'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a new barcode for a product.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:EAN13,UPC,CODE128,QR,CUSTOM'
        ]);

        try {
            $product = Product::findOrFail($request->product_id);
            $type = $request->input('type');

            // Generate barcode
            $barcode = ProductBarcode::generateBarcode($type);

            // Ensure uniqueness
            while (ProductBarcode::where('barcode', $barcode)->exists()) {
                $barcode = ProductBarcode::generateBarcode($type);
            }

            // Create barcode record
            $productBarcode = $product->addBarcode($barcode, $type, $request->boolean('is_primary'));

            return response()->json([
                'success' => true,
                'barcode' => $barcode,
                'type' => $type,
                'is_primary' => $productBarcode->is_primary,
                'message' => __('messages.barcode_generated')
            ]);

        } catch (\Exception $e) {
            Log::error('Barcode generation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => __('messages.barcode_generation_error'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get barcode scan history.
     */
    public function scanHistory(Request $request)
    {
        $userId = Auth::id();
        $limit = $request->input('limit', 50);

        $scans = BarcodeScan::with(['product', 'user'])
            ->where('user_id', $userId)
            ->orderBy('scanned_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'scans' => $scans->map(function($scan) {
                return [
                    'id' => $scan->id,
                    'barcode' => $scan->barcode,
                    'found' => $scan->found,
                    'scanned_at' => $scan->scanned_at->format('Y-m-d H:i:s'),
                    'product' => $scan->product ? [
                        'id' => $scan->product->id,
                        'name' => $scan->product->name,
                        'brand' => $scan->product->brand
                    ] : null
                ];
            })
        ]);
    }

    /**
     * Get product suggestions based on failed barcode scan.
     */
    private function getSuggestions($barcode)
    {
        // Try to find similar products based on partial barcode match
        $suggestions = Product::where('barcode', 'like', '%' . substr($barcode, 0, 6) . '%')
            ->orWhere('barcode', 'like', '%' . substr($barcode, -6) . '%')
            ->limit(5)
            ->get(['id', 'name', 'brand', 'barcode']);

        return $suggestions->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand,
                'barcode' => $product->barcode
            ];
        });
    }

    /**
     * Validate barcode format.
     */
    public function validateBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
            'type' => 'required|in:EAN13,UPC,CODE128,QR,CUSTOM'
        ]);

        $barcode = $request->input('barcode');
        $type = $request->input('type');

        $isValid = $this->isValidBarcode($barcode, $type);

        return response()->json([
            'success' => true,
            'valid' => $isValid,
            'barcode' => $barcode,
            'type' => $type
        ]);
    }

    /**
     * Check if barcode format is valid.
     */
    private function isValidBarcode($barcode, $type)
    {
        switch ($type) {
            case 'EAN13':
                return strlen($barcode) === 13 && is_numeric($barcode) && $this->validateEAN13($barcode);
            case 'UPC':
                return strlen($barcode) === 12 && is_numeric($barcode) && $this->validateUPC($barcode);
            case 'CODE128':
                return strlen($barcode) >= 6;
            case 'QR':
                return strlen($barcode) >= 1;
            case 'CUSTOM':
                return strlen($barcode) >= 4;
            default:
                return false;
        }
    }

    /**
     * Validate EAN13 check digit.
     */
    private function validateEAN13($barcode)
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $barcode[$i] * (($i % 2 === 0) ? 1 : 3);
        }
        $checkDigit = (10 - ($sum % 10)) % 10;
        return $checkDigit == $barcode[12];
    }

    /**
     * Validate UPC check digit.
     */
    private function validateUPC($barcode)
    {
        $sum = 0;
        for ($i = 0; $i < 11; $i++) {
            $sum += $barcode[$i] * (($i % 2 === 0) ? 3 : 1);
        }
        $checkDigit = (10 - ($sum % 10)) % 10;
        return $checkDigit == $barcode[11];
    }
}

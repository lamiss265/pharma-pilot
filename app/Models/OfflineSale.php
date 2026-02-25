<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OfflineSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'offline_id',
        'sale_data',
        'user_id',
        'synced',
        'sale_timestamp'
    ];

    protected $casts = [
        'sale_data' => 'array',
        'synced' => 'boolean',
        'sale_timestamp' => 'datetime'
    ];

    /**
     * Get the user who made the offline sale.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Store an offline sale.
     */
    public static function storeOfflineSale($saleData, $userId)
    {
        $offlineId = 'OFF_' . date('YmdHis') . '_' . uniqid();

        return self::create([
            'offline_id' => $offlineId,
            'sale_data' => $saleData,
            'user_id' => $userId,
            'synced' => false,
            'sale_timestamp' => now()
        ]);
    }

    /**
     * Sync offline sales to the main sales system.
     */
    public static function syncOfflineSales()
    {
        $offlineSales = self::where('synced', false)->get();
        $syncedCount = 0;
        $errors = [];

        foreach ($offlineSales as $offlineSale) {
            try {
                DB::beginTransaction();

                $saleData = $offlineSale->sale_data;
                
                // Create the main sale record
                $sale = Sale::create([
                    'receipt_number' => $saleData['receipt_number'] ?? Sale::generateReceiptNumber(),
                    'total_amount' => $saleData['total_amount'],
                    'discount_amount' => $saleData['discount_amount'] ?? 0,
                    'tax_amount' => $saleData['tax_amount'] ?? 0,
                    'final_amount' => $saleData['final_amount'],
                    'payment_method' => $saleData['payment_method'],
                    'notes' => $saleData['notes'] ?? null,
                    'status' => 'completed',
                    'sale_date' => $offlineSale->sale_timestamp,
                    'customer_id' => $saleData['customer_id'] ?? null,
                    'user_id' => $offlineSale->user_id,
                    'offline_sale_id' => $offlineSale->offline_id,
                    'device_id' => $saleData['device_id'] ?? null
                ]);

                // Create sale items
                if (isset($saleData['items'])) {
                    foreach ($saleData['items'] as $itemData) {
                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'product_id' => $itemData['product_id'],
                            'batch_id' => $itemData['batch_id'] ?? null,
                            'quantity' => $itemData['quantity'],
                            'unit_price' => $itemData['unit_price'],
                            'discount_amount' => $itemData['discount_amount'] ?? 0,
                            'subtotal' => $itemData['subtotal']
                        ]);

                        // Update product stock
                        $product = Product::find($itemData['product_id']);
                        if ($product) {
                            $product->decrement('quantity', $itemData['quantity']);
                        }
                    }
                }

                // Handle customer loyalty points
                if ($sale->customer_id && isset($saleData['loyalty_points_earned'])) {
                    LoyaltyTransaction::recordEarned(
                        $sale->customer_id,
                        $sale->id,
                        $saleData['loyalty_points_earned'],
                        'Points earned from offline sale'
                    );
                }

                // Mark as synced
                $offlineSale->update(['synced' => true]);
                $syncedCount++;

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                $errors[] = [
                    'offline_id' => $offlineSale->offline_id,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'synced_count' => $syncedCount,
            'errors' => $errors
        ];
    }

    /**
     * Get unsynced offline sales count.
     */
    public static function getUnsyncedCount()
    {
        return self::where('synced', false)->count();
    }

    /**
     * Get offline sales for a specific user.
     */
    public static function getUserOfflineSales($userId, $synced = null)
    {
        $query = self::where('user_id', $userId);
        
        if ($synced !== null) {
            $query->where('synced', $synced);
        }

        return $query->orderBy('sale_timestamp', 'desc')->get();
    }

    /**
     * Clear old synced offline sales.
     */
    public static function clearOldSyncedSales($days = 30)
    {
        return self::where('synced', true)
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
    }
}

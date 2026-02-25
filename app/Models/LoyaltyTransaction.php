<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'sale_id',
        'type',
        'points',
        'balance_after',
        'description'
    ];

    protected $casts = [
        'points' => 'decimal:2',
        'balance_after' => 'decimal:2'
    ];

    /**
     * Get the customer that owns the loyalty transaction.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the sale associated with the loyalty transaction.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Record points earned from a sale.
     */
    public static function recordEarned($customerId, $saleId, $points, $description = null)
    {
        $customer = Customer::find($customerId);
        $newBalance = $customer->loyalty_points + $points;

        $transaction = self::create([
            'customer_id' => $customerId,
            'sale_id' => $saleId,
            'type' => 'earned',
            'points' => $points,
            'balance_after' => $newBalance,
            'description' => $description ?: "Points earned from sale"
        ]);

        // Update customer balance
        $customer->update(['loyalty_points' => $newBalance]);

        return $transaction;
    }

    /**
     * Record points redeemed.
     */
    public static function recordRedeemed($customerId, $saleId, $points, $description = null)
    {
        $customer = Customer::find($customerId);
        $newBalance = max(0, $customer->loyalty_points - $points);

        $transaction = self::create([
            'customer_id' => $customerId,
            'sale_id' => $saleId,
            'type' => 'redeemed',
            'points' => -$points,
            'balance_after' => $newBalance,
            'description' => $description ?: "Points redeemed"
        ]);

        // Update customer balance
        $customer->update(['loyalty_points' => $newBalance]);

        return $transaction;
    }

    /**
     * Record points adjustment.
     */
    public static function recordAdjustment($customerId, $points, $description)
    {
        $customer = Customer::find($customerId);
        $newBalance = max(0, $customer->loyalty_points + $points);

        $transaction = self::create([
            'customer_id' => $customerId,
            'type' => 'adjusted',
            'points' => $points,
            'balance_after' => $newBalance,
            'description' => $description
        ]);

        // Update customer balance
        $customer->update(['loyalty_points' => $newBalance]);

        return $transaction;
    }

    /**
     * Record expired points.
     */
    public static function recordExpired($customerId, $points, $description = null)
    {
        $customer = Customer::find($customerId);
        $newBalance = max(0, $customer->loyalty_points - $points);

        $transaction = self::create([
            'customer_id' => $customerId,
            'type' => 'expired',
            'points' => -$points,
            'balance_after' => $newBalance,
            'description' => $description ?: "Points expired"
        ]);

        // Update customer balance
        $customer->update(['loyalty_points' => $newBalance]);

        return $transaction;
    }

    /**
     * Get transactions for a specific customer.
     */
    public static function getCustomerTransactions($customerId, $limit = 50)
    {
        return self::where('customer_id', $customerId)
            ->with('sale')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

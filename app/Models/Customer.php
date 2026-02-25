<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_number',
        'name',
        'email',
        'phone',
        'address',
        'date_of_birth',
        'gender',
        'loyalty_points',
        'total_spent',
        'total_purchases',
        'preferred_language',
        'email_notifications',
        'sms_notifications'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'loyalty_points' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean'
    ];

    /**
     * Get the sales for the customer.
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Generate unique customer number.
     */
    public static function generateCustomerNumber()
    {
        do {
            $number = 'CUST' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('customer_number', $number)->exists());

        return $number;
    }

    /**
     * Add loyalty points.
     */
    public function addLoyaltyPoints($amount)
    {
        $points = floor($amount / 10); // 1 point per 10 currency units
        $this->increment('loyalty_points', $points);
        return $points;
    }

    /**
     * Redeem loyalty points.
     */
    public function redeemLoyaltyPoints($points)
    {
        if ($this->loyalty_points >= $points) {
            $this->decrement('loyalty_points', $points);
            return true;
        }
        return false;
    }

    /**
     * Get customer tier based on total spent.
     */
    public function getTierAttribute()
    {
        if ($this->total_spent >= 10000) {
            return 'platinum';
        } elseif ($this->total_spent >= 5000) {
            return 'gold';
        } elseif ($this->total_spent >= 1000) {
            return 'silver';
        }
        return 'bronze';
    }

    /**
     * Get discount percentage based on tier.
     */
    public function getTierDiscountAttribute()
    {
        switch ($this->tier) {
            case 'platinum':
                return 10;
            case 'gold':
                return 7;
            case 'silver':
                return 5;
            default:
                return 0;
        }
    }
}

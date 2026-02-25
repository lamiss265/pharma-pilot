<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'minimum_amount',
        'minimum_items',
        'start_date',
        'end_date',
        'is_active',
        'usage_limit',
        'usage_count',
        'applicable_products',
        'applicable_categories'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'applicable_products' => 'array',
        'applicable_categories' => 'array'
    ];

    /**
     * Check if promotion is currently active.
     */
    public function isActive()
    {
        return $this->is_active 
            && Carbon::now()->between($this->start_date, $this->end_date)
            && ($this->usage_limit === null || $this->usage_count < $this->usage_limit);
    }

    /**
     * Check if promotion can apply based on subtotal and item count.
     */
    public function canApply($subtotal, $itemCount)
    {
        if (!$this->isActive()) {
            return false;
        }
        if ($this->minimum_amount && $subtotal < $this->minimum_amount) {
            return false;
        }
        if ($this->minimum_items && $itemCount < $this->minimum_items) {
            return false;
        }
        return true;
    }

    /**
     * Check if promotion applies to given products.
     */
    public function appliesTo($products)
    {
        if (empty($this->applicable_products) && empty($this->applicable_categories)) {
            return true; // Applies to all products
        }

        foreach ($products as $product) {
            // Check if product ID is in applicable products
            if (!empty($this->applicable_products) && in_array($product->id, $this->applicable_products)) {
                return true;
            }

            // Check if product category is in applicable categories
            if (!empty($this->applicable_categories) && $product->category_id && in_array($product->category_id, $this->applicable_categories)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate discount amount for given cart.
     */
    public function calculateDiscount($cartItems, $cartTotal)
    {
        if (!$this->isActive()) {
            return 0;
        }

        // Check minimum amount
        if ($this->minimum_amount && $cartTotal < $this->minimum_amount) {
            return 0;
        }

        // Check minimum items
        if ($this->minimum_items && count($cartItems) < $this->minimum_items) {
            return 0;
        }

        switch ($this->type) {
            case 'percentage':
                return ($cartTotal * $this->value) / 100;
            
            case 'fixed':
                return min($this->value, $cartTotal);
            
            case 'bogo':
                // Buy one get one - calculate based on cheapest items
                $applicableItems = collect($cartItems)->filter(function ($item) {
                    return $this->appliesTo([$item['product']]);
                })->sortBy('unit_price');

                $discount = 0;
                $pairsCount = floor($applicableItems->count() / 2);
                $cheapestItems = $applicableItems->take($pairsCount);
                
                foreach ($cheapestItems as $item) {
                    $discount += $item['unit_price'];
                }
                
                return $discount;
            
            case 'loyalty':
                // Loyalty discount - only for customers with enough points
                return 0; // Handle in controller with customer context
            
            default:
                return 0;
        }
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    /**
     * Scope for active promotions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('start_date', '<=', Carbon::now())
                    ->where('end_date', '>=', Carbon::now())
                    ->where(function ($q) {
                        $q->whereNull('usage_limit')
                          ->orWhereRaw('usage_count < usage_limit');
                    });
    }
}

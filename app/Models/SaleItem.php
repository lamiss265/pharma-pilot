<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'batch_id',
        'quantity',
        'unit_price',
        'discount_amount',
        'subtotal',
        'total'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    /**
     * Get the sale that owns the sale item.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the product that belongs to the sale item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the batch that belongs to the sale item.
     */
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * Calculate the final price after discount.
     */
    public function getFinalPriceAttribute()
    {
        return $this->subtotal - $this->discount_amount;
    }
}

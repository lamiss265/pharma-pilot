<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    // Connect to parapharma database
    protected $connection = "mysql";

    // Fillable fields
    protected $fillable = [
        "product_id",
        "quantity",
        "receipt_number",
        "price",
        "subtotal",
        "total_amount",
        "discount_amount",
        "discount_type",
        "tax_amount",
        "final_amount",
        "payment_method",
        "notes",
        "is_prescription",
        "prescription_number",
        "status",
        "sale_date",
        "client_id",
        "user_id"
    ];

    // Date fields
    protected $dates = [
        "sale_date"
    ];

    // Cast attributes
    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'is_prescription' => 'boolean',
        'sale_date' => 'date'
    ];

    // Relationships


    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'client_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Product of the sale (basic sales system)
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class);
    }

    // Calculate total price
    public function getTotalPrice()
    {
        // Sum subtotals of sale items
        return $this->saleItems->sum(function($item) {
            return $item->unit_price * $item->quantity;
        });
    }

    /**
     * Generate unique receipt number.
     */
    public static function generateReceiptNumber()
    {
        do {
            $number = 'SAL' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('receipt_number', $number)->exists());

        return $number;
    }

    /**
     * Calculate final amount after discounts and taxes.
     */
    public function calculateFinalAmount()
    {
        $amount = $this->total_amount - $this->discount_amount + $this->tax_amount;
        $this->final_amount = max(0, $amount);
        return $this->final_amount;
    }

    /**
     * Apply discount to sale.
     */
    public function applyDiscount($amount, $type = 'fixed')
    {
        $this->discount_amount = $amount;
        $this->discount_type = $type;
        $this->calculateFinalAmount();
    }

    /**
     * Check if sale is returnable (within return period).
     */
    public function isReturnable($days = 30)
    {
        return $this->status === 'completed' && 
               $this->created_at->diffInDays(now()) <= $days;
    }

    /**
     * Get formatted receipt number.
     */
    public function getFormattedReceiptNumberAttribute()
    {
        return $this->receipt_number ?: 'N/A';
    }

    /**
     * Scope for completed sales.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for prescription sales.
     */
    public function scopePrescription($query)
    {
        return $query->where('is_prescription', true);
    }

    
    

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_number',
        'expiry_date',
        'manufacturing_date',
        'quantity_received',
        'quantity_remaining'
    ];

    protected $dates = ['expiry_date', 'manufacturing_date'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Validation rules
     */
    public static $rules = [
        'batch_number' => 'required|string|unique:batches',
        'expiry_date' => 'required|date|after:manufacturing_date|after:today',
        'manufacturing_date' => 'required|date|before:expiry_date|before_or_equal:today',
        'quantity_received' => 'required|integer|min:1',
    ];

    /**
     * Custom validation messages
     */
    public static $messages = [
        'expiry_date.after' => 'Expiry date must be after manufacturing date and in the future.',
        'manufacturing_date.before' => 'Manufacturing date must be before expiry date.',
        'manufacturing_date.before_or_equal' => 'Manufacturing date cannot be in the future.',
    ];
}

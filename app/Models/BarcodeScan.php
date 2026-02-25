<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarcodeScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'barcode',
        'product_id',
        'user_id',
        'found',
        'scanned_at'
    ];

    protected $casts = [
        'found' => 'boolean',
        'scanned_at' => 'datetime'
    ];

    /**
     * Get the product that was scanned.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who performed the scan.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a barcode scan.
     */
    public static function recordScan($barcode, $userId, $productId = null)
    {
        return self::create([
            'barcode' => $barcode,
            'user_id' => $userId,
            'product_id' => $productId,
            'found' => $productId !== null,
            'scanned_at' => now()
        ]);
    }
}

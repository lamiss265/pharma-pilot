<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBarcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'barcode',
        'type',
        'is_primary'
    ];

    protected $casts = [
        'is_primary' => 'boolean'
    ];

    /**
     * Get the product that owns the barcode.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Set this barcode as primary and unset others for the same product.
     */
    public function setPrimary()
    {
        // Unset all other primary barcodes for this product
        self::where('product_id', $this->product_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        // Set this one as primary
        $this->update(['is_primary' => true]);
    }

    /**
     * Find product by barcode.
     */
    public static function findProductByBarcode($barcode)
    {
        $productBarcode = self::where('barcode', $barcode)->first();
        return $productBarcode ? $productBarcode->product : null;
    }

    /**
     * Generate barcode based on type.
     */
    public static function generateBarcode($type = 'EAN13')
    {
        switch ($type) {
            case 'EAN13':
                return self::generateEAN13();
            case 'UPC':
                return self::generateUPC();
            case 'CODE128':
                return self::generateCODE128();
            default:
                return self::generateCustom();
        }
    }

    /**
     * Generate EAN13 barcode.
     */
    private static function generateEAN13()
    {
        $code = str_pad(mt_rand(1, 999999999999), 12, '0', STR_PAD_LEFT);
        
        // Calculate check digit
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $code[$i] * (($i % 2 === 0) ? 1 : 3);
        }
        $checkDigit = (10 - ($sum % 10)) % 10;
        
        return $code . $checkDigit;
    }

    /**
     * Generate UPC barcode.
     */
    private static function generateUPC()
    {
        $code = str_pad(mt_rand(1, 99999999999), 11, '0', STR_PAD_LEFT);
        
        // Calculate check digit
        $sum = 0;
        for ($i = 0; $i < 11; $i++) {
            $sum += $code[$i] * (($i % 2 === 0) ? 3 : 1);
        }
        $checkDigit = (10 - ($sum % 10)) % 10;
        
        return $code . $checkDigit;
    }

    /**
     * Generate CODE128 barcode.
     */
    private static function generateCODE128()
    {
        return 'C128' . str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
    }

    /**
     * Generate custom barcode.
     */
    private static function generateCustom()
    {
        return 'PP' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}

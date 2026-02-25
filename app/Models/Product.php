<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Product extends Model
{
    use HasFactory;

    // Connect to parapharma database
    protected $connection = "mysql";

    // Fillable fields
    protected $fillable = [
        "name",
        "dci",
        "dosage_form",
        "therapeutic_class",
        "composition",
        "indications",
        "contraindications",
        "side_effects",
        "storage_conditions",
        "barcode",
        "batch_number",
        "manufacturing_date",
        "quantity",
        "reorder_point",
        "optimal_stock_level",
        "expiry_date",
        "supplier",
        "price",
        "category_id"
    ];

    // Date fields
    protected $dates = [
        "expiry_date",
        "manufacturing_date"
    ];

    // Relationships
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'product_supplier')->withTimestamps();
    }

    public function barcodes()
    {
        return $this->hasMany(ProductBarcode::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function primaryBarcode()
    {
        return $this->hasOne(ProductBarcode::class)->where('is_primary', true);
    }

    // Business logic methods
    public function isLowStock($threshold = null)
    {
        $threshold = $threshold ?? $this->reorder_point;
        return $this->quantity <= $threshold;
    }

    public function needsReordering()
    {
        return $this->quantity <= $this->reorder_point;
    }

    public function getReorderQuantity()
    {
        return $this->optimal_stock_level - $this->quantity;
    }

    public function isNearExpiry($days = 30)
    {
        $expiryDate = Carbon::parse($this->expiry_date);
        $now = Carbon::now();
        
        return $now->diffInDays($expiryDate, false) <= $days && $now->diffInDays($expiryDate, false) >= 0;
    }

    public function isExpired()
    {
        return Carbon::parse($this->expiry_date)->isPast();
    }

    public function decrementStock($quantity)
    {
        if ($this->quantity >= $quantity) {
            $this->quantity -= $quantity;
            $this->save();
            return true;
        }
        return false;
    }
    
    // Barcode validation
    public static function validateBarcode($barcode)
    {
        // Check if barcode is 13 digits (EAN-13)
        if (!preg_match('/^[0-9]{13}$/', $barcode)) {
            return false;
        }
        
        // EAN-13 checksum validation
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += ($i % 2 == 0) ? (int)$barcode[$i] : (int)$barcode[$i] * 3;
        }
        
        $checkDigit = (10 - ($sum % 10)) % 10;
        
        return $checkDigit == (int)$barcode[12];
    }

    // Scan barcode method
    public static function scanBarcode($barcode)
    {
        if (!self::validateBarcode($barcode)) {
            return null;
        }

        return self::where('barcode', $barcode)->first();
    }

    // Get shelf life in days
    public function getShelfLife()
    {
        if (!$this->manufacturing_date || !$this->expiry_date) {
            return null;
        }

        $manufacturingDate = Carbon::parse($this->manufacturing_date);
        $expiryDate = Carbon::parse($this->expiry_date);
        
        return $manufacturingDate->diffInDays($expiryDate);
    }

    // Get remaining shelf life in days
    public function getRemainingShelfLife()
    {
        if (!$this->expiry_date) {
            return null;
        }

        $now = Carbon::now();
        $expiryDate = Carbon::parse($this->expiry_date);
        
        return $now->diffInDays($expiryDate, false);
    }

    // Get remaining shelf life as percentage
    public function getRemainingShelfLifePercentage()
    {
        $shelfLife = $this->getShelfLife();
        $remainingShelfLife = $this->getRemainingShelfLife();
        
        if (!$shelfLife || !$remainingShelfLife || $remainingShelfLife < 0) {
            return 0;
        }
        
        return round(($remainingShelfLife / $shelfLife) * 100);
    }

    // Get products that need reordering
    public static function getProductsNeedingReorder()
    {
        return self::where('quantity', '<=', \DB::raw('reorder_point'))->get();
    }

    // Advanced search methods
    public static function searchByBarcode($barcode)
    {
        // First check direct barcode field
        $product = self::where('barcode', $barcode)->first();
        
        if (!$product) {
            // Check in product_barcodes table
            $productBarcode = ProductBarcode::where('barcode', $barcode)->first();
            if ($productBarcode) {
                $product = $productBarcode->product;
            }
        }
        
        return $product;
    }
    
    public static function fuzzySearch($query, $limit = 10)
    {
        return self::where(function($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('brand', 'like', "%{$query}%")
              ->orWhere('active_ingredient', 'like', "%{$query}%")
              ->orWhere('dci', 'like', "%{$query}%")
              ->orWhere('searchable_text', 'like', "%{$query}%")
              ->orWhere('barcode', 'like', "%{$query}%");
        })
        ->with(['category', 'primaryBarcode', 'batches' => function($q) {
            $q->where('expiry_date', '>', now())->orderBy('expiry_date');
        }])
        ->where('quantity', '>', 0)
        ->limit($limit)
        ->get();
    }
    
    public function updateSearchableText()
    {
        $searchableText = collect([
            $this->name,
            $this->brand,
            $this->active_ingredient,
            $this->dci,
            $this->composition,
            $this->therapeutic_class,
            $this->category ? $this->category->name : null
        ])->filter()->implode(' ');
        
        $this->update(['searchable_text' => strtolower($searchableText)]);
    }
    
    public function addBarcode($barcode, $type = 'CUSTOM', $isPrimary = false)
    {
        // Check if barcode already exists
        if (ProductBarcode::where('barcode', $barcode)->exists()) {
            throw new \Exception('Barcode already exists');
        }
        
        $productBarcode = $this->barcodes()->create([
            'barcode' => $barcode,
            'type' => $type,
            'is_primary' => $isPrimary
        ]);
        
        if ($isPrimary) {
            $productBarcode->setPrimary();
        }
        
        return $productBarcode;
    }
    
    public function getAvailableStock()
    {
        // Get stock from batches that haven't expired
        $batchStock = $this->batches()
            ->where('expiry_date', '>', now())
            ->sum('quantity_remaining');
            
        return min($this->quantity, $batchStock ?: $this->quantity);
    }
    
    public function canSell($requestedQuantity)
    {
        return $this->getAvailableStock() >= $requestedQuantity;
    }
    
    // Get purchase suggestions
    public static function getPurchaseSuggestions()
    {
        $products = self::getProductsNeedingReorder();
        $suggestions = [];
        
        foreach ($products as $product) {
            $suggestions[] = [
                'product' => $product,
                'quantity_to_order' => $product->getReorderQuantity(),
                'estimated_cost' => $product->price * $product->getReorderQuantity()
            ];
        }
        
        return $suggestions;
    }
}

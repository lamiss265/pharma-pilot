<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    // Connect to parapharma database
    protected $connection = "mysql";

    // Fillable fields
    protected $fillable = [
        "name",
        "phone",
        "notes"
    ];

    // Relationships
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    // Get total purchases
    public function getTotalPurchases()
    {
        return $this->sales()->count();
    }

    // Get total spent
    public function getTotalSpent()
    {
        $total = 0;
        foreach ($this->sales as $sale) {
            $total += $sale->quantity * $sale->product->price ?? 0;
        }
        return $total;
    }
}

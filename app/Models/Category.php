<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    
    // Connect to parapharma database
    protected $connection = "mysql";
    
    // Fillable fields
    protected $fillable = [
        'name',
        'description'
    ];
    
    // Relationships
    public function products()
    {
        return $this->hasMany(Product::class);
    }
} 
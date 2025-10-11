<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
//      * Run the migrations.

    protected $fillable = [
        'pro_name',
        'qty',
        'upis',
        'sup',
        'image',
        'category_id',
        'status',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class ,'category_id');
    }

    /**
     * Get the brand that owns the product.
     */
    // public function brand()
    // {
    //     return $this->belongsTo(Brand::class);
    // }
   

   
}
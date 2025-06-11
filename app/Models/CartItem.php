<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = ['cart_id', 'product_id', 'quantity', 'price_at_addition', 'image_path'];

    /**
     * Un ítem de carrito pertenece a un carrito.
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Un ítem de carrito pertenece a un producto.
     */
    public function product()
    {
        return $this->belongsTo(Product::class); 
    }
}
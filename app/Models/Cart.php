<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id'];

    /**
     * Un carrito pertenece a un usuario.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Un carrito tiene muchos ítems.
     * Ahora llamada 'cartItems' para consistencia.
     */
    public function cartItems() // <-- ¡Este es el nombre correcto ahora!
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Calcula el total del carrito.
     */
    public function getTotalAttribute()
    {
        // Usa 'cartItems' en lugar de 'items'
        return $this->cartItems->sum(function($item) {
            return $item->quantity * $item->price_at_addition;
        });
    }

    /**
     * Calcula la cantidad total de ítems distintos en el carrito.
     */
    public function getCountAttribute()
    {
        // Usa 'cartItems' en lugar de 'items'
        return $this->cartItems->count();
    }

    /**
     * Calcula la cantidad total de unidades en el carrito (suma de cantidades).
     */
    public function getTotalQuantityAttribute()
    {
        // Usa 'cartItems' en lugar de 'items'
        return $this->cartItems->sum('quantity');
    }
}
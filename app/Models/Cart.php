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
     * CAMBIADO DE 'items' A 'cartItems' PARA COINCIDIR CON EL CONTROLADOR
     */
    public function cartItems() // <-- ¡CAMBIADO AQUÍ!
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Calcula el total del carrito.
     */
    public function getTotalAttribute()
    {
        // Asegúrate de usar la relación correcta si usas 'items()' en otros lugares
        return $this->cartItems->sum(function($item) { // <-- Posiblemente también aquí
            return $item->quantity * $item->price_at_addition;
        });
    }

    /**
     * Calcula la cantidad total de ítems distintos en el carrito.
     */
    public function getCountAttribute()
    {
        // Asegúrate de usar la relación correcta si usas 'items()' en otros lugares
        return $this->cartItems->count(); // <-- Posiblemente también aquí
    }

    /**
     * Calcula la cantidad total de unidades en el carrito (suma de cantidades).
     */
    public function getTotalQuantityAttribute()
    {
        // Asegúrate de usar la relación correcta si usas 'items()' en otros lugares
        return $this->cartItems->sum('quantity'); // <-- Posiblemente también aquí
    }
}
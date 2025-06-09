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
     */
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Calcula el total del carrito.
     */
    public function getTotalAttribute()
    {
        return $this->items->sum(function($item) {
            return $item->quantity * $item->price_at_addition;
        });
    }

    /**
     * Calcula la cantidad total de ítems distintos en el carrito.
     */
    public function getCountAttribute()
    {
        return $this->items->count();
    }

    /**
     * Calcula la cantidad total de unidades en el carrito (suma de cantidades).
     */
    public function getTotalQuantityAttribute()
    {
        return $this->items->sum('quantity');
    }
}
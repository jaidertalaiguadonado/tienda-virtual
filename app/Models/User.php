<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Cart;
use App\Models\Location; // <--- ¡Importante! Asegúrate de que esta línea esté si tienes el modelo Location

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Correcto: Asegúrate de que 'role' esté aquí porque es la columna en tu DB.
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        // Correcto: NO necesitas 'is_admin' => 'boolean' si no tienes esa columna.
        // Ni necesitas un cast para 'role' si es un string (que es lo más común).
    ];

    // Correcto: Este método ahora usa la columna 'role' para determinar si es admin.
    // Es usado por tu AdminMiddleware.
    public function isAdmin(): bool
    {
        return $this->role === 'admin'; // Usa la columna 'role' para la comprobación.
    }

    // Correcto: Tu relación con el carrito.
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    // ¡¡¡IMPORTANTE!!!: Asegúrate de que la relación con el modelo Location esté aquí.
    // La vista de pedidos la necesita para mostrar la ubicación del usuario.
    public function location()
    {
        return $this->hasOne(Location::class);
    }
}
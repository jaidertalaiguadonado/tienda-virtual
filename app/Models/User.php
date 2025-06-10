<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Cart;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // ¡Asegúrate de que 'role' esté aquí!
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        // ¡Elimina esta línea si no tienes columna 'is_admin' o no la usas!
        // 'is_admin' => 'boolean',
        // No necesitas un cast para 'role' si es un string.
    ];

    // Este método ya no es usado por tu AdminMiddleware, pero puedes mantenerlo
    // si lo usas en otras partes de tu aplicación.
    public function isAdmin(): bool
    {
        return $this->role === 'admin'; // Usa la columna 'role'
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }
}
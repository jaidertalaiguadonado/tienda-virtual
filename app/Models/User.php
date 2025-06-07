<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean', // <--- ¡Esto es crucial!
    ];

    /**
     * Determina si el usuario es un administrador.
     */
    public function isAdmin(): bool
    {
        // Asegúrate de que $this->is_admin sea siempre un booleano.
        // Si por alguna razón es null, el cast a 'boolean' en $casts debería manejarlo.
        // Pero si la columna no existe o el valor no es booleano/entero (0/1),
        // podría haber problemas.
        return (bool) $this->is_admin; // Explícitamente castear a booleano si no estás seguro
                                     // aunque el $casts en el modelo ya debería hacerlo.
    }
}
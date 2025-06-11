<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Cart;
use App\Models\Location; 

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        
        
    ];

    
    
    public function isAdmin(): bool
    {
        return $this->role === 'admin'; 
    }

    
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    
    
    public function location()
    {
        return $this->hasOne(Location::class);
    }
}
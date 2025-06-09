<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Http\Controllers\CartController; // Importa tu CartController

class SyncCartOnLogin
{
    public function __construct()
    {
        //
    }

    public function handle(Login $event): void
    {
        // Llama a la función estática para sincronizar el carrito
        CartController::syncCart();
    }
}
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // Necesario para Auth::user()

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica si está autenticado y si el usuario es administrador usando el método isAdmin()
        // Asumiendo que el modelo User tiene un método isAdmin() que verifica el campo 'is_admin'.
        if (Auth::check() && Auth::user()->isAdmin()) { // <--- ¡CAMBIO AQUÍ!
            return $next($request);
        }

        // Redirigir si no es admin o no está logueado
        return redirect('/')->with('error', 'Acceso denegado. Solo administradores.');
    }
}
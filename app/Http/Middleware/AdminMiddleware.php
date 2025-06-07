<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role === 'admin') {
            return $next($request);
        }

        // Redirigir a la página de inicio o mostrar un error 403
        return redirect('/')->with('error', 'Acceso denegado. Solo administradores.');
        // O abort(403, 'Unauthorized action.');
    }
}
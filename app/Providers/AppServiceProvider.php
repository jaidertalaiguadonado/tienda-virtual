<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // ✅ Esto ya lo tienes, ¡perfecto!

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    // Puedes comentar o eliminar esta línea, ya que has definido un método redirectTo()
    // protected $home = '/'; 

    public function boot(): void
    {
        // Define aquí el límite de tasa para tus rutas API
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Este método registra las rutas de tu aplicación
        $this->routes(function () {
            // Rutas de la API (generalmente JSON, sin sesión)
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Rutas web (con sesión, cookies, etc.)
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Get the path the user should be redirected to after authentication.
     *
     * Este método se encarga de la lógica de redirección después de que un usuario
     * ha iniciado sesión con éxito.
     */
    protected function redirectTo(): string
    {
        // Verifica si el usuario está autenticado y si tiene el método isAdmin().
        // Si es un administrador, lo redirige al dashboard.
        if (Auth::check() && Auth::user()->isAdmin()) {
            return '/dashboard'; // Ruta del dashboard para administradores
        }

        // Si no es un administrador (o no está autenticado, aunque esta función se llama
        // *después* de la autenticación), lo redirige a la página principal.
        return '/'; // Ruta por defecto para usuarios no administradores o visitantes
    }
}
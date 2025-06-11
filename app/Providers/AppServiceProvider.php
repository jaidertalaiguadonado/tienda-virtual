<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; 

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    
    

    public function boot(): void
    {
        
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        
        $this->routes(function () {
            
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            
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
        
        
        if (Auth::check() && Auth::user()->isAdmin()) {
            return '/dashboard'; 
        }

        
        
        return '/'; 
    }
}
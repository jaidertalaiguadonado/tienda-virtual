<?php

// routes/web.php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;
use App\Models\Product;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\OrderController; // Asegúrate de que esta línea esté si tienes OrderController


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// =========================================================
// RUTAS PÚBLICAS (ACCESIBLES POR TODOS)
// =========================================================

Route::get('/', function () {
    $products = Product::with('category')->latest()->paginate(12);
    return view('welcome', compact('products'));
})->name('welcome');

Route::post('/mercadopago/webhook', [MercadoPagoController::class, 'handleWebhook'])->name('mercadopago.webhook');


// =========================================================
// RUTAS PARA USUARIOS AUTENTICADOS (PROTEGIDAS POR MIDDLEWARE 'auth')
// =========================================================

Route::middleware('auth')->group(function () {

    // NUEVA: La ruta '/home' para usuarios logueados NO admin.
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // --- Rutas del Carrito de Compras ---
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');


    // --- Rutas de Mercado Pago ---
    Route::post('/process-payment', [MercadoPagoController::class, 'createPaymentPreference'])->name('mercadopago.pay');
    Route::get('/payment/success', [MercadoPagoController::class, 'paymentSuccess'])->name('mercadopago.success');
    Route::get('/payment/failure', [MercadoPagoController::class, 'paymentFailure'])->name('mercadopago.failure');
    Route::get('/payment/pending', [MercadoPagoController::class, 'paymentPending'])->name('mercadopago.pending');


    // --- Rutas de Perfil de Usuario ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Ruta para guardar la ubicación del usuario
    Route::post('/user/save-location', [UserController::class, 'saveLocation'])->name('user.save_location');

    // Aquí es donde residía el dashboard original para los usuarios,
    // si no era el admin el que iniciaba sesión
    // Esto es para que funcione si el usuario normal iniciaba sesión y veía el dashboard normal.
    // Si no había una redirección para usuarios normales y siempre iban al home, puedes ajustar esto.
    Route::get('/user-dashboard', function () { // Puedes ponerle otro nombre si no era 'dashboard'
        return view('dashboard'); // Si tu vista de dashboard para usuarios normales es 'dashboard.blade.php'
    })->name('user.dashboard'); // Nombre de ruta para usuarios normales

}); // Fin del grupo de rutas protegidas por 'auth'


// =========================================================
// RUTAS DE ADMINISTRACIÓN (PROTEGIDAS POR 'auth' y 'admin')
// =========================================================

// ESTE ES EL CAMBIO CLAVE: Mover la ruta 'dashboard' con su nombre 'dashboard' a este grupo.
// Esto significa que si un usuario autenticado intenta acceder a la ruta nombrada 'dashboard' (la que Breeze usa por defecto),
// y está protegido por 'admin' middleware, solo los admins podrán acceder y verán su dashboard.
Route::middleware(['auth', 'admin'])->group(function () {
    // La ruta '/dashboard' ahora está dentro del middleware 'admin'.
    // Si un administrador inicia sesión, Breeze lo envía a 'dashboard',
    // y aquí es donde será interceptado por el middleware 'admin' y luego mostrará el dashboard de admin.
    Route::get('/dashboard', function () {
        // Asumiendo que tu dashboard de administrador es la vista 'dashboard.blade.php'
        // que tienes directamente en 'resources/views/'
        return view('dashboard');
    })->name('dashboard'); // Mantiene el nombre de ruta 'dashboard' para que Breeze lo use.


    // El resto de tus rutas de administración
    Route::prefix('admin')->name('admin.')->group(function () {
        // Aquí no necesitas un /dashboard si el de arriba ya lo maneja
        // Route::get('/dashboard', function () { return view('admin.dashboard'); })->name('dashboard');

        Route::resource('categories', CategoryController::class);
        Route::resource('products', ProductController::class);
        Route::resource('orders', OrderController::class)->only(['index', 'show', 'update', 'destroy']);
    });
});


// =========================================================
// RUTAS DE AUTENTICACIÓN (de Laravel Breeze)
// =========================================================
require __DIR__.'/auth.php';
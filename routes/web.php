<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\CartController; // Asegúrate de que este use esté presente
use Illuminate\Support\Facades\Route;
use App\Models\Product;
use App\Http\Controllers\MercadoPagoController; // Asegúrate de que este use esté presente
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth; // Asegúrate de que este use esté presente
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\OrderController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Ruta de bienvenida - Accesible para todos
Route::get('/', function () {
    $products = Product::with('category')->latest()->paginate(12);
    return view('welcome', compact('products'));
})->name('welcome');

// Ruta del webhook de Mercado Pago - DEBE ser pública y POST
Route::post('/mercadopago/webhook', [MercadoPagoController::class, 'handleWebhook'])->name('mercadopago.webhook');


// =========================================================================
// RUTAS DEL CARRITO - ACCESIBLES PARA INVITADOS Y USUARIOS AUTENTICADOS
// =========================================================================
// Corrección de nombres de métodos para que coincidan con CartController.php
Route::get('/cart', [CartController::class, 'showCart'])->name('cart.show');
Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'updateCart'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'removeFromCart'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clearCart'])->name('cart.clear'); // Añadida si tienes este método
Route::get('/api/cart-count', [CartController::class, 'getCartItemCount'])->name('api.cart.count');


// =========================================================================
// RUTAS QUE REQUIEREN AUTENTICACIÓN (PARA CUALQUIER USUARIO LOGUEADO)
// =========================================================================
Route::middleware('auth')->group(function () {

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Rutas de Mercado Pago para procesar pagos (requieren usuario logueado)
    // createPaymentPreference es POST
    Route::post('/process-payment', [MercadoPagoController::class, 'createPaymentPreference'])->name('mercadopago.pay');
    // Las rutas de éxito/fallo/pendiente son GET (a donde MP redirige al usuario)
    Route::get('/payment/success', [MercadoPagoController::class, 'paymentSuccess'])->name('mercadopago.success');
    Route::get('/payment/failure', [MercadoPagoController::class, 'paymentFailure'])->name('mercadopago.failure');
    Route::get('/payment/pending', [MercadoPagoController::class, 'paymentPending'])->name('mercadopago.pending');

    // Rutas de perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Ruta para guardar la ubicación del usuario
    Route::post('/user/save-location', [UserController::class, 'saveLocation'])->name('user.save_location');

    // Dashboard de usuario (si aplica, diferente al de admin)
    Route::get('/user-dashboard', function () {
        return view('dashboard');
    })->name('user.dashboard');

    // Eliminada la ruta de depuración específica, ya que la depuración se maneja con logs o dd() temporales en el controlador.
    // Si necesitas depurar el payload, puedes reactivar el dd() dentro de createPaymentPreference temporalmente.

});


// =========================================================================
// RUTAS QUE REQUIEREN AUTENTICACIÓN Y ROL DE ADMINISTRADOR
// =========================================================================
Route::middleware(['auth', 'admin'])->group(function () {

    // Dashboard de administrador
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Prefijo y nombre para las rutas de administración
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('categories', CategoryController::class);
        Route::resource('products', ProductController::class);
        Route::resource('orders', OrderController::class)->only(['index', 'show', 'update', 'destroy']);
    });
});


// =========================================================================
// RUTAS DE AUTENTICACIÓN DE LARAVEL BREEZE (o similar)
// =========================================================================
require __DIR__.'/auth.php';
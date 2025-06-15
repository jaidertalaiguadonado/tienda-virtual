<?php

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

// Ruta del webhook de Mercado Pago - Debe ser pública
Route::post('/mercadopago/webhook', [MercadoPagoController::class, 'handleWebhook'])->name('mercadopago.webhook');


// =========================================================================
// RUTAS DEL CARRITO - ACCESIBLES PARA INVITADOS Y USUARIOS AUTENTICADOS
// =========================================================================
Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/api/cart-count', [CartController::class, 'getCartItemCount'])->name('api.cart.count');


// =========================================================================
// RUTAS QUE REQUIEREN AUTENTICACIÓN (PARA CUALQUIER USUARIO LOGUEADO)
// =========================================================================
Route::middleware('auth')->group(function () {

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Rutas de Mercado Pago para procesar pagos (requieren usuario logueado)
    Route::post('/process-payment', [MercadoPagoController::class, 'createPaymentPreference'])->name('mercadopago.pay');
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

    // =====================================================================
    // NUEVA RUTA DE DEPURACIÓN PARA MERCADO PAGO
    // Accede a esta ruta para probar la creación de preferencia con un producto fijo.
    // =====================================================================
    Route::get('/debug-mercadopago-preference', [MercadoPagoController::class, 'createPaymentPreference'])->name('mercadopago.debug')->defaults('debugMode', true);
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

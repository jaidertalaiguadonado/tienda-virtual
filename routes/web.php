<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;
use App\Models\Product;
use App\Http\Controllers\MercadoPagoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Mercado Pago Routes
// ---
// Ruta para iniciar el proceso de pago con Mercado Pago
Route::post('/process-payment', [MercadoPagoController::class, 'createPaymentPreference'])->name('mercadopago.pay');

// Rutas de retorno de Mercado Pago (el cliente regresa a estas URLs)
Route::get('/payment/success', [MercadoPagoController::class, 'paymentSuccess'])->name('mercadopago.success');
Route::get('/payment/failure', [MercadoPagoController::class, 'paymentFailure'])->name('mercadopago.failure');
Route::get('/payment/pending', [MercadoPagoController::class, 'paymentPending'])->name('mercadopago.pending');

// ¡LA RUTA DEL WEBHOOK DE MERCADO PAGO! (CRÍTICA)
// Mercado Pago enviará notificaciones POST a esta URL.
Route::post('/mercadopago/webhook', [MercadoPagoController::class, 'handleWebhook'])->name('mercadopago.webhook');

// Welcome Page Route
// ---
// Ruta principal para la página de bienvenida con productos
Route::get('/', function () {
    // Fetches 12 products per page, eager loading their categories.
    $products = Product::with('category')->latest()->paginate(12);
    return view('welcome', compact('products'));
})->name('welcome');

// Shopping Cart Routes
// ---
// Ruta para añadir un producto al carrito (usada por AJAX en el frontend)
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');

// Ruta para mostrar la página del carrito
Route::get('/cart', [CartController::class, 'show'])->name('cart.show');

// Ruta para actualizar la cantidad de un producto en el carrito (usada por AJAX)
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');

// Ruta para eliminar un producto del carrito (usada por AJAX)
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');

// Optional: Route to clear the entire cart
// Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// Dashboard Route
// ---
// The 'admin' middleware should be applied here if only admins should access the dashboard directly.
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'admin'])->name('dashboard');

// User Profile Routes
// ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Panel Routes
// ---
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
});

// Authentication Routes
// ---
require __DIR__.'/auth.php';
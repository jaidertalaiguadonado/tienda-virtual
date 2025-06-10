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
use App\Http\Controllers\UserController; // ¡NUEVA IMPORTACIÓN: Necesitas un controlador para manejar la lógica de guardar la ubicación!


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// =========================================================
// RUTAS PÚBLICAS (ACCESIBLES POR TODOS)
// =========================================================

// Ruta principal para la página de bienvenida con productos
// Ahora esta es SÓLO para mostrar productos, sin añadir al carrito directamente para no logueados.
Route::get('/', function () {
    $products = Product::with('category')->latest()->paginate(12);
    return view('welcome', compact('products'));
})->name('welcome');

// El webhook de Mercado Pago DEBE ser accesible públicamente, ya que MP lo llamará.
Route::post('/mercadopago/webhook', [MercadoPagoController::class, 'handleWebhook'])->name('mercadopago.webhook');


// =========================================================
// RUTAS PARA USUARIOS AUTENTICADOS (PROTEGIDAS POR MIDDLEWARE 'auth')
// =========================================================

Route::middleware('auth')->group(function () {

    // NUEVO: La ruta '/home' como el nuevo home para usuarios logueados.
    // Aquí es donde se encontrarán los productos con la funcionalidad de añadir al carrito.
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // NUEVO: Redirecciona la ruta '/dashboard' (por defecto de Breeze) a '/home'.
    // Esto es para que después de iniciar sesión, el usuario vaya a tu nueva "home de pedidos".
    Route::get('/dashboard', function () {
        return redirect()->route('home');
    })->name('dashboard'); // Mantén este nombre de ruta por compatibilidad con Breeze.


    // --- Rutas del Carrito de Compras (¡Ahora protegidas por 'auth'!) ---
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    // Si usas una ruta para vaciar el carrito:
    // Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');


    // --- Rutas de Mercado Pago (¡También protegidas por 'auth'!) ---
    // La ruta que crea la preferencia de pago
    Route::post('/process-payment', [MercadoPagoController::class, 'createPaymentPreference'])->name('mercadopago.pay');
    // Las rutas de retorno de Mercado Pago (el cliente regresa a estas URLs)
    Route::get('/payment/success', [MercadoPagoController::class, 'paymentSuccess'])->name('mercadopago.success');
    Route::get('/payment/failure', [MercadoPagoController::class, 'paymentFailure'])->name('mercadopago.failure');
    Route::get('/payment/pending', [MercadoPagoController::class, 'paymentPending'])->name('mercadopago.pending');


    // --- Rutas de Perfil de Usuario ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ¡¡¡AQUÍ ES DONDE DEBES AGREGAR LA RUTA PARA GUARDAR LA UBICACIÓN!!!
    // Asegúrate de que tu HomeController o un nuevo UserController tenga un método para manejar esto.
    // He importado 'App\Http\Controllers\UserController;' arriba.
    Route::post('/user/save-location', [UserController::class, 'saveLocation'])->name('user.save_location');

}); // Fin del grupo de rutas protegidas por 'auth'


// =========================================================
// RUTAS DE ADMINISTRACIÓN (PROTEGIDAS POR 'auth' y 'admin')
// =========================================================

// Asumiendo que tu middleware 'admin' está correctamente definido
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    // ... agrega aquí otras rutas específicas del panel de administración
});


// =========================================================
// RUTAS DE AUTENTICACIÓN (de Laravel Breeze)
// =========================================================
require __DIR__.'/auth.php';
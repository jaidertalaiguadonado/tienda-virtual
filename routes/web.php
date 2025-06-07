<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;
use App\Models\Product; // Asegúrate de que esta línea esté presente
use Illuminate\Support\Facades\Auth;

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

// Ruta principal para la página de bienvenida con productos
Route::get('/', function () {
    // CAMBIO AQUÍ: Ahora obtenemos más productos, por ejemplo, 12 por página.
    // Si quieres mostrar TODOS los productos (cuidado con el rendimiento si son muchos),
    // usa ->get() en lugar de ->paginate(12) y omite los enlaces de paginación en la vista.
    $products = Product::with('category')->latest()->paginate(12); // <-- CORRECCIÓN

    return view('welcome', compact('products'));
})->name('welcome');

// Resto de tus rutas (sin cambios si ya funcionan bien)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'admin'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
});

require __DIR__.'/auth.php';
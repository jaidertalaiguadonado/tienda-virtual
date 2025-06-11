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





Route::get('/', function () {
    $products = Product::with('category')->latest()->paginate(12);
    return view('welcome', compact('products'));
})->name('welcome');

Route::post('/mercadopago/webhook', [MercadoPagoController::class, 'handleWebhook'])->name('mercadopago.webhook');






Route::middleware('auth')->group(function () {

    
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');


    
    Route::post('/process-payment', [MercadoPagoController::class, 'createPaymentPreference'])->name('mercadopago.pay');
    Route::get('/payment/success', [MercadoPagoController::class, 'paymentSuccess'])->name('mercadopago.success');
    Route::get('/payment/failure', [MercadoPagoController::class, 'paymentFailure'])->name('mercadopago.failure');
    Route::get('/payment/pending', [MercadoPagoController::class, 'paymentPending'])->name('mercadopago.pending');


    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    
    Route::post('/user/save-location', [UserController::class, 'saveLocation'])->name('user.save_location');

    
    
    
    
    Route::get('/user-dashboard', function () { 
        return view('dashboard'); 
    })->name('user.dashboard'); 

}); 









Route::middleware(['auth', 'admin'])->group(function () {
    
    
    
    Route::get('/dashboard', function () {
        
        
        return view('dashboard');
    })->name('dashboard'); 


    
    Route::prefix('admin')->name('admin.')->group(function () {
        
        

        Route::resource('categories', CategoryController::class);
        Route::resource('products', ProductController::class);
        Route::resource('orders', OrderController::class)->only(['index', 'show', 'update', 'destroy']);
    });
});





require __DIR__.'/auth.php';
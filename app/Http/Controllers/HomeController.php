<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product; // Asegúrate de importar tu modelo Product

class HomeController extends Controller
{
    /**
     * Muestra la página principal para usuarios logueados con productos.
     * Aquí es donde tendrán la funcionalidad completa de compra.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Carga los productos paginados
        $products = Product::with('category')->latest()->paginate(12);

        // Opcional: Cargar pedidos del usuario para mostrarlos en el home, si aplica
        // $userOrders = auth()->user()->orders()->latest()->get();

        return view('home', compact('products')); // Usaremos una nueva vista 'home.blade.php'
    }
}
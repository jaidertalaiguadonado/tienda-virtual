<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage; // ¡Importante: Importar el facade Storage!

class ProductController extends Controller
{
    /**
     * Muestra una lista de todos los productos.
     */
    public function index()
    {
        // Carga los productos y sus categorías asociadas para mostrarlos
        $products = Product::with('category')->get();
        return view('admin.products.index', compact('products'));
    }

    /**
     * Muestra el formulario para crear un nuevo producto.
     */
    public function create()
    {
        // Obtiene todas las categorías para el desplegable del formulario
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Almacena un nuevo producto en la base de datos.
     */
    public function store(Request $request)
    {
        // Validación de los datos del formulario
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|max:255',
            'description' => 'nullable',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validación de imagen
        ]);

        $imagePath = null; // Inicializa la ruta de la imagen como nula

        // Si se ha subido una imagen, la almacena
        if ($request->hasFile('image')) {
            // Guarda la imagen en la carpeta 'products' dentro del disco 'public'
            // Esto significa que se guardará en 'storage/app/public/products'
            // y $imagePath contendrá la ruta relativa, ej: 'products/nombre_aleatorio.jpg'
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // Crea el nuevo producto con los datos del request y la ruta de la imagen
        Product::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name), // Genera un slug para la URL amigable
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image_path' => $imagePath, // Guarda la ruta relativa de la imagen
            'is_active' => $request->has('is_active'), // Comprueba si el checkbox está marcado
        ]);

        // Redirige al índice de productos con un mensaje de éxito
        return redirect()->route('admin.products.index')->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Muestra los detalles de un producto específico.
     */
    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    /**
     * Muestra el formulario para editar un producto existente.
     */
    public function edit(Product $product)
    {
        // Obtiene todas las categorías para el desplegable del formulario de edición
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Actualiza un producto existente en la base de datos.
     */
    public function update(Request $request, Product $product)
    {
        // Validación de los datos del formulario de actualización
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|max:255',
            'description' => 'nullable',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validación de imagen
        ]);

        // Mantiene la ruta de la imagen existente por defecto
        $imagePath = $product->image_path;

        // Si se sube una nueva imagen
        if ($request->hasFile('image')) {
            // Elimina la imagen antigua del almacenamiento si existe
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            // Almacena la nueva imagen
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // Actualiza el producto con los nuevos datos y la ruta de la imagen
        $product->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image_path' => $imagePath, // Actualiza la ruta de la imagen
            'is_active' => $request->has('is_active'),
        ]);

        // Redirige al índice de productos con un mensaje de éxito
        return redirect()->route('admin.products.index')->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Elimina un producto de la base de datos.
     */
    public function destroy(Product $product)
    {
        // Elimina la imagen asociada del almacenamiento si existe
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        // Elimina el producto de la base de datos
        $product->delete();
        // Redirige al índice de productos con un mensaje de éxito
        return redirect()->route('admin.products.index')->with('success', 'Producto eliminado exitosamente.');
    }
}
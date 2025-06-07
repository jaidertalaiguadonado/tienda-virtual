<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage; // Aún lo mantenemos si hay otros usos de Storage en el futuro, pero no para la imagen de producto en este caso específico.

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
            // VALIDACIÓN PARA URL DE IMAGEN:
            // 'nullable' permite que sea opcional
            // 'url' asegura que el valor sea una URL válida si se proporciona
            // 'max:2048' (o un valor más alto) para la longitud de la URL si es necesario
            'image_path' => 'nullable|url|max:2048', 
        ]);

        // La ruta de la imagen ahora viene directamente del input image_path
        $imagePath = $request->input('image_path');

        // Crea el nuevo producto con los datos del request y la ruta de la imagen
        Product::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name), // Genera un slug para la URL amigable
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image_path' => $imagePath, // Guarda la URL de la imagen
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
            // VALIDACIÓN PARA URL DE IMAGEN:
            // 'nullable' permite que sea opcional
            // 'url' asegura que el valor sea una URL válida si se proporciona
            // 'max:2048' (o un valor más alto) para la longitud de la URL si es necesario
            'image_path' => 'nullable|url|max:2048',
        ]);

        // La ruta de la imagen ahora viene directamente del input image_path
        // No hay necesidad de verificar si se subió un archivo o eliminar el antiguo
        $imagePath = $request->input('image_path');

        // Actualiza el producto con los nuevos datos y la ruta de la imagen
        $product->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image_path' => $imagePath, // Actualiza con la URL de la imagen
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
        // NO es necesario eliminar la imagen del almacenamiento local
        // porque ahora se asume que son URLs externas.
        // Si en tu aplicación aún manejas imágenes subidas localmente
        // en algún otro lugar, deberías conservar o adaptar esta lógica.
        /*
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }
        */

        // Elimina el producto de la base de datos
        $product->delete();
        // Redirige al índice de productos con un mensaje de éxito
        return redirect()->route('admin.products.index')->with('success', 'Producto eliminado exitosamente.');
    }
}
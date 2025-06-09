<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart; // Importa el modelo Cart
use App\Models\CartItem; // Importa el modelo CartItem
use App\Models\Product; // Importa el modelo Product

class CartController extends Controller
{
    /**
     * Muestra la página del carrito de compras.
     */
    public function show()
    {
        if (Auth::check()) {
            // Usuario autenticado: Cargar carrito desde la base de datos
            $cart = Auth::user()->cart;
            // Cargar los ítems del carrito con sus productos relacionados
            // Aseguramos que el producto se carga para acceder a su nombre e imagen
            $cartItems = $cart ? $cart->items()->with('product')->get() : collect();

            // Transformar cartItems para que tengan una estructura consistente para la vista,
            // incluyendo 'name' y 'image_path' para los productos relacionados
            $formattedCartItems = $cartItems->map(function($item) {
                return [
                    'product_id' => $item->product_id,
                    'name' => $item->product->name, // Nombre del producto relacionado
                    'price' => $item->product->price, // Precio actual del producto (opcional, si no usas price_at_addition)
                    'price_at_addition' => $item->price_at_addition, // Precio en el momento de la adición
                    'quantity' => $item->quantity,
                    'image_path' => $item->image_path ?? $item->product->image_path, // Imagen del item o del producto
                    // Puedes añadir 'subtotal' aquí si lo calculas en el modelo CartItem o Cart
                    'subtotal' => $item->price_at_addition * $item->quantity, // Asegúrate de que el precio sea el correcto
                ];
            });

            $total = $cart ? $cart->total : 0;
            $cartCount = $cart ? $cart->total_quantity : 0; // Total de unidades
        } else {
            // Usuario invitado: Cargar carrito desde la sesión
            $sessionCart = Session::get('cart', []);
            $formattedCartItems = collect($sessionCart)->map(function($item) {
                // Asegúrate de que los ítems de sesión tengan los mismos campos clave
                return [
                    'product_id' => $item['id'], // Asume 'id' es el product_id
                    'name' => $item['name'],
                    'price' => $item['price'], // Precio del producto en sesión
                    'price_at_addition' => $item['price'], // Usar price como price_at_addition para consistencia
                    'quantity' => $item['quantity'],
                    'image_path' => $item['image'], // Asume 'image' es la ruta de la imagen
                    'subtotal' => $item['price'] * $item['quantity'],
                ];
            });


            $total = $formattedCartItems->sum('subtotal');
            $cartCount = $formattedCartItems->sum('quantity');
        }

        return view('cart.show', [
            'cartItems' => $formattedCartItems, // Pasar los ítems formateados
            'total' => $total,
            'cartCount' => $cartCount
        ]);
    }

    /**
     * Añade un producto al carrito.
     * Si el usuario está autenticado, lo guarda en DB. Si es invitado, en sesión.
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $productId = $request->product_id;
        $quantity = $request->quantity;

        $product = Product::find($productId);
        if (!$product || !($product->is_in_stock ?? true) || $product->stock < $quantity) {
            return response()->json(['message' => 'Producto no disponible o stock insuficiente.'], 400);
        }

        if (Auth::check()) {
            // --- Usuario Autenticado: Guardar en la Base de Datos ---
            $user = Auth::user();
            $cart = $user->cart;

            if (!$cart) {
                $cart = Cart::create(['user_id' => $user->id]);
            }

            $cartItem = $cart->items()->where('product_id', $productId)->first();

            if ($cartItem) {
                $cartItem->quantity += $quantity;
                $cartItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price_at_addition' => $product->price,
                    'image_path' => $product->image_path,
                ]);
            }

            // Refrescar el carrito para recalcular totales y cantidades
            $cart->refresh();
            $cartCount = $cart->total_quantity;
            $total = $cart->total;

        } else {
            // --- Usuario Invitado: Guardar en la Sesión ---
            $cart = Session::get('cart', []);

            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] += $quantity;
            } else {
                $cart[$productId] = [
                    'id' => $productId,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image' => $product->image_path, // Asegúrate de que esto sea la ruta de la imagen
                    'quantity' => $quantity,
                ];
            }
            Session::put('cart', $cart);

            $cartCount = array_sum(array_column($cart, 'quantity'));
            $total = array_sum(array_map(function($item) {
                return $item['price'] * $item['quantity'];
            }, $cart));
        }

        return response()->json([
            'message' => 'Producto añadido al carrito correctamente.',
            'cartCount' => $cartCount,
            'total' => $total, // IMPORTANT: No format here! Send as number
        ]);
    }

    /**
     * Actualiza la cantidad de un producto en el carrito.
     */
    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:0', // 0 para eliminar si la cantidad llega a cero
        ]);

        $productId = $request->product_id;
        $newQuantity = $request->quantity;
        $itemData = null; // Inicializar a null

        if (Auth::check()) {
            // --- Usuario Autenticado: Actualizar en la Base de Datos ---
            $user = Auth::user();
            $cart = $user->cart;
            if (!$cart) {
                return response()->json(['message' => 'Carrito no encontrado.'], 404);
            }

            $cartItem = $cart->items()->where('product_id', $productId)->first();

            if ($cartItem) {
                if ($newQuantity > 0) {
                    $cartItem->quantity = $newQuantity;
                    $cartItem->save();
                    // Refrescar el item para obtener el subtotal actualizado si está calculado en el modelo
                    $cartItem->refresh();
                    $itemData = [
                        'product_id' => $cartItem->product_id, // Usar product_id para consistencia
                        'name' => $cartItem->product->name,
                        'price' => $cartItem->price_at_addition,
                        'image_path' => $cartItem->image_path,
                        'quantity' => $cartItem->quantity,
                        'subtotal' => $cartItem->price_at_addition * $cartItem->quantity,
                    ];
                } else {
                    $cartItem->delete(); // Eliminar si la cantidad es 0
                    $itemData = null; // El ítem se eliminó
                }
            } else {
                return response()->json(['message' => 'Producto no encontrado en el carrito.'], 404);
            }

            $cart->refresh(); // Recargar el carrito para obtener el total y total_quantity actualizado
            $cartCount = $cart->total_quantity;
            $total = $cart->total;

        } else {
            // --- Usuario Invitado: Actualizar en la Sesión ---
            $cart = Session::get('cart', []);

            if (isset($cart[$productId])) {
                if ($newQuantity > 0) {
                    $cart[$productId]['quantity'] = $newQuantity;
                    $itemData = [
                        'product_id' => $productId,
                        'name' => $cart[$productId]['name'],
                        'price' => $cart[$productId]['price'],
                        'image_path' => $cart[$productId]['image'], // Consistencia con 'image_path'
                        'quantity' => $cart[$productId]['quantity'],
                        'subtotal' => $cart[$productId]['price'] * $cart[$productId]['quantity'],
                    ];
                } else {
                    unset($cart[$productId]);
                    $itemData = null; // El ítem se eliminó
                }
                Session::put('cart', $cart);
            } else {
                return response()->json(['message' => 'Producto no encontrado en el carrito de sesión.'], 404);
            }

            $cartCount = array_sum(array_column($cart, 'quantity'));
            $total = array_sum(array_map(function($item) {
                return $item['price'] * $item['quantity'];
            }, $cart));
        }

        return response()->json([
            'message' => 'Carrito actualizado.',
            'cartCount' => $cartCount,
            'total' => $total, // IMPORTANT: No format here! Send as number
            'item' => $itemData // Devuelve la data del ítem actualizado (o null si se eliminó)
        ]);
    }

    /**
     * Elimina un producto del carrito.
     */
    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
        ]);

        $productId = $request->product_id;

        if (Auth::check()) {
            // --- Usuario Autenticado: Eliminar de la Base de Datos ---
            $user = Auth::user();
            $cart = $user->cart;
            if (!$cart) {
                return response()->json(['message' => 'Carrito no encontrado.'], 404);
            }

            $cartItem = $cart->items()->where('product_id', $productId)->first();

            if ($cartItem) {
                $cartItem->delete();
            } else {
                return response()->json(['message' => 'Producto no encontrado en el carrito.'], 404);
            }

            $cart->refresh(); // Recargar el carrito para obtener el total y total_quantity actualizado
            $cartCount = $cart->total_quantity;
            $total = $cart->total;

        } else {
            // --- Usuario Invitado: Eliminar de la Sesión ---
            $cart = Session::get('cart', []);

            if (isset($cart[$productId])) {
                unset($cart[$productId]);
                Session::put('cart', $cart);
            } else {
                return response()->json(['message' => 'Producto no encontrado en el carrito de sesión.'], 404);
            }

            $cartCount = array_sum(array_column($cart, 'quantity'));
            $total = array_sum(array_map(function($item) {
                return $item['price'] * $item['quantity'];
            }, $cart));
        }

        return response()->json([
            'message' => 'Producto eliminado del carrito.',
            'cartCount' => $cartCount,
            'total' => $total, // IMPORTANT: No format here! Send as number
        ]);
    }

    /**
     * Sincroniza el carrito de sesión con el carrito de la base de datos al iniciar sesión.
     * Este método se llamaría después de que un usuario inicie sesión.
     * Podrías llamarlo desde el AuthController@login o un middleware.
     */
    public static function syncCart()
    {
        if (Auth::check() && Session::has('cart')) {
            $user = Auth::user();
            $sessionCart = Session::get('cart');

            $cart = $user->cart;

            if (!$cart) {
                $cart = Cart::create(['user_id' => $user->id]);
            }

            foreach ($sessionCart as $productId => $itemData) {
                $cartItem = $cart->items()->where('product_id', $productId)->first();

                if ($cartItem) {
                    $cartItem->quantity += $itemData['quantity'];
                    $cartItem->save();
                } else {
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $productId,
                        'quantity' => $itemData['quantity'],
                        'price_at_addition' => $itemData['price'],
                        'image_path' => $itemData['image'],
                    ]);
                }
            }

            Session::forget('cart'); // Limpiar el carrito de sesión después de sincronizar
        }
    }
}
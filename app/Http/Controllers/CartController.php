<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;

class CartController extends Controller
{
    const IVA_RATE = 0.19; // Tasa de IVA (19%)
    const MP_COMMISSION_PERCENTAGE = 0.0329; // Comisión de Mercado Pago (3.29%)
    const MP_FIXED_FEE = 900; // Cuota fija de Mercado Pago

    /**
     * Muestra el contenido del carrito.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $cartItems = $this->getFormattedCartItems();
        $totals = $this->calculateCartTotals($cartItems);

        return view('cart.show', $totals);
    }

    /**
     * Añade un producto al carrito.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse | \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);

        $product = Product::findOrFail($productId); // Fallará si el producto no existe

        if (Auth::check()) {
            $user = Auth::user();
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);

            $cartItem = $cart->cartItems()->where('product_id', $productId)->first();

            if ($cartItem) {
                $cartItem->quantity += $quantity;
                $cartItem->save();
            } else {
                $cart->cartItems()->create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price_at_addition' => $product->price,
                ]);
            }
            // Para peticiones AJAX, devolver JSON
            if ($request->expectsJson() || $request->ajax()) {
                $cartCount = $this->getCartItemCount()->original['cartCount'];
                return response()->json(['message' => 'Producto añadido al carrito.', 'cartCount' => $cartCount]);
            }
        } else {
            $sessionCart = Session::get('cart', []);
            $found = false;
            foreach ($sessionCart as $key => &$item) {
                if ($item['id'] == $productId) {
                    $item['quantity'] += $quantity;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $sessionCart[] = [
                    'id' => $productId, // ID del producto para sesión
                    'name' => $product->name,
                    'price' => $product->price,
                    'image_url' => $product->image_url,
                    'quantity' => $quantity
                ];
            }
            Session::put('cart', $sessionCart);

            // Para peticiones AJAX, devolver JSON
            if ($request->expectsJson() || $request->ajax()) {
                $cartCount = $this->getCartItemCount()->original['cartCount'];
                return response()->json(['message' => 'Producto añadido al carrito.', 'cartCount' => $cartCount]);
            }
        }

        // Si no es una petición AJAX, redirigir
        return redirect()->back()->with('success', 'Producto añadido al carrito.');
    }

    /**
     * Actualiza la cantidad de un producto en el carrito.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        // El frontend envía 'id', que es el cart_item_id para usuarios logueados,
        // o el product_id para usuarios invitados.
        $identifier = $request->input('id'); 
        $quantity = $request->input('quantity');

        if (!is_numeric($quantity) || $quantity < 0) {
            return response()->json(['message' => 'Cantidad inválida.', 'status' => 'error'], 400);
        }

        if ($quantity == 0) {
            // Si la cantidad es 0, llamamos al método remove, que ya devuelve JSON
            return $this->remove($request); 
        }

        $updatedSuccessfully = false;

        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart;
            if ($cart) {
                // Para usuarios logueados, $identifier es el ID del CartItem
                $cartItem = $cart->cartItems()->where('id', $identifier)->first(); 
                if ($cartItem) {
                    $cartItem->quantity = $quantity;
                    $cartItem->save();
                    $updatedSuccessfully = true;
                } else {
                    return response()->json(['message' => 'Producto no encontrado en el carrito del usuario.', 'status' => 'error'], 404);
                }
            } else {
                 return response()->json(['message' => 'Carrito no encontrado para el usuario.', 'status' => 'error'], 404);
            }
        } else { // Carrito de invitado
            $sessionCart = Session::get('cart', []);
            $updated = false;
            foreach ($sessionCart as $key => &$item) {
                // Para invitados, $identifier es el product_id
                if ($item['id'] == $identifier) { 
                    $item['quantity'] = $quantity;
                    $updated = true;
                    break;
                }
            }
            Session::put('cart', $sessionCart);

            if (!$updated) {
                return response()->json(['message' => 'Producto no encontrado en el carrito de sesión.', 'status' => 'error'], 404);
            }
            $updatedSuccessfully = true;
        }

        if (!$updatedSuccessfully) {
            return response()->json(['message' => 'No se pudo actualizar el producto en el carrito.', 'status' => 'error'], 500);
        }

        // Recalcular y devolver los nuevos totales
        $formattedCartItems = $this->getFormattedCartItems();
        $cartTotals = $this->calculateCartTotals($formattedCartItems);

        // Buscar el item que se acaba de actualizar para devolverlo en la respuesta
        $updatedFormattedItem = null;
        // Para encontrar el item en la respuesta, necesitamos saber si era un cart_item_id o product_id
        if (Auth::check()) {
            $itemFound = CartItem::find($identifier);
            if ($itemFound) {
                $productIdForResponse = $itemFound->product_id;
            } else {
                $productIdForResponse = null; // Item fue eliminado o no existe
            }
        } else {
            $productIdForResponse = $identifier; // Para invitados, el identifier es el product_id
        }

        foreach ($formattedCartItems as $item) {
            if ($item['product_id'] == $productIdForResponse) {
                $updatedFormattedItem = $item;
                break;
            }
        }

        return response()->json(array_merge($cartTotals, [
            'message' => 'Cantidad actualizada.',
            'status' => 'success', // Añadir status success
            'item' => $updatedFormattedItem // Devolver el item actualizado para reflejar cambios en la UI
        ]));
    }

    /**
     * Elimina un producto del carrito.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(Request $request)
    {
        // El frontend envía 'id', que es el cart_item_id para usuarios logueados,
        // o el product_id para usuarios invitados.
        $identifier = $request->input('id'); 

        $deleted = false;
        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart;
            if ($cart) {
                // Para usuarios logueados, $identifier es el ID del CartItem
                $deleted = $cart->cartItems()->where('id', $identifier)->delete();
            }
        } else { // Carrito de invitado
            $sessionCart = Session::get('cart', []);
            $initialCount = count($sessionCart);
            $sessionCart = array_filter($sessionCart, function (array $item) use ($identifier) {
                // Para invitados, $identifier es el product_id
                return $item['id'] != $identifier;
            });
            // Reindexar el array para evitar problemas con las claves
            $sessionCart = array_values($sessionCart);
            Session::put('cart', $sessionCart);
            $deleted = (count($sessionCart) < $initialCount);
        }

        if (!$deleted) {
            return response()->json(['message' => 'Producto no encontrado en el carrito para eliminar.', 'status' => 'error'], 404);
        }

        // Recalcular y devolver los nuevos totales
        $formattedCartItems = $this->getFormattedCartItems();
        $cartTotals = $this->calculateCartTotals($formattedCartItems);

        return response()->json(array_merge($cartTotals, [
            'message' => 'Producto eliminado del carrito.',
            'status' => 'success', // Añadir status success
            'cartCount' => count($formattedCartItems)
        ]));
    }

    /**
     * Obtiene el conteo de ítems (cantidad total de unidades) en el carrito (para la API).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCartItemCount()
    {
        $count = 0;
        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart;
            // Asegúrate de que $cart->cartItems sea una colección, incluso si está vacía
            $count = $cart ? ($cart->cartItems ?? collect())->sum('quantity') : 0;
        } else {
            $sessionCart = Session::get('cart', []);
            // Asegúrate de que $sessionCart sea un array
            if (!is_array($sessionCart)) {
                $sessionCart = [];
            }
            foreach ($sessionCart as $item) {
                $count += $item['quantity'] ?? 0; // Usar null coalescing para seguridad
            }
        }
        return response()->json(['cartCount' => $count]);
    }

    /**
     * Obtiene los ítems del carrito formateados con precios netos y brutos.
     *
     * @return array
     */
    public function getFormattedCartItems(): array
    {
        $formattedItems = [];

        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart()->with('cartItems.product')->first();
            if ($cart) {
                // Asegura que cartItems sea una colección, incluso si la relación es null
                $cartItemsCollection = $cart->cartItems ?? collect();
                foreach ($cartItemsCollection as $cartItem) {
                    if ($cartItem->product) {
                        $priceNet = (float) $cartItem->product->price;
                        $subtotalNet = $cartItem->quantity * $priceNet;
                        $subtotalGross = round($subtotalNet * (1 + self::IVA_RATE), 2);

                        $formattedItems[] = [
                            'id' => $cartItem->id, // Este es el ID de CartItem (para operaciones de update/remove)
                            'product_id' => $cartItem->product_id, // ID del Producto (para referencias)
                            'name' => $cartItem->product->name,
                            'quantity' => $cartItem->quantity,
                            'price_net' => $priceNet,
                            'subtotal_item_net' => $subtotalNet,
                            'subtotal_item_gross' => $subtotalGross,
                            'image_url' => $cartItem->product->image_url ?? asset('images/default_product.png'), // Fallback
                        ];
                    } else {
                        \Log::warning("CartItem {$cartItem->id} tiene un product_id inválido o producto no encontrado. Eliminando.");
                        $cartItem->delete();
                    }
                }
            }
        } else {
            $sessionCart = Session::get('cart', []);
            $validSessionCart = [];
            foreach ($sessionCart as $item) {
                $product = Product::find($item['id']); // 'id' en sesión es el product_id
                if ($product) {
                    $priceNet = (float) $product->price;
                    $subtotalNet = ($item['quantity'] ?? 0) * $priceNet;
                    $subtotalGross = round($subtotalNet * (1 + self::IVA_RATE), 2);

                    $validSessionCart[] = [
                        'id' => $item['id'], // Para la UI y JS, mantenemos 'id' como product_id en sesión
                        'product_id' => $item['id'], // También product_id para consistencia
                        'name' => $item['name'] ?? $product->name, // Fallback por si 'name' no está en sesión
                        'quantity' => $item['quantity'] ?? 0, // Fallback
                        'price_net' => $priceNet,
                        'subtotal_item_net' => $subtotalNet,
                        'subtotal_item_gross' => $subtotalGross,
                        'image_url' => $product->image_url ?? asset('images/default_product.png'), // Fallback
                    ];
                } else {
                    \Log::warning("Producto con ID {$item['id']} en sesión no encontrado. No se añadirá a los ítems formateados.");
                }
            }
            if (count($validSessionCart) !== count($sessionCart)) {
                Session::put('cart', $validSessionCart);
            }
            $formattedItems = $validSessionCart;
        }

        return $formattedItems;
    }

    /**
     * Calcula todos los totales del carrito.
     *
     * @param array $cartItems // Este es el array de ítems formateados que espera
     * @return array
     */
    public function calculateCartTotals(array $cartItems)
    {
        $subtotal_net_products = 0;
        $subtotal_gross_products = 0;

        foreach ($cartItems as $item) {
            $subtotal_net_products += $item['subtotal_item_net'] ?? 0;
            $subtotal_gross_products += $item['subtotal_item_gross'] ?? 0;
        }

        $iva_products_amount = round($subtotal_gross_products - ($subtotal_gross_products / (1 + self::IVA_RATE)), 2);

        $commission_percent_value = $subtotal_gross_products * self::MP_COMMISSION_PERCENTAGE;
        $iva_on_commission_percent = $commission_percent_value * self::IVA_RATE;
        $mp_fee_amount = round(($commission_percent_value + $iva_on_commission_percent) + self::MP_FIXED_FEE, 2);

        $final_total = round($subtotal_gross_products + $mp_fee_amount, 2);

        $cartCount = 0;
        foreach ($cartItems as $item) {
            $cartCount += $item['quantity'] ?? 0;
        }

        return [
            'cartItems' => $cartItems,
            'subtotal_net_products' => round($subtotal_net_products, 2),
            'iva_products_amount' => $iva_products_amount,
            'subtotal_gross_products' => round($subtotal_gross_products, 2),
            'mp_fee_amount' => $mp_fee_amount,
            'final_total' => $final_total,
            'cartCount' => $cartCount
        ];
    }
}
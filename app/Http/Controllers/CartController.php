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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function add(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);

        $product = Product::findOrFail($productId);

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
                    'price_at_addition' => $product->price, // Guarda el precio del producto al momento de añadir
                ]);
            }
        } else {
            $sessionCart = Session::get('cart', []);
            $found = false;
            foreach ($sessionCart as $key => &$item) { // Usar & para modificar el array directamente
                if ($item['id'] == $productId) {
                    $sessionCart[$key]['quantity'] += $quantity;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $sessionCart[] = [
                    'id' => $productId,
                    'name' => $product->name,
                    'price' => $product->price, // Precio del producto
                    'image_url' => $product->image_url, // Corregido a 'image_url' para consistencia
                    'quantity' => $quantity
                ];
            }
            Session::put('cart', $sessionCart);
        }

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
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');

        // Validar cantidad
        if (!is_numeric($quantity) || $quantity < 0) {
            return response()->json(['message' => 'Cantidad inválida.'], 400);
        }

        // Si la cantidad es 0, llamamos al método remove
        if ($quantity == 0) {
            return $this->remove($request); // Remove ya devuelve JSON
        }

        $cartItem = null; // Para almacenar el item actualizado si existe

        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart;
            if ($cart) {
                $cartItem = $cart->cartItems()->where('product_id', $productId)->first();
                if ($cartItem) {
                    $cartItem->quantity = $quantity;
                    $cartItem->save();
                } else {
                    return response()->json(['message' => 'Producto no encontrado en el carrito del usuario.'], 404);
                }
            } else {
                 return response()->json(['message' => 'Carrito no encontrado para el usuario.'], 404);
            }
        } else { // Carrito de invitado
            $sessionCart = Session::get('cart', []);
            $updated = false;
            foreach ($sessionCart as $key => &$item) {
                if ($item['id'] == $productId) {
                    $item['quantity'] = $quantity;
                    $updated = true;
                    break;
                }
            }
            Session::put('cart', $sessionCart);

            if (!$updated) {
                return response()->json(['message' => 'Producto no encontrado en el carrito de sesión.'], 404);
            }
        }

        // Recalcular y devolver los nuevos totales
        $formattedCartItems = $this->getFormattedCartItems();
        $cartTotals = $this->calculateCartTotals($formattedCartItems);

        // Buscar el item que se acaba de actualizar para devolverlo en la respuesta
        $updatedFormattedItem = null;
        foreach ($formattedCartItems as $item) {
            if ($item['product_id'] == $productId) { // Asegúrate de usar 'product_id' aquí
                $updatedFormattedItem = $item;
                break;
            }
        }

        return response()->json(array_merge($cartTotals, [
            'message' => 'Cantidad actualizada.',
            'cartCount' => count($formattedCartItems),
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
        $productId = $request->input('product_id');

        $deleted = false;
        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart;
            if ($cart) {
                $deleted = $cart->cartItems()->where('product_id', $productId)->delete();
            }
        } else {
            $sessionCart = Session::get('cart', []);
            $initialCount = count($sessionCart);
            $sessionCart = array_filter($sessionCart, function ($item) use ($productId) {
                return $item['id'] != $productId;
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
            'cartCount' => count($formattedCartItems)
        ]));
    }

    /**
     * Obtiene el conteo de ítems distintos en el carrito (para la API).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCartItemCount()
    {
        $count = 0;
        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart;
            if ($cart) {
                $count = $cart->cartItems->count();
            }
        } else {
            $sessionCart = Session::get('cart', []);
            $count = count($sessionCart);
        }
        return response()->json(['cartCount' => $count]); // Cambiado 'count' a 'cartCount' para consistencia con JS
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
            if ($cart && $cart->cartItems) {
                foreach ($cart->cartItems as $cartItem) {
                    if ($cartItem->product) {
                        $priceNet = $cartItem->product->price;
                        $subtotalNet = $cartItem->quantity * $priceNet;
                        $subtotalGross = round($subtotalNet * (1 + self::IVA_RATE), 2);

                        $formattedItems[] = [
                            'id' => $cartItem->id, // Este es el ID de CartItem, no el product_id
                            'product_id' => $cartItem->product_id, // Añadimos product_id
                            'name' => $cartItem->product->name,
                            'quantity' => $cartItem->quantity,
                            'price_net' => $priceNet,
                            'subtotal_item_net' => $subtotalNet,
                            'subtotal_item_gross' => $subtotalGross,
                            'image_url' => $cartItem->product->image_url,
                        ];
                    } else {
                        // Loguear y eliminar ítems huérfanos
                        \Log::warning("CartItem {$cartItem->id} tiene un product_id inválido o producto no encontrado. Eliminando.");
                        $cartItem->delete();
                    }
                }
            }
        } else {
            $sessionCart = Session::get('cart', []);
            // Limpiar la sesión de ítems inválidos si el producto ya no existe
            $validSessionCart = [];
            foreach ($sessionCart as $item) {
                $product = Product::find($item['id']); // 'id' en sesión es el product_id
                if ($product) {
                    $priceNet = $product->price;
                    $subtotalNet = $item['quantity'] * $priceNet;
                    $subtotalGross = round($subtotalNet * (1 + self::IVA_RATE), 2);

                    $validSessionCart[] = [
                        'id' => $item['id'], // Para la UI y JS, mantenemos 'id' como product_id en sesión
                        'product_id' => $item['id'], // También product_id para consistencia
                        'name' => $item['name'],
                        'quantity' => $item['quantity'],
                        'price_net' => $priceNet,
                        'subtotal_item_net' => $subtotalNet,
                        'subtotal_item_gross' => $subtotalGross,
                        'image_url' => $product->image_url,
                    ];
                } else {
                    \Log::warning("Producto con ID {$item['id']} en sesión no encontrado. Eliminando del carrito de sesión.");
                }
            }
            // Si se eliminaron ítems inválidos, actualizar la sesión
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
            $subtotal_net_products += $item['subtotal_item_net'];
            $subtotal_gross_products += $item['subtotal_item_gross'];
        }

        $iva_products_amount = round($subtotal_gross_products - ($subtotal_gross_products / (1 + self::IVA_RATE)), 2);

        $commission_percent_value = $subtotal_gross_products * self::MP_COMMISSION_PERCENTAGE;
        // La cuota fija de MP generalmente no lleva IVA adicional, pero el porcentaje sí.
        $iva_on_commission_percent = $commission_percent_value * self::IVA_RATE; 
        $mp_fee_amount = round(($commission_percent_value + $iva_on_commission_percent) + self::MP_FIXED_FEE, 2);

        $final_total = round($subtotal_gross_products + $mp_fee_amount, 2);

        // Aquí ya tienes cartItems, puedes calcular el conteo directamente
        $cartCount = count($cartItems); 

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
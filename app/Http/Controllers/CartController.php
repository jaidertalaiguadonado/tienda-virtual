<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

class CartController extends Controller
{
    // Define la tasa de IVA globalmente para consistencia
    private $ivaRate = 0.19; // 19% IVA en Colombia

    /**
     * Muestra la página del carrito de compras.
     */
    public function show()
    {
        $cart = null;
        // subtotal_products_only_NET será el subtotal de los productos SIN IVA
        $subtotal_products_only_NET = 0;
        $formattedCartItems = collect();

        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart()->with('items.product')->firstOrCreate(['user_id' => $user->id]);

            if ($cart) {
                foreach ($cart->items as $item) {
                    if ($item->product) {
                        // Usar el precio neto del producto de la DB
                        $subtotal_products_only_NET += $item->quantity * $item->product->price;
                    } else {
                        \Log::warning('Producto no encontrado para CartItem ID: ' . $item->id);
                    }
                }
            }

            $formattedCartItems = $cart ? $cart->items->map(function($item) {
                $productPriceNet = $item->product->price ?? $item->price_at_addition;
                $productPriceGross = round($productPriceNet * (1 + $this->ivaRate), 2); // Calcular precio bruto para la vista

                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product->name ?? 'Producto Desconocido',
                    'price_net' => $productPriceNet,
                    'price_gross' => $productPriceGross,
                    'quantity' => $item->quantity,
                    'image' => $item->product->image_url ?? asset('images/default_product.png'),
                    'subtotal_item_net' => $item->quantity * $productPriceNet,
                    'subtotal_item_gross' => $item->quantity * $productPriceGross,
                ];
            }) : collect();

        } else {
            $sessionCart = Session::get('cart', []);
            $formattedCartItems = collect($sessionCart)->map(function($item) {
                $product = Product::find($item['id']);
                $productPriceNet = $product ? $product->price : $item['price']; // Asumiendo que item['price'] también es neto si no hay producto
                $productPriceGross = round($productPriceNet * (1 + $this->ivaRate), 2);

                return [
                    'id' => $item['id'],
                    'product_id' => $item['id'],
                    'name' => $item['name'],
                    'price_net' => $productPriceNet,
                    'price_gross' => $productPriceGross,
                    'quantity' => $item['quantity'],
                    'image' => $item['image'] ?? asset('images/default_product.png'),
                    'subtotal_item_net' => $productPriceNet * $item['quantity'],
                    'subtotal_item_gross' => $productPriceGross * $item['quantity'],
                ];
            });

            // Sumar el subtotal NETO para pasar a la función de cálculo
            $subtotal_products_only_NET = $formattedCartItems->sum('subtotal_item_net');
        }

        // ===============================================================
        // CÁLCULO DE IVA Y COMISIÓN DE MERCADO PAGO (Ahora asumiendo subtotal NETO de productos)
        // ===============================================================
        $totals = $this->calculateCartTotals($subtotal_products_only_NET);

        return view('cart.show', [
            'cartItems' => $formattedCartItems,
            'subtotal_net_products' => $totals['subtotal_net_products'],      // Subtotal de productos SIN IVA
            'iva_products_amount' => $totals['iva_products_amount'],          // IVA calculado sobre productos
            'subtotal_gross_products' => $totals['subtotal_gross_products'],  // Subtotal de productos CON IVA
            'mp_fee_amount' => $totals['mp_fee_amount'],                      // Monto de la comisión total de Mercado Pago (incluyendo su IVA)
            'final_total' => $totals['final_total'],                          // Total final con todo
            'cart' => $cart,
        ]);
    }

    /**
     * Añade un producto al carrito.
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

        $currentSubtotalNet = 0; // Inicializar para la respuesta, será el subtotal NETO

        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart()->firstOrCreate(['user_id' => $user->id]);

            $cartItem = $cart->items()->where('product_id', $productId)->first();

            if ($cartItem) {
                if ($product->stock < ($cartItem->quantity + $quantity)) {
                    return response()->json(['message' => 'Stock insuficiente para la cantidad solicitada.'], 400);
                }
                $cartItem->quantity += $quantity;
                $cartItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price_at_addition' => $product->price, // Aquí guardamos el precio NETO
                    'image_path' => $product->image_url,
                ]);
            }
            $cart->refresh();
            foreach ($cart->items as $item) {
                if ($item->product) {
                    $currentSubtotalNet += $item->quantity * $item->product->price; // Sumamos precios NETOS
                }
            }

        } else {
            $sessionCart = Session::get('cart', []);

            if (isset($sessionCart[$productId])) {
                 $currentTotalQuantity = $sessionCart[$productId]['quantity'] + $quantity;
                if ($product->stock < $currentTotalQuantity) {
                    return response()->json(['message' => 'Stock insuficiente para la cantidad solicitada.'], 400);
                }
                $sessionCart[$productId]['quantity'] = $currentTotalQuantity;
            } else {
                $sessionCart[$productId] = [
                    'id' => $productId,
                    'name' => $product->name,
                    'price' => $product->price, // Guardamos el precio NETO
                    'image' => $product->image_url,
                    'quantity' => $quantity,
                    // Subtotal item en sesión también será NETO
                    'subtotal_item' => $product->price * $quantity,
                ];
            }
            Session::put('cart', $sessionCart);

            $currentSubtotalNet = array_sum(array_map(function($item) {
                return $item['price'] * $item['quantity']; // Sumamos precios NETOS
            }, $sessionCart));
        }

        // Aquí solo devolvemos el subtotal de productos NETO para el AJAX
        return response()->json([
            'message' => 'Producto añadido al carrito correctamente.',
            'cartCount' => $this->getCartCount(),
            'subtotal_net' => $currentSubtotalNet,
        ]);
    }

    /**
     * Actualiza la cantidad de un producto en el carrito.
     */
    public function update(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required',
            'quantity' => 'required|integer|min:0',
        ]);

        $cartItemId = $request->cart_item_id;
        $newQuantity = $request->quantity;

        $subtotal_products_only_NET = 0; // Será el subtotal neto de productos
        $itemSubtotalNet = 0; // Para el subtotal neto del ítem afectado

        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart()->with('items.product')->first();
            if (!$cart) {
                return response()->json(['success' => false, 'message' => 'Carrito no encontrado.'], 404);
            }

            $cartItem = $cart->items->firstWhere('id', $cartItemId);
            if (!$cartItem) {
                return response()->json(['success' => false, 'message' => 'Producto no encontrado en el carrito.'], 404);
            }

            $product = $cartItem->product;
            if (!$product) {
                $cartItem->delete();
                $cart->refresh();
                foreach ($cart->items as $item) {
                    if ($item->product) {
                        $subtotal_products_only_NET += $item->quantity * $item->product->price;
                    }
                }
                $totals = $this->calculateCartTotals($subtotal_products_only_NET);
                return response()->json([
                    'success' => true,
                    'message' => 'Producto no encontrado, eliminado del carrito.',
                    'subtotal_net_products' => $totals['subtotal_net_products'],
                    'iva_products_amount' => $totals['iva_products_amount'],
                    'subtotal_gross_products' => $totals['subtotal_gross_products'],
                    'mp_fee_amount' => $totals['mp_fee_amount'],
                    'final_total' => $totals['final_total'],
                    'cartCount' => $this->getCartCount(),
                ], 200);
            }

            if ($newQuantity > 0) {
                if ($product->stock < $newQuantity) {
                    return response()->json(['success' => false, 'message' => 'Stock insuficiente para la cantidad solicitada.'], 400);
                }
                $cartItem->quantity = $newQuantity;
                $cartItem->save();
                $itemSubtotalNet = $newQuantity * $product->price; // Subtotal neto del ítem
            } else {
                $cartItem->delete();
            }

            $cart->refresh();
            foreach ($cart->items as $item) {
                if ($item->product) {
                    $subtotal_products_only_NET += $item->quantity * $item->product->price;
                }
            }

        } else { // Usuario Invitado
            $sessionCart = Session::get('cart', []);
            $productId = $cartItemId;

            $found = false;
            foreach ($sessionCart as $key => &$item) {
                if ($item['id'] == $productId) {
                    $product = Product::find($productId);
                    if (!$product) {
                        unset($sessionCart[$key]);
                        $found = true;
                        break;
                    }

                    if ($newQuantity > 0) {
                        if ($product->stock < $newQuantity) {
                            return response()->json(['success' => false, 'message' => 'Stock insuficiente para la cantidad solicitada.'], 400);
                        }
                        $item['quantity'] = $newQuantity;
                        $item['price'] = $product->price; // Asegurarse que el precio en sesión es el actual NETO
                        $item['subtotal_item'] = $newQuantity * $product->price; // Recalcular subtotal NETO del ítem
                        $itemSubtotalNet = $item['subtotal_item'];
                    } else {
                        unset($sessionCart[$key]);
                    }
                    $found = true;
                    break;
                }
            }
            unset($item);

            if (!$found) {
                return response()->json(['success' => false, 'message' => 'Producto no encontrado en el carrito de sesión.'], 404);
            }
            Session::put('cart', $sessionCart);

            $subtotal_products_only_NET = collect($sessionCart)->sum('subtotal_item');
        }

        // Recalcular todos los totales con la lógica unificada
        $totals = $this->calculateCartTotals($subtotal_products_only_NET);

        return response()->json([
            'success' => true,
            'message' => 'Carrito actualizado.',
            'item' => [
                'id' => $cartItemId,
                'product_id' => $cartItemId,
                'quantity' => $newQuantity,
                'subtotal_item_net' => $itemSubtotalNet, // Subtotal NETO del ítem individual
                'subtotal_item_gross' => round($itemSubtotalNet * (1 + $this->ivaRate), 2), // Subtotal BRUTO del ítem individual
            ],
            'subtotal_net_products' => $totals['subtotal_net_products'],
            'iva_products_amount' => $totals['iva_products_amount'],
            'subtotal_gross_products' => $totals['subtotal_gross_products'],
            'mp_fee_amount' => $totals['mp_fee_amount'],
            'final_total' => $totals['final_total'],
            'cartCount' => $this->getCartCount(),
        ]);
    }

    /**
     * Elimina un producto del carrito.
     */
    public function remove(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required',
        ]);

        $cartItemId = $request->cart_item_id;
        $subtotal_products_only_NET = 0;

        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart()->with('items.product')->first();
            if (!$cart) {
                return response()->json(['success' => false, 'message' => 'Carrito no encontrado.'], 404);
            }

            $cartItem = $cart->items->firstWhere('id', $cartItemId);
            if ($cartItem) {
                $cartItem->delete();
            } else {
                return response()->json(['success' => false, 'message' => 'Producto no encontrado en el carrito.'], 404);
            }
            $cart->refresh();
            foreach ($cart->items as $item) {
                if ($item->product) {
                    $subtotal_products_only_NET += $item->quantity * $item->product->price;
                }
            }

        } else {
            $sessionCart = Session::get('cart', []);
            $productId = $cartItemId;

            $found = false;
            foreach ($sessionCart as $key => $item) {
                if ($item['id'] == $productId) {
                    unset($sessionCart[$key]);
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                return response()->json(['success' => false, 'message' => 'Producto no encontrado en el carrito de sesión.'], 404);
            }
            Session::put('cart', $sessionCart);

            $subtotal_products_only_NET = collect($sessionCart)->sum('subtotal_item');
        }

        $totals = $this->calculateCartTotals($subtotal_products_only_NET);

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado del carrito.',
            'subtotal_net_products' => $totals['subtotal_net_products'],
            'iva_products_amount' => $totals['iva_products_amount'],
            'subtotal_gross_products' => $totals['subtotal_gross_products'],
            'mp_fee_amount' => $totals['mp_fee_amount'],
            'final_total' => $totals['final_total'],
            'cartCount' => $this->getCartCount(),
        ]);
    }

    /**
     * Sincroniza el carrito de sesión con el carrito de la base de datos al iniciar sesión.
     */
    public static function syncCart()
    {
        if (Auth::check() && Session::has('cart')) {
            $user = Auth::user();
            $sessionCart = Session::get('cart');

            $cart = $user->cart()->firstOrCreate(['user_id' => $user->id]);

            foreach ($sessionCart as $productId => $itemData) {
                $product = Product::find($productId);
                if (!$product) {
                    continue;
                }

                $cartItem = $cart->items()->where('product_id', $productId)->first();

                if ($cartItem) {
                    $cartItem->quantity += $itemData['quantity'];
                    $cartItem->save();
                } else {
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $productId,
                        'quantity' => $itemData['quantity'],
                        'price_at_addition' => $product->price, // Asumiendo que product->price es NETO
                        'image_path' => $product->image_url,
                    ]);
                }
            }

            Session::forget('cart');
        }
    }

    /**
     * Helper para obtener el conteo total de ítems en el carrito.
     */
    private function getCartCount()
    {
        if (Auth::check()) {
            $cart = Auth::user()->cart;
            return $cart ? $cart->items->sum('quantity') : 0;
        } else {
            $sessionCart = Session::get('cart', []);
            return array_sum(array_column($sessionCart, 'quantity'));
        }
    }

    /**
     * Helper para calcular el IVA y la comisión de Mercado Pago.
     * Recibe el subtotal de productos NETO (sin IVA de productos).
     * @param float $subtotal_products_only_NET El subtotal de los productos sin IVA.
     * @return array Con subtotal_net_products, iva_products_amount, subtotal_gross_products, mp_fee_amount, final_total.
     */
    public function calculateCartTotals($subtotal_products_only_NET)
    {
        $iva_rate = $this->ivaRate; // 19% IVA en Colombia
        $mercadopago_fee_percentage = 0.0329; // 3.29% de Mercado Pago
        $mercadopago_fixed_fee = 952.00; // Monto fijo de Mercado Pago

        // 1. Calcular el IVA para los productos (se aplica sobre el precio NETO)
        $iva_products_amount = round($subtotal_products_only_NET * $iva_rate, 2);

        // Subtotal BRUTO de los productos (NETO + IVA de productos)
        $subtotal_products_GROSS = $subtotal_products_only_NET + $iva_products_amount;

        // 2. Calcular la comisión de Mercado Pago (aplicada sobre el subtotal BRUTO de los productos)
        $mp_commission_on_gross_subtotal = $subtotal_products_GROSS * $mercadopago_fee_percentage;

        // Base para el cálculo del IVA de Mercado Pago (porcentaje + fijo)
        $mp_commission_base = $mp_commission_on_gross_subtotal + $mercadopago_fixed_fee;

        // IVA sobre la comisión de Mercado Pago
        $mp_iva_on_fee = round($mp_commission_base * $iva_rate, 2);

        // Monto total de la comisión de Mercado Pago (base + IVA de la comisión)
        $mp_fee_amount = round($mp_commission_base + $mp_iva_on_fee, 2);

        // 3. Total final a pagar por el cliente
        // Es el subtotal BRUTO de los productos MÁS el monto total de la comisión de Mercado Pago.
        $final_total = round($subtotal_products_GROSS + $mp_fee_amount, 2);

        return [
            'subtotal_net_products' => $subtotal_products_only_NET,
            'iva_products_amount' => $iva_products_amount,
            'subtotal_gross_products' => $subtotal_products_GROSS,
            'mp_fee_amount' => $mp_fee_amount,
            'final_total' => $final_total,
        ];
    }
}
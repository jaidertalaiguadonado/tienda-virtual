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
    /**
     * Muestra la página del carrito de compras.
     */
    public function show()
    {
        $cart = null;
        $subtotal_products_only = 0;
        $formattedCartItems = collect();

        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart()->with('items.product')->firstOrCreate(['user_id' => $user->id]);

            if ($cart) {
                foreach ($cart->items as $item) {
                    if ($item->product) {
                        $subtotal_products_only += $item->quantity * $item->product->price;
                    } else {
                        \Log::warning('Producto no encontrado para CartItem ID: ' . $item->id);
                    }
                }
            }

            $formattedCartItems = $cart ? $cart->items->map(function($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product->name ?? 'Producto Desconocido',
                    'price' => $item->product->price ?? $item->price_at_addition,
                    'quantity' => $item->quantity,
                    'image' => $item->product->image_url ?? asset('images/default_product.png'),
                    'subtotal_item' => $item->quantity * ($item->product->price ?? $item->price_at_addition),
                ];
            }) : collect();

        } else {
            $sessionCart = Session::get('cart', []);
            $formattedCartItems = collect($sessionCart)->map(function($item) {
                $product = Product::find($item['id']);
                $productPrice = $product ? $product->price : $item['price'];

                return [
                    'id' => $item['id'],
                    'product_id' => $item['id'],
                    'name' => $item['name'],
                    'price' => $productPrice,
                    'quantity' => $item['quantity'],
                    'image' => $item['image'] ?? asset('images/default_product.png'),
                    'subtotal_item' => $productPrice * $item['quantity'],
                ];
            });

            $subtotal_products_only = $formattedCartItems->sum('subtotal_item');
        }

        $totals = $this->calculateCartTotals($subtotal_products_only);

        return view('cart.show', [
            'cartItems' => $formattedCartItems,
            'subtotal' => $totals['subtotal'],
            'iva_amount' => $totals['iva_amount'],
            'mp_fee_amount' => $totals['mp_fee_amount'],
            'final_total' => $totals['final_total'],
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

        $currentSubtotal = 0;

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
                    'price_at_addition' => $product->price,
                    'image_path' => $product->image_url,
                ]);
            }
            $cart->refresh();
            foreach ($cart->items as $item) {
                if ($item->product) {
                    $currentSubtotal += $item->quantity * $item->product->price;
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
                    'price' => $product->price,
                    'image' => $product->image_url,
                    'quantity' => $quantity,
                    'subtotal_item' => $product->price * $quantity,
                ];
            }
            Session::put('cart', $sessionCart);

            $currentSubtotal = array_sum(array_map(function($item) {
                return $item['price'] * $item['quantity'];
            }, $sessionCart));
        }

        return response()->json([
            'message' => 'Producto añadido al carrito correctamente.',
            'cartCount' => $this->getCartCount(),
            'total' => $currentSubtotal,
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

        $subtotal_products_only = 0;
        $itemSubtotal = 0;

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
                        $subtotal_products_only += $item->quantity * $item->product->price;
                    }
                }
                $totals = $this->calculateCartTotals($subtotal_products_only);
                return response()->json([
                    'success' => true,
                    'message' => 'Producto no encontrado, eliminado del carrito.',
                    'subtotal' => $totals['subtotal'],
                    'iva_amount' => $totals['iva_amount'],
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
                $itemSubtotal = $newQuantity * $product->price;
            } else {
                $cartItem->delete();
            }

            $cart->refresh();
            foreach ($cart->items as $item) {
                if ($item->product) {
                    $subtotal_products_only += $item->quantity * $item->product->price;
                }
            }

        } else {
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
                        $item['price'] = $product->price;
                        $item['subtotal_item'] = $newQuantity * $product->price;
                        $itemSubtotal = $item['subtotal_item'];
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

            $subtotal_products_only = collect($sessionCart)->sum('subtotal_item');
        }

        $totals = $this->calculateCartTotals($subtotal_products_only);

        return response()->json([
            'success' => true,
            'message' => 'Carrito actualizado.',
            'item' => [
                'id' => $cartItemId,
                'product_id' => $cartItemId,
                'quantity' => $newQuantity,
                'subtotal_item' => $itemSubtotal,
            ],
            'subtotal' => $totals['subtotal'],
            'iva_amount' => $totals['iva_amount'],
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
        $subtotal_products_only = 0;

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
                    $subtotal_products_only += $item->quantity * $item->product->price;
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

            $subtotal_products_only = collect($sessionCart)->sum('subtotal_item');
        }

        $totals = $this->calculateCartTotals($subtotal_products_only);

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado del carrito.',
            'subtotal' => $totals['subtotal'],
            'iva_amount' => $totals['iva_amount'],
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
                        'price_at_addition' => $product->price,
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
     * @param float $subtotal_products_only El subtotal de los productos, asumiendo que ya incluye su IVA.
     * @return array Con subtotal, iva_amount (IVA de productos), mp_fee_amount (comisión MP total), final_total.
     */
    public function calculateCartTotals($subtotal_products_only) // <-- ¡CAMBIO A PUBLIC!
    {
        $iva_rate = 0.19; // 19% IVA en Colombia
        $mercadopago_fee_percentage = 0.0329; // 3.29% de Mercado Pago
        $mercadopago_fixed_fee = 952.00; // Monto fijo de Mercado Pago

        $iva_amount = round($subtotal_products_only * ($iva_rate / (1 + $iva_rate)), 2);

        $mp_commission_on_subtotal = $subtotal_products_only * $mercadopago_fee_percentage;
        $mp_commission_base = $mp_commission_on_subtotal + $mercadopago_fixed_fee;
        $mp_iva_on_fee = round($mp_commission_base * $iva_rate, 2);
        $mp_fee_amount = round($mp_commission_base + $mp_iva_on_fee, 2);

        $final_total = round($subtotal_products_only + $mp_fee_amount, 2);

        return [
            'subtotal' => $subtotal_products_only,
            'iva_amount' => $iva_amount,
            'mp_fee_amount' => $mp_fee_amount,
            'final_total' => $final_total,
        ];
    }
}
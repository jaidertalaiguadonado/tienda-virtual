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
            foreach ($sessionCart as $key => $item) {
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
                    'image' => $product->image_url,
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');

        if ($quantity <= 0) {
            return $this->remove($request);
        }

        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart;
            if ($cart) {
                $cartItem = $cart->cartItems()->where('product_id', $productId)->first();
                if ($cartItem) {
                    $cartItem->quantity = $quantity;
                    $cartItem->save();
                }
            }
        } else {
            $sessionCart = Session::get('cart', []);
            foreach ($sessionCart as $key => $item) {
                if ($item['id'] == $productId) {
                    $sessionCart[$key]['quantity'] = $quantity;
                    break;
                }
            }
            Session::put('cart', $sessionCart);
        }

        return redirect()->back()->with('success', 'Cantidad actualizada.');
    }

    /**
     * Elimina un producto del carrito.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Request $request)
    {
        $productId = $request->input('product_id');

        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart;
            if ($cart) {
                $cart->cartItems()->where('product_id', $productId)->delete();
            }
        } else {
            $sessionCart = Session::get('cart', []);
            $sessionCart = array_filter($sessionCart, function ($item) use ($productId) {
                return $item['id'] != $productId;
            });
            Session::put('cart', $sessionCart);
        }

        return redirect()->back()->with('success', 'Producto eliminado del carrito.');
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
        return response()->json(['count' => $count]);
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
            $cart = $user->cart()->with('cartItems.product')->first(); // Carga la relación 'cartItems'
            if ($cart && $cart->cartItems) {
                foreach ($cart->cartItems as $cartItem) {
                    if ($cartItem->product) {
                        $priceNet = $cartItem->product->price;
                        $subtotalNet = $cartItem->quantity * $priceNet;
                        $subtotalGross = round($subtotalNet * (1 + self::IVA_RATE), 2);

                        $formattedItems[] = [
                            'id' => $cartItem->id,
                            'product_id' => $cartItem->product_id,
                            'name' => $cartItem->product->name,
                            'quantity' => $cartItem->quantity,
                            'price_net' => $priceNet,
                            'subtotal_item_net' => $subtotalNet,
                            'subtotal_item_gross' => $subtotalGross,
                            'image_url' => $cartItem->product->image_url,
                        ];
                    } else {
                        // Opcional: limpiar ítems huérfanos o loguear
                        \Log::warning("CartItem {$cartItem->id} tiene un product_id inválido.");
                        $cartItem->delete();
                    }
                }
            }
        } else {
            $sessionCart = Session::get('cart', []);
            foreach ($sessionCart as $item) {
                $product = Product::find($item['id']);
                if ($product) {
                    $priceNet = $product->price;
                    $subtotalNet = $item['quantity'] * $priceNet;
                    $subtotalGross = round($subtotalNet * (1 + self::IVA_RATE), 2);

                    $formattedItems[] = [
                        'id' => $item['id'], // En sesión, el 'id' es el product_id
                        'product_id' => $item['id'],
                        'name' => $item['name'],
                        'quantity' => $item['quantity'],
                        'price_net' => $priceNet,
                        'subtotal_item_net' => $subtotalNet,
                        'subtotal_item_gross' => $subtotalGross,
                        'image_url' => $product->image_url,
                    ];
                }
            }
        }

        return $formattedItems;
    }

    /**
     * Calcula todos los totales del carrito.
     *
     * @param array $cartItems // Este es el array de ítems formateados que espera
     * @return array
     */
    public function calculateCartTotals(array $cartItems) // <--- CAMBIADO A 'public'
    {
        $subtotal_net_products = 0;
        $subtotal_gross_products = 0;

        foreach ($cartItems as $item) {
            $subtotal_net_products += $item['subtotal_item_net'];
            $subtotal_gross_products += $item['subtotal_item_gross'];
        }

        $iva_products_amount = round($subtotal_gross_products - ($subtotal_gross_products / (1 + self::IVA_RATE)), 2); // Redondeo aquí

        $commission_percent_value = $subtotal_gross_products * self::MP_COMMISSION_PERCENTAGE;
        $iva_on_commission_percent = $commission_percent_value * self::IVA_RATE;
        $mp_fee_amount = round(($commission_percent_value + $iva_on_commission_percent) + self::MP_FIXED_FEE, 2);

        $final_total = round($subtotal_gross_products + $mp_fee_amount, 2);

        $cartCount = $this->getCartItemCount()->original['count']; // Obtener el valor del JSON response

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
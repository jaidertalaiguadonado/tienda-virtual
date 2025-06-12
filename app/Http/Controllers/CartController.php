<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    const IVA_RATE = 0.19;
    const MP_COMMISSION_PERCENTAGE = 0.0329;
    const MP_FIXED_FEE = 952.00;

    /**
     * Muestra el contenido del carrito de compras.
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }

        if (Auth::check()) {
            $cart = Auth::user()->cart;
            if (!$cart) {
                $cart = Auth::user()->cart()->create([]);
            }

            $cartItem = $cart->cartItems()->where('product_id', $productId)->first();

            if ($cartItem) {
                $cartItem->quantity += $quantity;
                $cartItem->save();
            } else {
                $cart->cartItems()->create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                ]);
            }
        } else {
            $cart = Session::get('cart', []);

            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] += $quantity;
            } else {
                $cart[$productId] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                ];
            }
            Session::put('cart', $cart);
        }

        $cartCount = $this->getCartItemCount();

        return response()->json(['message' => 'Producto añadido al carrito.', 'cartCount' => $cartCount]);
    }

    /**
     * Actualiza la cantidad de un producto en el carrito.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $identifier = $request->input('id');
        $newQuantity = $request->input('quantity');

        if (!is_numeric($newQuantity) || $newQuantity < 0) {
            return response()->json(['message' => 'Cantidad inválida.'], 400);
        }

        $productDataForUpdate = null;

        if (Auth::check()) {
            $cartItem = CartItem::find($identifier);
            if (!$cartItem || ($cartItem->cart && $cartItem->cart->user_id !== Auth::id())) {
                return response()->json(['message' => 'Producto no encontrado en el carrito.'], 404);
            }

            if ($newQuantity === 0) {
                $cartItem->delete();
            } else {
                $cartItem->quantity = $newQuantity;
                $cartItem->save();
            }

            $product = $cartItem->product ?? Product::find($cartItem->product_id);
            if ($product) {
                $price_net = (float) $product->price;
                $price_gross = $price_net * (1 + self::IVA_RATE);
                $productDataForUpdate = [
                    'id' => $identifier,
                    'quantity' => $newQuantity,
                    'subtotal_item_net' => round($price_net * $newQuantity, 2),
                    'subtotal_item_gross' => round($price_gross * $newQuantity, 2),
                ];
            }

        } else {
            $cart = Session::get('cart', []);
            if (!isset($cart[$identifier])) {
                return response()->json(['message' => 'Producto no encontrado en el carrito.'], 404);
            }

            if ($newQuantity === 0) {
                unset($cart[$identifier]);
            } else {
                $cart[$identifier]['quantity'] = $newQuantity;
            }
            Session::put('cart', $cart);

            $product = Product::find($identifier);
            if ($product) {
                $price_net = (float) $product->price;
                $price_gross = $price_net * (1 + self::IVA_RATE);
                $productDataForUpdate = [
                    'id' => $identifier,
                    'quantity' => $newQuantity,
                    'subtotal_item_net' => round($price_net * $newQuantity, 2),
                    'subtotal_item_gross' => round($price_gross * $newQuantity, 2),
                ];
            }
        }

        $cartItems = $this->getFormattedCartItems();
        $totals = $this->calculateCartTotals($cartItems);

        return response()->json(array_merge([
            'message' => 'Carrito actualizado exitosamente.',
            'item' => $productDataForUpdate,
        ], $totals));
    }

    /**
     * Elimina un producto del carrito.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(Request $request)
    {
        $identifier = $request->input('id');

        if (Auth::check()) {
            $cartItem = CartItem::find($identifier);
            if ($cartItem && ($cartItem->cart && $cartItem->cart->user_id === Auth::id())) {
                $cartItem->delete();
            }
        } else {
            $cart = Session::get('cart', []);
            if (isset($cart[$identifier])) {
                unset($cart[$identifier]);
                Session::put('cart', $cart);
            }
        }

        $cartItems = $this->getFormattedCartItems();
        $totals = $this->calculateCartTotals($cartItems);

        return response()->json(array_merge([
            'message' => 'Producto eliminado del carrito.',
        ], $totals));
    }

    /**
     * Devuelve el número total de ítems distintos en el carrito.
     * Útil para el ícono del carrito en la barra de navegación.
     *
     * @return int
     */
    public function getCartItemCount()
    {
        if (Auth::check()) {
            $cart = Auth::user()->cart;
            // Asegura que cartItems sea una colección antes de sumar
            return $cart ? ($cart->cartItems ?? collect())->sum('quantity') : 0;
        } else {
            $cart = Session::get('cart', []);
            // Aseguramos que $cart sea un array, incluso si Session::get() falla por alguna razón exótica
            if (!is_array($cart)) {
                $cart = [];
            }

            $count = 0;
            foreach ($cart as $item) {
                $count += $item['quantity'];
            }
            return $count;
        }
    }

    /**
     * Obtiene los ítems del carrito (sesión o DB) y los formatea con precios netos y brutos.
     * Asume que `product->price` en la DB es el precio neto.
     *
     * @return array
     */
    protected function getFormattedCartItems()
    {
        $formattedCartItems = [];

        if (Auth::check()) {
            $cart = Auth::user()->cart;
            if ($cart) {
                // MODIFICACIÓN CLAVE AQUÍ (Línea 197 aproximada en tu código si esta era la original):
                // Asegura que $cartItemsCollection es una Collection, incluso si $cart->cartItems fuera null (comportamiento inusual)
                $cartItemsCollection = $cart->cartItems ?? collect(); 

                foreach ($cartItemsCollection as $cartItem) {
                    $product = $cartItem->product;
                    if ($product) {
                        $price_net = (float) $product->price;
                        $price_gross = $price_net * (1 + self::IVA_RATE);

                        $formattedCartItems[] = [
                            'id' => $cartItem->id,
                            'product_id' => $product->id,
                            'name' => $product->name,
                            'image' => $product->image_path,
                            'price_unit_net' => round($price_net, 2),
                            'price_unit_gross' => round($price_gross, 2),
                            'quantity' => $cartItem->quantity,
                            'subtotal_item_net' => round($price_net * $cartItem->quantity, 2),
                            'subtotal_item_gross' => round($price_gross * $cartItem->quantity, 2),
                        ];
                    }
                }
            }
        } else {
            $cart = Session::get('cart', []);
            foreach ($cart as $productId => $itemData) {
                $product = Product::find($productId);
                if ($product) {
                    $price_net = (float) $product->price;
                    $price_gross = $price_net * (1 + self::IVA_RATE);

                    $formattedCartItems[] = [
                        'id' => $productId,
                        'product_id' => $productId,
                        'name' => $product->name,
                        'image' => $product->image_path,
                        'price_unit_net' => round($price_net, 2),
                        'price_unit_gross' => round($price_gross, 2),
                        'quantity' => $itemData['quantity'],
                        'subtotal_item_net' => round($price_net * $itemData['quantity'], 2),
                        'subtotal_item_gross' => round($price_gross * $itemData['quantity'], 2),
                    ];
                }
            }
        }

        return $formattedCartItems;
    }

    /**
     * Calcula todos los totales del carrito.
     *
     * @param array $cartItems
     * @return array
     */
    protected function calculateCartTotals(array $cartItems)
    {
        $subtotal_net_products = 0;
        $subtotal_gross_products = 0;

        foreach ($cartItems as $item) {
            $subtotal_net_products += $item['subtotal_item_net'];
            $subtotal_gross_products += $item['subtotal_item_gross'];
        }

        $iva_products_amount = $subtotal_gross_products - ($subtotal_gross_products / (1 + self::IVA_RATE));

        $commission_percent_value = $subtotal_gross_products * self::MP_COMMISSION_PERCENTAGE;
        $iva_on_commission_percent = $commission_percent_value * self::IVA_RATE;
        $mp_fee_amount = round(($commission_percent_value + $iva_on_commission_percent) + self::MP_FIXED_FEE, 2);

        $final_total = round($subtotal_gross_products + $mp_fee_amount, 2);

        $cartCount = $this->getCartItemCount();

        return [
            'cartItems' => $cartItems,
            'subtotal_net_products' => round($subtotal_net_products, 2),
            'iva_products_amount' => round($iva_products_amount, 2),
            'subtotal_gross_products' => round($subtotal_gross_products, 2),
            'mp_fee_amount' => $mp_fee_amount,
            'final_total' => $final_total,
            'cartCount' => $cartCount
        ];
    }
}
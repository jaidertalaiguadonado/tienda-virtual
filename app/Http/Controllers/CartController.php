<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Product;
use App\Models\Cart;     // Asume que tienes un modelo Cart para los carritos de usuario
use App\Models\CartItem; // Asume que tienes un modelo CartItem para los ítems del carrito
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // Constantes para las tasas de IVA y comisión de Mercado Pago
    const IVA_RATE = 0.19; // 19% para el IVA de productos y para el IVA sobre la comisión de MP
    const MP_COMMISSION_PERCENTAGE = 0.0329; // 3.29% de la comisión base de MP
    const MP_FIXED_FEE = 952.00; // $952 COP de comisión fija de MP

    /**
     * Muestra el contenido del carrito de compras.
     */
    public function show()
    {
        $cartItems = $this->getFormattedCartItems();

        // Calcular totales
        $totals = $this->calculateCartTotals($cartItems);

        return view('cart.show', $totals); // Pasa directamente el array de totales a la vista
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
        $quantity = $request->input('quantity', 1); // Por defecto 1 si no se especifica

        // Validar la existencia del producto
        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }

        if (Auth::check()) {
            // Usuario autenticado: Guardar en la base de datos
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
            // Usuario invitado: Guardar en la sesión
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

        // Recalcular el contador del carrito para el frontend
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
        // En el frontend, `cart_item_id` debe ser el `id` del `CartItem` (para usuarios autenticados)
        // o el `product_id` (para usuarios invitados en la sesión).
        $identifier = $request->input('id'); // Renombrado de 'cart_item_id' a 'id' para consistencia
        $newQuantity = $request->input('quantity');

        // Validar la cantidad
        if (!is_numeric($newQuantity) || $newQuantity < 0) {
            return response()->json(['message' => 'Cantidad inválida.'], 400);
        }

        $productDataForUpdate = null; // Para devolver el subtotal actualizado del ítem al frontend

        if (Auth::check()) {
            $cartItem = CartItem::find($identifier); // Buscar por ID de cart_item
            if (!$cartItem || $cartItem->cart->user_id !== Auth::id()) {
                return response()->json(['message' => 'Producto no encontrado en el carrito.'], 404);
            }

            if ($newQuantity === 0) {
                $cartItem->delete();
            } else {
                $cartItem->quantity = $newQuantity;
                $cartItem->save();
            }

            // Para la respuesta JSON: obtener el producto real
            $product = $cartItem->product ?? Product::find($cartItem->product_id);
            if ($product) {
                $price_net = $product->price;
                $price_gross = $price_net * (1 + self::IVA_RATE);
                $productDataForUpdate = [
                    'id' => $identifier,
                    'quantity' => $newQuantity,
                    'subtotal_item_net' => $price_net * $newQuantity,
                    'subtotal_item_gross' => $price_gross * $newQuantity,
                ];
            }

        } else {
            $cart = Session::get('cart', []);
            if (!isset($cart[$identifier])) { // Buscar por product_id en la sesión
                return response()->json(['message' => 'Producto no encontrado en el carrito.'], 404);
            }

            if ($newQuantity === 0) {
                unset($cart[$identifier]);
            } else {
                $cart[$identifier]['quantity'] = $newQuantity;
            }
            Session::put('cart', $cart);

            // Para la respuesta JSON: obtener el producto real
            $product = Product::find($identifier);
            if ($product) {
                $price_net = $product->price;
                $price_gross = $price_net * (1 + self::IVA_RATE);
                $productDataForUpdate = [
                    'id' => $identifier,
                    'quantity' => $newQuantity,
                    'subtotal_item_net' => $price_net * $newQuantity,
                    'subtotal_item_gross' => $price_gross * $newQuantity,
                ];
            }
        }

        $cartItems = $this->getFormattedCartItems();
        $totals = $this->calculateCartTotals($cartItems);

        return response()->json(array_merge([
            'message' => 'Carrito actualizado exitosamente.',
            'item' => $productDataForUpdate, // Datos del ítem actualizado
        ], $totals)); // Incluye todos los totales recalculados
    }

    /**
     * Elimina un producto del carrito.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(Request $request)
    {
        // En el frontend, `id` debe ser el `id` del `CartItem` (para usuarios autenticados)
        // o el `product_id` (para usuarios invitados en la sesión).
        $identifier = $request->input('id');

        if (Auth::check()) {
            $cartItem = CartItem::find($identifier);
            if ($cartItem && $cartItem->cart->user_id === Auth::id()) {
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
        ], $totals)); // Incluye todos los totales recalculados
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
            return $cart ? $cart->cartItems->sum('quantity') : 0;
        } else {
            $cart = Session::get('cart', []);
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
                foreach ($cart->cartItems as $cartItem) {
                    $product = $cartItem->product;
                    if ($product) {
                        $price_net = (float) $product->price;
                        $price_gross = $price_net * (1 + self::IVA_RATE);

                        $formattedCartItems[] = [
                            'id' => $cartItem->id, // ID del CartItem para operaciones de actualización/eliminación
                            'product_id' => $product->id, // ID del Producto
                            'name' => $product->name,
                            'image' => $product->image_path, // Asegúrate de que este campo existe
                            'price_unit_net' => round($price_net, 2), // Precio unitario sin IVA
                            'price_unit_gross' => round($price_gross, 2), // Precio unitario con IVA
                            'quantity' => $cartItem->quantity,
                            'subtotal_item_net' => round($price_net * $cartItem->quantity, 2),
                            'subtotal_item_gross' => round($price_gross * $cartItem->quantity, 2), // Subtotal del ítem con IVA incluido
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
                        'id' => $productId, // Usamos product_id como identificador para la sesión
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
        $subtotal_gross_products = 0; // Este es el "Para recibir" de Mercado Pago por los productos

        foreach ($cartItems as $item) {
            $subtotal_net_products += $item['subtotal_item_net'];
            $subtotal_gross_products += $item['subtotal_item_gross'];
        }

        // IVA de los productos (desglose si subtotal_gross_products ya lo incluye)
        $iva_products_amount = $subtotal_gross_products - ($subtotal_gross_products / (1 + self::IVA_RATE));

        // ---- CÁLCULO DE LA COMISIÓN DE MERCADO PAGO ----
        // La base para la comisión es el subtotal_gross_products (lo que el vendedor espera recibir por los productos, con IVA de producto)
        $commission_percent_value = $subtotal_gross_products * self::MP_COMMISSION_PERCENTAGE;

        // IVA sobre la comisión porcentual de Mercado Pago
        $iva_on_commission_percent = $commission_percent_value * self::IVA_RATE;

        // Suma de la parte variable de la comisión (porcentaje + su IVA) y la parte fija
        $mp_fee_amount = round(($commission_percent_value + $iva_on_commission_percent) + self::MP_FIXED_FEE, 2);

        // Total Final a Pagar (lo que el cliente realmente paga)
        $final_total = round($subtotal_gross_products + $mp_fee_amount, 2);

        // Obtener el conteo de ítems para el frontend
        $cartCount = $this->getCartItemCount();

        return [
            'cartItems' => $cartItems,
            'subtotal_net_products' => round($subtotal_net_products, 2),
            'iva_products_amount' => round($iva_products_amount, 2),
            'subtotal_gross_products' => round($subtotal_gross_products, 2), // Este es el "Para recibir" de MP
            'mp_fee_amount' => $mp_fee_amount,
            'final_total' => $final_total,
            'cartCount' => $cartCount // El número total de artículos en el carrito
        ];
    }
}
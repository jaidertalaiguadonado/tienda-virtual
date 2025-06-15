<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    const IVA_RATE = 0.19; // Tasa de IVA (19%)
    const MP_COMMISSION_PERCENTAGE = 0.0329; // Comisión de Mercado Pago (3.29%)
    const MP_FIXED_FEE = 900; // Cuota fija de Mercado Pago (por ejemplo, 900 COP)

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
            // Busca o crea el carrito para el usuario
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);

            // Busca si el producto ya existe en el carrito del usuario
            $cartItem = $cart->cartItems()->where('product_id', $productId)->first();

            if ($cartItem) {
                // Si existe, actualiza la cantidad
                $cartItem->quantity += $quantity;
                $cartItem->save();
            } else {
                // Si no existe, crea un nuevo CartItem
                $cart->cartItems()->create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price_at_addition' => $product->price, // Guarda el precio del producto al momento de añadir
                ]);
            }

            // Para peticiones AJAX, devolver JSON
            if ($request->expectsJson() || $request->ajax()) {
                $cartCount = $this->getCartItemCount()->original['cartCount'];
                return response()->json([
                    'message' => 'Producto añadido al carrito.',
                    'status' => 'success',
                    'cartCount' => $cartCount
                ]);
            }
        } else {
            // Manejo del carrito para usuarios invitados (sesión)
            $sessionCart = Session::get('cart', []);
            $found = false;
            foreach ($sessionCart as $key => &$item) { // Usar & para modificar el array directamente
                if (($item['id'] ?? null) == $productId) { // Usa null coalescing para seguridad
                    $sessionCart[$key]['quantity'] += $quantity;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                // Si el producto no está en la sesión, añádelo
                $sessionCart[] = [
                    'id' => $productId, // ID del producto para sesión
                    'name' => $product->name,
                    'price' => $product->price,
                    'image_url' => $product->image_url,
                    'quantity' => $quantity
                ];
            }
            Session::put('cart', $sessionCart); // Guarda el carrito actualizado en la sesión

            // Para peticiones AJAX, devolver JSON
            if ($request->expectsJson() || $request->ajax()) {
                $cartCount = $this->getCartItemCount()->original['cartCount'];
                return response()->json([
                    'message' => 'Producto añadido al carrito.',
                    'status' => 'success',
                    'cartCount' => $cartCount
                ]);
            }
        }

        // Si no es una petición AJAX, redirigir
        return redirect()->back()->with('success', 'Producto añadido al carrito.');
    }

    /**
     * Actualiza la cantidad de un producto en el carrito.
     * El frontend debe enviar 'id' (que puede ser cart_item_id para DB o product_id para sesión).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $identifier = $request->input('id'); // ID del CartItem (para logueado) o product_id (para invitado)
        $quantity = $request->input('quantity');

        // Validar cantidad
        if (!is_numeric($quantity) || $quantity < 0) {
            return response()->json(['message' => 'Cantidad inválida.', 'status' => 'error'], 400);
        }

        // Si la cantidad es 0, procesar como eliminación
        if ($quantity == 0) {
            return $this->remove($request); // remove() ya devuelve JSON
        }

        $updatedSuccessfully = false;
        $productIdForResponse = null; // Para devolver el item actualizado en la UI

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
                    $productIdForResponse = $cartItem->product_id; // Obtener product_id para la respuesta
                } else {
                    Log::warning("Intento de actualizar CartItem con ID {$identifier} para usuario {$user->id}, pero no encontrado.");
                    return response()->json(['message' => 'Producto no encontrado en el carrito del usuario.', 'status' => 'error'], 404);
                }
            } else {
                Log::warning("Usuario {$user->id} no tiene carrito asociado al intentar actualizar.");
                return response()->json(['message' => 'Carrito no encontrado para el usuario.', 'status' => 'error'], 404);
            }
        } else { // Carrito de invitado (sesión)
            $sessionCart = Session::get('cart', []);
            $updated = false;
            foreach ($sessionCart as $key => &$item) {
                // Para invitados, $identifier es el product_id
                if (($item['id'] ?? null) == $identifier) {
                    $item['quantity'] = $quantity;
                    $updated = true;
                    $productIdForResponse = $identifier; // Usar el identifier como product_id
                    break;
                }
            }
            Session::put('cart', $sessionCart); // Guarda el carrito actualizado en la sesión

            if (!$updated) {
                Log::warning("Intento de actualizar producto con product_id {$identifier} en sesión, pero no encontrado.");
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

        // Buscar el item que se acaba de actualizar en la lista formateada para devolverlo
        $updatedFormattedItem = null;
        if ($productIdForResponse) {
            foreach ($formattedCartItems as $item) {
                if (($item['product_id'] ?? null) == $productIdForResponse) {
                    $updatedFormattedItem = $item;
                    break;
                }
            }
        }

        return response()->json(array_merge($cartTotals, [
            'message' => 'Cantidad actualizada.',
            'status' => 'success',
            'item' => $updatedFormattedItem // Devolver el item actualizado para reflejar cambios en la UI
        ]));
    }

    /**
     * Elimina un producto del carrito.
     * El frontend debe enviar 'id' (que puede ser cart_item_id para DB o product_id para sesión).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(Request $request)
    {
        $identifier = $request->input('id'); // ID del CartItem (para logueado) o product_id (para invitado)

        $deleted = false;
        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart;
            if ($cart) {
                // Para usuarios logueados, $identifier es el ID del CartItem
                $deleted = $cart->cartItems()->where('id', $identifier)->delete();
                if (!$deleted) {
                    Log::warning("Intento de eliminar CartItem con ID {$identifier} para usuario {$user->id}, pero no encontrado.");
                }
            } else {
                Log::warning("Usuario {$user->id} no tiene carrito asociado al intentar eliminar.");
            }
        } else { // Carrito de invitado (sesión)
            $sessionCart = Session::get('cart', []);
            $initialCount = count($sessionCart);
            $sessionCart = array_filter($sessionCart, function (array $item) use ($identifier) {
                // Para invitados, $identifier es el product_id
                return ($item['id'] ?? null) != $identifier;
            });
            // Reindexar el array para evitar problemas con las claves después de filtrar
            $sessionCart = array_values($sessionCart);
            Session::put('cart', $sessionCart);
            $deleted = (count($sessionCart) < $initialCount); // Si el conteo disminuyó, algo se eliminó
            if (!$deleted) {
                    Log::warning("Intento de eliminar producto con product_id {$identifier} en sesión, pero no encontrado.");
            }
        }

        if (!$deleted) {
            return response()->json(['message' => 'Producto no encontrado en el carrito para eliminar.', 'status' => 'error'], 404);
        }

        // Recalcular y devolver los nuevos totales
        $formattedCartItems = $this->getFormattedCartItems();
        $cartTotals = $this->calculateCartTotals($formattedCartItems);

        return response()->json(array_merge($cartTotals, [
            'message' => 'Producto eliminado del carrito.',
            'status' => 'success',
            'cartCount' => $cartTotals['cartCount'] // Asegúrate de que esto sea el conteo total de unidades
        ]));
    }

    /**
     * Obtiene el conteo total de unidades en el carrito (para la API).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCartItemCount()
    {
        $count = 0;
        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart()->first(); // Cargar el carrito del usuario
            // Suma la cantidad de todos los ítems en el carrito del usuario
            $count = $cart ? ($cart->cartItems ?? collect())->sum('quantity') : 0;
        } else {
            $sessionCart = Session::get('cart', []);
            // Asegúrate de que $sessionCart sea un array
            if (!is_array($sessionCart)) {
                $sessionCart = [];
            }
            // Suma la cantidad de todos los ítems en el carrito de la sesión
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
            $cart = $user->cart()->with('cartItems.product')->first(); // Carga las relaciones product
            if ($cart) {
                // Asegura que cartItems sea una colección, incluso si la relación es null
                $cartItemsCollection = $cart->cartItems ?? collect();
                foreach ($cartItemsCollection as $cartItem) {
                    if ($cartItem->product) {
                        $priceNet = (float) $cartItem->product->price;
                        $subtotalNet = $cartItem->quantity * $priceNet;
                        $subtotalGross = round($subtotalNet * (1 + self::IVA_RATE), 2);

                        $formattedItems[] = [
                            'id' => $cartItem->id, // ID del CartItem (para actualizar/eliminar en DB)
                            'product_id' => $cartItem->product_id, // ID del Producto (para referencias/visualización)
                            'name' => $cartItem->product->name,
                            'quantity' => $cartItem->quantity,
                            'price_net' => $priceNet,
                            'subtotal_item_net' => $subtotalNet,
                            'subtotal_item_gross' => $subtotalGross,
                            'image_url' => $cartItem->product->image_url ?? asset('images/default_product.png'), // Fallback de imagen
                        ];
                    } else {
                        // Loguear y eliminar ítems huérfanos (si el producto asociado ya no existe)
                        Log::warning("CartItem {$cartItem->id} (product_id: {$cartItem->product_id}) tiene un producto no encontrado. Eliminando.");
                        $cartItem->delete();
                    }
                }
            }
        } else {
            $sessionCart = Session::get('cart', []);
            $validSessionCart = []; // Para reconstruir un carrito de sesión válido
            foreach ($sessionCart as $item) {
                $product = Product::find($item['id'] ?? null); // 'id' en sesión es el product_id
                if ($product) {
                    $priceNet = (float) $product->price;
                    $subtotalNet = ($item['quantity'] ?? 0) * $priceNet;
                    $subtotalGross = round($subtotalNet * (1 + self::IVA_RATE), 2);

                    $validSessionCart[] = [
                        'id' => $item['id'], // Para la UI/JS, sigue siendo el product_id en sesión
                        'product_id' => $item['id'], // También product_id para consistencia
                        'name' => $item['name'] ?? $product->name, // Usa el nombre del producto si no está en sesión
                        'quantity' => $item['quantity'] ?? 0, // Fallback por si 'quantity' no está
                        'price_net' => $priceNet,
                        'subtotal_item_net' => $subtotalNet,
                        'subtotal_item_gross' => $subtotalGross,
                        'image_url' => $product->image_url ?? asset('images/default_product.png'), // Fallback de imagen
                    ];
                } else {
                    Log::warning("Producto con ID {$item['id']} en sesión no encontrado. Se omitirá del carrito.");
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
        $cartCount = 0; // Para el conteo total de unidades

        foreach ($cartItems as $item) {
            $subtotal_net_products += $item['subtotal_item_net'] ?? 0;
            $subtotal_gross_products += $item['subtotal_item_gross'] ?? 0;
            $cartCount += $item['quantity'] ?? 0;
        }

        // Cálculo del IVA de los productos
        // Se calcula sobre el subtotal bruto para obtener la porción de IVA
        $iva_products_amount = round($subtotal_gross_products - ($subtotal_gross_products / (1 + self::IVA_RATE)), 2);

        // Cálculo de la comisión de Mercado Pago
        $commission_percent_value = $subtotal_gross_products * self::MP_COMMISSION_PERCENTAGE;
        $iva_on_commission_percent = $commission_percent_value * self::IVA_RATE; // IVA sobre la comisión porcentual
        $mp_fee_amount = round(($commission_percent_value + $iva_on_commission_percent) + self::MP_FIXED_FEE, 2);

        // Cálculo del total final
        $final_total = round($subtotal_gross_products + $mp_fee_amount, 2);

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

    /**
     * Método de depuración: Limpia el carrito y añade un producto de prueba.
     * Luego, loguea los totales calculados.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function testCartPopulateAndCalculate()
    {
        Log::info('--- INICIANDO TEST DE CÁLCULO DE CARRITO ---');

        // 1. Limpiar el carrito existente
        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart;
            if ($cart) {
                $cart->cartItems()->delete();
                Log::info('Carrito de usuario ' . $user->id . ' limpiado para la prueba.');
            } else {
                // Si no hay carrito, se creará uno al añadir el producto
                Log::info('No se encontró carrito para el usuario ' . $user->id . ', se creará uno.');
            }
        } else {
            Session::forget('cart');
            Log::info('Carrito de sesión limpiado para la prueba.');
        }

        // 2. Crear o usar un producto de prueba
        $testProductId = 9999; // Un ID que es poco probable que exista en producción
        $testProductName = 'Producto de Prueba Debug';
        $testProductPrice = 50000; // Precio neto fijo (50.000 COP)
        $testProductQuantity = 2; // Cantidad fija

        // Intenta encontrar el producto real, si no existe, crea un objeto Product mock
        $product = Product::find($testProductId);

        if (!$product) {
            // Crea un objeto Product "mock" solo para esta prueba, sin guardarlo en DB
            $product = new Product([
                'id' => $testProductId,
                'name' => $testProductName,
                'price' => $testProductPrice,
                'image_url' => asset('images/default_product.png'),
                // Asegúrate de incluir cualquier otra propiedad que tu `CartController` espere
            ]);
            Log::warning('Producto con ID ' . $testProductId . ' no encontrado en la DB. Usando un producto mock para la prueba.');
        } else {
            // Si el producto existe, ajusta su precio al valor de prueba para asegurar consistencia
            $product->price = $testProductPrice;
            Log::info('Usando producto existente con ID ' . $product->id . ' para la prueba, precio ajustado a ' . $testProductPrice);
        }

        // 3. Añadir el producto de prueba al carrito (usando la lógica de add())
        $request = Request::create('/cart/add', 'POST', [
            'product_id' => $product->id,
            'quantity' => $testProductQuantity,
        ]);

        // Asegurarse de que el request se interprete como AJAX para obtener JSON de respuesta de add()
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        // Llama directamente al método add para simular la adición
        $this->add($request);
        Log::info('Producto de prueba añadido al carrito: ' . $product->name . ' x ' . $testProductQuantity);

        // 4. Obtener ítems formateados y calcular totales
        $cartItems = $this->getFormattedCartItems();
        $totals = $this->calculateCartTotals($cartItems);

        // 5. Loguear los resultados detalladamente
        Log::info('--- RESULTADOS DEL TEST DE CÁLCULO DE CARRITO ---');
        Log::info('Ítems formateados para el carrito:', $cartItems);
        Log::info('Totales del carrito calculados:', $totals);

        Log::info('Verificaciones Clave:');
        Log::info('Subtotal NETO de productos esperados: ' . ($testProductPrice * $testProductQuantity));
        Log::info('Subtotal BRUTO de productos esperados (IVA inc.): ' . round(($testProductPrice * $testProductQuantity) * (1 + self::IVA_RATE), 2));
        Log::info('Comisión MP esperada: ' . round((($testProductPrice * $testProductQuantity) * (1 + self::IVA_RATE)) * self::MP_COMMISSION_PERCENTAGE * (1 + self::IVA_RATE) + self::MP_FIXED_FEE, 2));
        Log::info('Total FINAL esperado: ' . round((($testProductPrice * $testProductQuantity) * (1 + self::IVA_RATE)) + round((($testProductPrice * $testProductQuantity) * (1 + self::IVA_RATE)) * self::MP_COMMISSION_PERCENTAGE * (1 + self::IVA_RATE) + self::MP_FIXED_FEE, 2), 2));
        Log::info('--- FIN DEL TEST DE CÁLCULO DE CARRITO ---');

        // Redirigir al usuario a la vista del carrito para que pueda ver los cambios
        return redirect()->route('cart.show')->with('success', 'Carrito poblado con datos de prueba. Revisa los logs para los cálculos.');
    }
}

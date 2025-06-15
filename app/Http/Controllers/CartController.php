<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    // Cambiamos a constante pública para poder accederla desde la vista
    public const IVA_RATE = 0.19; // 19% de IVA para Colombia
    private $mpFeeRate = 0.0349; // 3.49% de comisión de Mercado Pago
    private $mpFixedFee = 900; // 900 COP de costo fijo por transacción de Mercado Pago

    /**
     * Constructor para inicializar el SDK de Mercado Pago.
     * Inyecta el CartController.
     */
    public function __construct()
    {
        // El constructor no necesita inyectar CartController si solo se usa $this para los métodos.
        // Si este constructor necesita CartController, habría un problema de recursividad.
        // Asegúrate de que el constructor de MercadoPagoController si inyecta CartController lo haga correctamente.
    }

    /**
     * Añade un producto al carrito.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addToCart(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);

        if ($quantity <= 0) {
            return back()->with('error', 'La cantidad debe ser al menos 1.');
        }

        $product = Product::find($productId);

        if (!$product) {
            return back()->with('error', 'Producto no encontrado.');
        }

        if (Auth::check()) {
            // Usuario autenticado: guardar en la base de datos
            $user = Auth::user();
            $cart = $user->cart()->firstOrCreate([]); // Obtener o crear carrito del usuario

            $cartItem = $cart->cartItems()->where('product_id', $productId)->first();

            if ($cartItem) {
                // Si el producto ya está en el carrito, actualizar la cantidad
                $cartItem->quantity += $quantity;
                $cartItem->save();
            } else {
                // Si es un nuevo producto, crearlo
                $cart->cartItems()->create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                ]);
            }
        } else {
            // Usuario no autenticado (invitado): guardar en la sesión
            $cart = Session::get('cart', []);

            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] += $quantity;
            } else {
                $cart[$productId] = [
                    'id' => $productId,
                    'name' => $product->name,
                    'price' => $product->price, // Almacenar precio neto del producto
                    'image_url' => $product->image_url, // Asegúrate de guardar la image_url aquí también
                    'quantity' => $quantity,
                ];
            }
            Session::put('cart', $cart);
        }

        return back()->with('success', 'Producto añadido al carrito.');
    }

    /**
     * Muestra el contenido del carrito.
     *
     * @return \Illuminate\View\View
     */
    public function showCart()
    {
        $cartItems = $this->getFormattedCartItems();
        $totals = $this->calculateCartTotals($cartItems);
        
        // ¡NUEVO! Calculamos cartCount aquí
        $cartCount = $cartItems->sum('quantity');

        // Pasamos cartCount a la vista
        return view('cart.show', array_merge(compact('cartItems', 'cartCount'), $totals));
    }

    /**
     * Actualiza la cantidad de un producto en el carrito.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCart(Request $request)
    {
        $productId = $request->input('product_id'); // Usamos product_id como en el JS
        $quantity = $request->input('quantity');

        if (!is_numeric($quantity) || $quantity < 0) {
            return response()->json(['message' => 'Cantidad inválida.'], 400);
        }

        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart()->first();
            if ($cart) {
                $cartItem = $cart->cartItems()->where('product_id', $productId)->first();
                if ($cartItem) {
                    if ($quantity == 0) {
                        $cartItem->delete();
                        $itemRemoved = true;
                    } else {
                        $cartItem->quantity = $quantity;
                        $cartItem->save();
                        $itemRemoved = false;
                    }
                } else {
                    return response()->json(['message' => 'Producto no encontrado en el carrito.'], 404);
                }
            } else {
                return response()->json(['message' => 'Carrito no encontrado para el usuario.'], 404);
            }
        } else {
            $sessionCart = Session::get('cart', []);
            if (isset($sessionCart[$productId])) {
                if ($quantity == 0) {
                    unset($sessionCart[$productId]);
                    $itemRemoved = true;
                } else {
                    $sessionCart[$productId]['quantity'] = $quantity;
                    $itemRemoved = false;
                }
                Session::put('cart', $sessionCart);
            } else {
                return response()->json(['message' => 'Producto no encontrado en el carrito de sesión.'], 404);
            }
        }

        // Después de la actualización/eliminación, recalculamos y devolvemos los nuevos totales
        $cartItems = $this->getFormattedCartItems();
        $totals = $this->calculateCartTotals($cartItems);
        $cartCount = $cartItems->sum('quantity'); // Obtener la cuenta total de items

        return response()->json(array_merge($totals, ['cartCount' => $cartCount, 'item_removed' => $itemRemoved]));
    }

    /**
     * Elimina un producto del carrito.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFromCart(Request $request)
    {
        $productId = $request->input('product_id'); // Usamos product_id como en el JS

        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart()->first();
            if ($cart) {
                $cart->cartItems()->where('product_id', $productId)->delete();
            }
        } else {
            $cart = Session::get('cart', []);
            if (isset($cart[$productId])) {
                unset($cart[$productId]);
                Session::put('cart', $cart);
            }
        }
        
        // Recalculamos y devolvemos los nuevos totales
        $cartItems = $this->getFormattedCartItems();
        $totals = $this->calculateCartTotals($cartItems);
        $cartCount = $cartItems->sum('quantity'); // Obtener la cuenta total de items

        return response()->json(array_merge($totals, ['cartCount' => $cartCount, 'item_removed' => true]));
    }

    /**
     * Vacía completamente el carrito.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearCart()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart()->first();
            if ($cart) {
                $cart->cartItems()->delete();
            }
        } else {
            Session::forget('cart');
        }
        return back()->with('success', 'El carrito ha sido vaciado.');
    }

    /**
     * Obtiene el conteo total de ítems en el carrito.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCartItemCount()
    {
        $cartItems = $this->getFormattedCartItems();
        $cartCount = $cartItems->sum('quantity');
        return response()->json(['cartCount' => $cartCount]);
    }


    /**
     * Obtiene los ítems del carrito formateados con precios brutos.
     * Utilizado tanto para mostrar el carrito como para calcular los totales.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFormattedCartItems()
    {
        $rawCartItems = collect();

        if (Auth::check()) {
            $user = Auth::user();
            // Aseguramos que 'product' se cargue para acceder a sus propiedades
            $cart = $user->cart()->with('cartItems.product')->first();
            if ($cart) {
                $rawCartItems = $cart->cartItems->filter(fn($item) => $item->product);
            }
        } else {
            $sessionCart = Session::get('cart', []);
            foreach ($sessionCart as $itemData) {
                $product = Product::find($itemData['id']);
                if ($product) {
                    $rawCartItems->push((object)[ // Convertir a objeto para consistencia
                        'product_id' => $product->id,
                        'product' => $product, // Este es el modelo Product Eloquent
                        'quantity' => $itemData['quantity'],
                    ]);
                }
            }
        }

        // Formatear ítems para la vista y cálculos, incluyendo el precio bruto
        $formattedItems = $rawCartItems->map(function($item) {
            //dd($item->product); // <--- DEJAMOS ESTE DD() AQUÍ PARA LA PRÓXIMA DEPURACIÓN
                                 // Necesitamos ver la estructura del objeto Product
                                 // para saber por qué no tiene 'image_url'

            $productPriceNet = $item->product->price;
            $productPriceGross = $productPriceNet * (1 + self::IVA_RATE); // Usamos self::IVA_RATE

            // Usar sprintf para asegurar 2 decimales y luego castear a float
            $unitPriceDisplay = (float) sprintf("%.2f", $productPriceGross);
            $subtotalDisplay = (float) sprintf("%.2f", $productPriceGross * $item->quantity);

            return (object)[
                'id'          => $item->product_id, // ID del producto
                'name'        => $item->product->name,
                'price_net'   => (float) sprintf("%.2f", $productPriceNet),
                'price_gross' => $unitPriceDisplay, // Precio unitario con IVA para mostrar
                // ACCESO CLAVE: ASUMIMOS que $item->product es un modelo Product con una columna 'image_url'
                'image_url'   => $item->product->image_url ?? asset('images/default_product.png'),
                'quantity'    => $item->quantity,
                'subtotal_item_gross' => $subtotalDisplay, // Nombre ajustado para coincidir con la vista
            ];
        });

        return $formattedItems;
    }

    /**
     * Calcula los totales del carrito incluyendo IVA y comisión de Mercado Pago.
     *
     * @param \Illuminate\Support\Collection $cartItems Ítems del carrito ya formateados con precios brutos.
     * @return array
     */
    public function calculateCartTotals($cartItems)
    {
        $subtotalNetProducts = 0;
        $subtotalGrossProducts = 0;

        foreach ($cartItems as $item) {
            $subtotalNetProducts += $item->price_net * $item->quantity;
            $subtotalGrossProducts += $item->price_gross * $item->quantity;
        }

        // Asegurar que los subtotales también tengan 2 decimales de precisión
        $subtotalNetProducts = (float) sprintf("%.2f", $subtotalNetProducts);
        $subtotalGrossProducts = (float) sprintf("%.2f", $subtotalGrossProducts);

        $ivaProductsAmount = (float) sprintf("%.2f", $subtotalGrossProducts - $subtotalNetProducts);

        // Cálculo de la comisión de Mercado Pago sobre el subtotal bruto de los productos
        $mpFeeAmountRaw = ($subtotalGrossProducts * $this->mpFeeRate) + $this->mpFixedFee;
        $mpFeeAmount = (float) sprintf("%.2f", $mpFeeAmountRaw);

        $finalTotal = (float) sprintf("%.2f", $subtotalGrossProducts + $mpFeeAmount);

        // --- Logging para depuración en el CartController ---
        \Log::info('Calculando totales en CartController:', [
            'subtotal_net_products'       => $subtotalNetProducts,
            'iva_products_amount'         => $ivaProductsAmount,
            'subtotal_gross_products'     => $subtotalGrossProducts,
            'comision_mp_bruta_calculada' => $mpFeeAmountRaw, // Valor antes de formatear
            'comision_mp_final'           => $mpFeeAmount, // Valor formateado a 2 decimales
            'total_final_calculado'       => $finalTotal,
            'mp_fee_rate'                 => $this->mpFeeRate,
            'mp_fixed_fee'                => $this->mpFixedFee,
        ]);
        // --- Fin de Logging ---

        return [
            'subtotal_net_products'   => $subtotalNetProducts,
            'iva_products_amount'     => $ivaProductsAmount,
            'subtotal_gross_products' => $subtotalGrossProducts,
            'mp_fee_amount'           => $mpFeeAmount,
            'final_total'             => $finalTotal,
        ];
    }
}
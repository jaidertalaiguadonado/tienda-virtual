<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class MercadoPagoController extends Controller
{
    protected $cartController;
    private $ivaRate = 0.19; // Tasa de IVA del 19%

    /**
     * Constructor para inicializar el SDK de Mercado Pago.
     * Inyecta el CartController.
     */
    public function __construct(CartController $cartController)
    {
        $this->cartController = $cartController;

        $accessToken = config('services.mercadopago.access_token');

        if (empty($accessToken)) {
            \Log::critical('Mercado Pago Access Token no configurado o es nulo. Verifique su .env y config/services.php');
            // Opcional: Si quieres que la aplicación se detenga si el token es nulo, descomenta la línea de abajo.
            // dd('ERROR CRÍTICO: Mercado Pago Access Token no configurado. Verifique logs.');
        } else {
            MercadoPagoConfig::setAccessToken($accessToken);
        }
    }

    /**
     * Crea una preferencia de pago en Mercado Pago.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createPaymentPreference(Request $request)
    {
        $cart = null;
        // Colección para los ítems formateados específicamente para el payload de Mercado Pago
        $mpItems = collect(); 

        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart()->with('cartItems.product')->first();
            if (!$cart || $cart->cartItems->isEmpty()) {
                return back()->with('error', 'Tu carrito no existe o está vacío.');
            }

            // Construir los ítems para Mercado Pago (con precios brutos aplicando IVA)
            $mpItems = $cart->cartItems->filter(fn($item) => $item->product)->map(function($item) {
                $productPriceNet = $item->product->price; // Precio NETO de la DB
                $productPriceGross = $productPriceNet * (1 + $this->ivaRate);
                
                // Formatear el precio unitario a una cadena con 2 decimales para precisión en MP
                $unitPriceFormatted = sprintf("%.2f", $productPriceGross);

                return [
                    'id'          => $item->product_id,
                    'title'       => $item->product->name,
                    'description' => Str::limit($item->product->description ?? $item->product->name, 250),
                    'quantity'    => $item->quantity,
                    'unit_price'  => (float) $unitPriceFormatted, // Castear a float para el SDK de MP
                    'currency_id' => "COP",
                    'picture_url' => $item->product->image_url ?? asset('images/default_product.png'),
                ];
            });

        } else {
            // Lógica para carrito de sesión (guest)
            $sessionCart = Session::get('cart', []);
            if (empty($sessionCart)) {
                return back()->with('error', 'Tu carrito está vacío. No se puede proceder con el pago.');
            }

            $mpItems = collect($sessionCart)->map(function($item) {
                $product = Product::find($item['id']);
                // Asumiendo que el precio del producto en la sesión o DB es NETO
                $priceNet = $product ? $product->price : ($item['price'] ?? 0); 
                $priceGross = $priceNet * (1 + $this->ivaRate);

                // Formatear el precio unitario a una cadena con 2 decimales para precisión en MP
                $unitPriceFormatted = sprintf("%.2f", $priceGross);

                return [
                    'id'          => $item['id'],
                    'title'       => $item['name'] ?? 'Producto Desconocido',
                    'description' => Str::limit($product->description ?? $item['name'] ?? 'Producto', 250),
                    'quantity'    => $item['quantity'],
                    'unit_price'  => (float) $unitPriceFormatted, // Castear a float para el SDK de MP
                    'currency_id' => "COP",
                    'picture_url' => $item['image'] ?? asset('images/default_product.png'),
                ];
            });
        }

        if ($mpItems->isEmpty()) {
            return back()->with('error', 'Tu carrito está vacío después de procesar. No se puede proceder con el pago.');
        }

        // Obtener los ítems formateados del CartController para un cálculo de totales consistente.
        $actualCartItemsForTotals = $this->cartController->getFormattedCartItems();
        $totals = $this->cartController->calculateCartTotals($actualCartItemsForTotals);

        // Añadir la comisión de Mercado Pago como un ítem adicional si es mayor que cero
        if ($totals['mp_fee_amount'] > 0) {
            // Formatear la comisión a una cadena con 2 decimales para precisión en MP
            $mpFeeFormatted = sprintf("%.2f", $totals['mp_fee_amount']);
            $mpItems->push([
                'id'          => 'MP_FEE',
                'title'       => 'Comisión de Mercado Pago',
                'description' => 'Costo por servicio de procesamiento de pago',
                'quantity'    => 1,
                'unit_price'  => (float) $mpFeeFormatted, // Castear a float para el SDK de MP
                'currency_id' => "COP",
            ]);
        }

        // Formatear el monto total final a pagar a una cadena con 2 decimales para precisión en MP
        $final_total_to_pay = (float) sprintf("%.2f", $totals['final_total']);

        // --- Logging para depuración y monitoreo (mantener en producción) ---
        \Log::info('Detalles de totales antes de enviar a Mercado Pago (FINAL):', [
            'subtotal_net_productos'             => $totals['subtotal_net_products'],
            'iva_productos_calculado'            => $totals['iva_products_amount'],
            'subtotal_gross_productos'           => $totals['subtotal_gross_products'],
            'comision_mp_total'                  => $totals['mp_fee_amount'],
            'total_final_a_pagar_cartcontroller' => $totals['final_total'], // Valor original del cálculo del carrito
            'total_final_a_pagar_formateado_enviado_a_mp' => $final_total_to_pay, // Valor final que se enviará
            'items_enviados_a_mp'                => $mpItems->toArray(),
            'total_sum_of_mp_items_internal'     => $mpItems->sum(function($item){ return $item['unit_price'] * $item['quantity']; }),
            'access_token_prefix'                => substr(MercadoPagoConfig::getAccessToken(), 0, 7) . '...', // Para verificar que el token se carga
        ]);
        // --- Fin de Logging ---

        // Asegurarse de que el monto sea válido antes de intentar crear la preferencia
        if ($final_total_to_pay <= 0) {
            \Log::error('Intento de crear preferencia con monto total <= 0: ' . $final_total_to_pay);
            return back()->with('error', 'El monto total a pagar debe ser positivo.');
        }

        $preferenceClient = new PreferenceClient();
        try {
            $preferenceData = [
                "items"            => $mpItems->toArray(),
                "back_urls"        => [
                    "success" => route('mercadopago.success'),
                    "failure" => route('mercadopago.failure'),
                    "pending" => route('mercadopago.pending')
                ],
                "auto_return"      => "approved",
                // La notification_url es vital para que Mercado Pago notifique a tu app sobre el estado del pago.
                "notification_url" => route('mercadopago.webhook') . '?source_news=webhooks',
                "external_reference" => 'ORDER-' . uniqid(), // Referencia única para tu sistema
                "statement_descriptor" => "TIENDAJD", // Lo que aparecerá en el resumen de la tarjeta del comprador
                "payer" => [
                    "email" => Auth::check() ? Auth::user()->email : 'invitado@ejemplo.com',
                    // Puedes añadir más datos del pagador si los tienes (ej. name, surname, phone, address)
                ],
                "transaction_amount" => $final_total_to_pay, // El monto total exacto
                // Opcional: configurar métodos de pago excluidos, cuotas, etc.
                // "payment_methods" => [
                //     "excluded_payment_types" => [ ["id" => "ticket"] ],
                //     "installments" => 6
                // ]
            ];

            \Log::info('Mercado Pago Preference Payload (Final enviado):', $preferenceData);

            $response = $preferenceClient->create($preferenceData);

            // Redirige al usuario al punto de inicio de pago de Mercado Pago
            return redirect()->away($response->init_point);

        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            // Manejo específico de errores de la API de Mercado Pago
            $apiResponse = $e->getApiResponse();
            $errorMessage = $e->getMessage();
            $errorDetails = [];

            if ($apiResponse && property_exists($apiResponse, 'error') && $apiResponse->error) {
                $errorMessage = $apiResponse->error->message ?? $errorMessage;
                $errorDetails = $apiResponse->error->cause ?? [];
            }

            \Log::error('Error de API de Mercado Pago al crear preferencia: ' . $errorMessage, [
                'details'          => $errorDetails,
                'exception_code'   => $e->getCode(),
                'api_response_raw' => json_encode($apiResponse), // Detalles crudos de la respuesta de la API
                'request_payload'  => $preferenceData, // Incluir el payload que se intentó enviar
            ]);

            $userMessage = 'Error al crear la preferencia de pago. Por favor, revisa los datos ingresados.';
            if (!empty($errorDetails) && is_array($errorDetails)) {
                $firstErrorCause = current($errorDetails);
                if (is_object($firstErrorCause) && property_exists($firstErrorCause, 'description')) {
                    $userMessage = 'Error al crear la preferencia de pago: ' . $firstErrorCause->description;
                } elseif (is_string($firstErrorCause)) {
                    $userMessage = 'Error al crear la preferencia de pago: ' . $firstErrorCause;
                }
            } elseif ($errorMessage !== $e->getMessage()) {
                $userMessage = 'Error al crear la preferencia de pago: ' . $errorMessage;
            }

            return back()->with('error', $userMessage . ' Código de error: ' . $e->getCode());

        } catch (\Exception $e) {
            // Manejo de cualquier otra excepción inesperada
            \Log::error('Error general al crear preferencia de pago: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Error interno al procesar el pago. Por favor, intenta de nuevo.');
        }
    }

    /**
     * Maneja el callback de éxito de Mercado Pago.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function paymentSuccess(Request $request)
    {
        $paymentId = $request->query('payment_id');
        $externalReference = $request->query('external_reference');
        \Log::info('Mercado Pago Success Callback:', $request->all());
        return view('mercadopago.success', compact('paymentId', 'externalReference'))->with('message', '¡Gracias por tu compra! Tu pago ha sido recibido y está siendo procesado.');
    }

    /**
     * Maneja el callback de fallo de Mercado Pago.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function paymentFailure(Request $request)
    {
        $paymentId = $request->query('payment_id');
        $externalReference = $request->query('external_reference');
        \Log::info('Mercado Pago Failure Callback:', $request->all());
        return view('mercadopago.failure', compact('paymentId', 'externalReference'))->with('message', 'Lo sentimos, tu pago no pudo ser procesado. Por favor, inténtalo de nuevo.');
    }

    /**
     * Maneja el callback de pago pendiente de Mercado Pago.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function paymentPending(Request $request)
    {
        $paymentId = $request->query('payment_id');
        $externalReference = $request->query('external_reference');
        \Log::info('Mercado Pago Pending Callback:', $request->all());
        return view('mercadopago.pending', compact('paymentId', 'externalReference'))->with('message', 'Tu pago está pendiente de confirmación. Te notificaremos cuando se apruebe.');
    }

    /**
     * Maneja las notificaciones de webhook de Mercado Pago.
     * Aquí se debería actualizar el estado de la orden en tu base de datos.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request)
    {
        $topic = $request->input('topic'); // 'payment', 'merchant_order', etc.
        $id = $request->input('id'); // ID del recurso (ej. ID del pago)

        \Log::info('Webhook de Mercado Pago recibido:', $request->all());

        if ($topic === 'payment' && !empty($id)) {
            try {
                $paymentClient = new PaymentClient();
                $payment = $paymentClient->get($id); // Obtener detalles completos del pago

                if ($payment) {
                    $paymentId = $payment->id;
                    $paymentStatus = $payment->status; // 'approved', 'pending', 'rejected', etc.
                    $externalReference = $payment->external_reference; // La referencia de tu orden

                    \Log::info("Webhook: Procesando pago MP ID: {$paymentId}, Estado: {$paymentStatus}, Ref Externa: {$externalReference}");

                    // === Lógica para actualizar tu base de datos aquí ===
                    // Buscar la orden por externalReference y actualizar su estado
                    // (ej. de 'pendiente' a 'aprobado' o 'rechazado')
                    // ...

                    // Opcional: Procesar el pago si está aprobado, enviar emails, etc.
                    if ($paymentStatus === 'approved') {
                        \Log::info("Webhook: Pago {$paymentId} APROBADO para orden {$externalReference}.");
                        // Aquí deberías realizar acciones como:
                        // 1. Marcar la orden como pagada en tu DB.
                        // 2. Reducir el stock de productos.
                        // 3. Enviar email de confirmación al cliente.
                    } elseif ($paymentStatus === 'rejected') {
                        \Log::warning("Webhook: Pago {$paymentId} RECHAZADO para orden {$externalReference}.");
                        // Marcar orden como fallida, notificar al cliente.
                    } elseif ($paymentStatus === 'pending') {
                        \Log::info("Webhook: Pago {$paymentId} PENDIENTE para orden {$externalReference}.");
                        // Marcar orden como pendiente.
                    }
                    // ===================================================

                } else {
                    \Log::warning("Webhook: No se pudo obtener el objeto de pago para ID: {$id}");
                }

            } catch (\MercadoPago\Exceptions\MPApiException $e) {
                $apiResponse = $e->getApiResponse();
                $errorMessage = $e->getMessage();
                $errorDetails = [];

                if ($apiResponse && property_exists($apiResponse, 'error') && $apiResponse->error) {
                    $errorMessage = $apiResponse->error->message ?? $errorMessage;
                    $errorDetails = $apiResponse->error->cause ?? [];
                }
                \Log::error('Error de API en Webhook al obtener pago: ' . $errorMessage, [
                    'details'          => $errorDetails,
                    'exception_code'   => $e->getCode(),
                    'api_response_raw' => json_encode($apiResponse)
                ]);
            } catch (\Exception $e) {
                \Log::error('Error general en Webhook al procesar: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            }
        }
        // Siempre devolver un 200 OK a Mercado Pago para indicar que el webhook fue recibido.
        return response()->json(['status' => 'ok'], 200);
    }
}
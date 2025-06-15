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
    private $ivaRate = 0.19;

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
        } else {
            MercadoPagoConfig::setAccessToken($accessToken);
            \Log::info('Mercado Pago Access Token cargado. Longitud: ' . strlen($accessToken) . ' caracteres.');
        }
    }

    /**
     * Crea una preferencia de pago en Mercado Pago.
     *
     * @param Request $request
     * @param bool $debugMode Permite activar el modo de depuración para probar con un producto fijo.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createPaymentPreference(Request $request, $debugMode = false)
    {
        $mpItems = collect(); // Ítems formateados específicamente para el payload de Mercado Pago
        $payerEmail = Auth::check() ? Auth::user()->email : 'invitado@ejemplo.com';

        // --- MODO DE DEPURACIÓN ACTIVO ---
        if ($debugMode) {
            \Log::info('*** MODO DE DEPURACIÓN DE MERCADO PAGO ACTIVO ***');

            // Definimos un producto de prueba fijo con precio positivo
            $mpItems->push([
                'id' => 'DEBUG-PROD-001',
                'title' => 'Producto de Depuración (10.000 COP)',
                'description' => 'Producto de prueba para verificar la comunicación con Mercado Pago. Precio incluye IVA.',
                'quantity' => 1,
                'unit_price' => 10000.00, // Precio fijo en COP, positivo
                'currency_id' => "COP",
                'picture_url' => asset('images/default_product.png'),
            ]);

            // Email fijo para el pagador en modo depuración
            $payerEmail = 'debug_user_123@example.com';
            
            \Log::info('MP Debug Items:', $mpItems->toArray());
            \Log::info('MP Debug Payer Email:', ['email' => $payerEmail]);

        } else {
            // --- LÓGICA NORMAL DEL CARRITO (si no es modo depuración) ---
            $cart = null;

            if (Auth::check()) {
                $user = Auth::user();
                $cart = $user->cart()->with('cartItems.product')->first();
                if (!$cart) {
                    return back()->with('error', 'Tu carrito no existe o está vacío.');
                }

                // Construir los ítems para Mercado Pago (con precios brutos)
                $mpItems = ($cart->cartItems ?? collect())->filter(fn($item) => $item->product)->map(function($item) {
                    $productPriceNet = (float) $item->product->price; // Precio NETO de la DB
                    $productPriceGross = round($productPriceNet * (1 + $this->ivaRate), 2);

                    if ($productPriceGross <= 0 || $item->quantity <= 0) {
                        \Log::error('Producto con precio o cantidad inválida detectado en el carrito de usuario.', [
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name,
                            'unit_price_gross' => $productPriceGross,
                            'quantity' => $item->quantity
                        ]);
                        return null; // Retorna null para que sea filtrado
                    }

                    return [
                        'id' => (string) $item->product_id,
                        'title' => $item->product->name,
                        'description' => Str::limit($item->product->description ?? $item->product->name, 250),
                        'quantity' => (int) $item->quantity,
                        'unit_price' => (float) $productPriceGross,
                        'currency_id' => "COP",
                        'picture_url' => $item->product->image_url ?? asset('images/default_product.png'),
                    ];
                })->filter()->values();
            } else {
                $sessionCart = Session::get('cart', []);
                $mpItems = collect($sessionCart)->map(function($item) {
                    $product = Product::find($item['id']);
                    $priceNet = $product ? (float) $product->price : (float) ($item['price'] ?? 0);
                    $priceGross = round($priceNet * (1 + $this->ivaRate), 2);

                    $itemQuantity = (int) ($item['quantity'] ?? 0);
                    if ($priceGross <= 0 || $itemQuantity <= 0) {
                        \Log::error('Producto con precio o cantidad inválida detectado en el carrito de sesión.', [
                            'product_id' => $item['id'],
                            'product_name' => $item['name'] ?? 'Desconocido',
                            'unit_price_gross' => $priceGross,
                            'quantity' => $itemQuantity
                        ]);
                        return null;
                    }

                    return [
                        'id' => (string) ($item['id'] ?? uniqid()),
                        'title' => $item['name'] ?? 'Producto Desconocido',
                        'description' => Str::limit($product->description ?? $item['name'] ?? 'Producto', 250),
                        'quantity' => $itemQuantity,
                        'unit_price' => (float) $priceGross,
                        'currency_id' => "COP",
                        'picture_url' => $item['image'] ?? asset('images/default_product.png'),
                    ];
                })->filter()->values();
            }

            // Añadir la comisión de MP si no estamos en modo debug (el modo debug tiene un producto simple)
            if ($mpItems->isEmpty()) {
                \Log::error('El carrito está vacío después de filtrar productos inválidos. No se puede proceder con el pago.');
                return back()->with('error', 'Tu carrito está vacío o contiene productos no válidos. No se puede proceder con el pago.');
            }

            // Obtener los ítems formateados del CartController para un cálculo de totales consistente.
            // Esto es importante para el log, incluso si no se usa para el payload final de MP.
            $actualCartItemsForTotals = $this->cartController->getFormattedCartItems();
            $totals = $this->cartController->calculateCartTotals($actualCartItemsForTotals);

            if ($totals['mp_fee_amount'] > 0) {
                $mpItems->push([
                    'id' => 'MP_FEE',
                    'title' => 'Comisión de Mercado Pago',
                    'description' => 'Costo por servicio de procesamiento de pago',
                    'quantity' => 1,
                    'unit_price' => (float) round($totals['mp_fee_amount'], 2),
                    'currency_id' => "COP",
                ]);
            }
        } // Fin del else (modo normal)

        // Asegurarse de que el monto total de ítems sea positivo
        $calculatedTotalFromItems = $mpItems->sum(function($item){ return $item['unit_price'] * $item['quantity']; });
        if ($calculatedTotalFromItems <= 0) {
            \Log::error('Intento de crear preferencia con la suma total de ítems <= 0: ' . $calculatedTotalFromItems);
            return back()->with('error', 'El monto total a pagar de los productos debe ser positivo.');
        }

        // ===================================================================
        // LOGS IMPORTANTES ANTES DE ENVIAR A MERCADO PAGO
        // ===================================================================
        \Log::info('Mercado Pago Preference Payload (Final):', [
            'items_enviados_a_mp' => $mpItems->toArray(),
            'total_sum_of_mp_items' => $calculatedTotalFromItems,
            'payer_email' => $payerEmail,
            'debug_mode_activo' => $debugMode, // Para saber si estamos en modo debug
        ]);
        // ===================================================================

        $preferenceClient = new PreferenceClient();
        try {
            $preferenceData = [
                "items" => $mpItems->toArray(),
                "back_urls" => [
                    "success" => route('mercadopago.success'),
                    "failure" => route('mercadopago.failure'),
                    "pending" => route('mercadopago.pending')
                ],
                "auto_return" => "approved",
                "notification_url" => route('mercadopago.webhook') . '?source_news=webhooks',
                "external_reference" => 'ORDER-' . uniqid(),
                "statement_descriptor" => "TIENDAJD",
                "payer" => [
                    "email" => $payerEmail, // Usa el email ya determinado (normal o debug)
                ],
            ];

            $response = $preferenceClient->create($preferenceData);

            return redirect()->away($response->init_point);

        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $errorMessage = $e->getMessage();
            $errorDetails = [];

            if ($apiResponse && property_exists($apiResponse, 'error') && $apiResponse->error) {
                $errorMessage = $apiResponse->error->message ?? $errorMessage;
                $errorDetails = $apiResponse->error->cause ?? [];
            }
            $rawApiResponseString = json_encode($apiResponse);
            \Log::error('Error de API de Mercado Pago al crear preferencia: ' . $errorMessage, [
                'details' => $errorDetails,
                'exception_code' => $e->getCode(),
                'api_response_raw' => $rawApiResponseString
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

            return back()->with('error', $userMessage);

        } catch (\Exception $e) {
            \Log::error('Error general al crear preferencia de pago: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Error interno al procesar el pago. Por favor, intenta de nuevo.');
        }
    }

    public function paymentSuccess(Request $request)
    {
        $paymentId = $request->query('payment_id');
        $externalReference = $request->query('external_reference');
        \Log::info('Mercado Pago Success Callback:', $request->all());
        return view('mercadopago.success', compact('paymentId', 'externalReference'))->with('message', '¡Gracias por tu compra! Tu pago ha sido recibido y está siendo procesado.');
    }

    public function paymentFailure(Request $request)
    {
        $paymentId = $request->query('payment_id');
        $externalReference = $request->query('external_reference');
        \Log::info('Mercado Pago Failure Callback:', $request->all());
        return view('mercadopago.failure', compact('paymentId', 'externalReference'))->with('message', 'Lo sentimos, tu pago no pudo ser procesado. Por favor, inténtalo de nuevo.');
    }

    public function paymentPending(Request $request)
    {
        $paymentId = $request->query('payment_id');
        $externalReference = $request->query('external_reference');
        \Log::info('Mercado Pago Pending Callback:', $request->all());
        return view('mercadopago.pending', compact('paymentId', 'externalReference'))->with('message', 'Tu pago está pendiente de confirmación. Te notificaremos cuando se apruebe.');
    }

    public function handleWebhook(Request $request)
    {
        $topic = $request->input('topic');
        $id = $request->input('id');

        \Log::info('Webhook de Mercado Pago recibido:', $request->all());

        if ($topic === 'payment' && !empty($id)) {
            try {
                $paymentClient = new PaymentClient();
                $payment = $paymentClient->get($id);

                if ($payment) {
                    $paymentId = $payment->id;
                    $paymentStatus = $payment->status;
                    $externalReference = $payment->external_reference;

                    \Log::info("Webhook: Procesando pago MP ID: {$paymentId}, Estado: {$paymentStatus}, Ref Externa: {$externalReference}");

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

                \Log::error('Error de API en Webhook al obtener pago: ' . $errorMessage, ['details' => $errorDetails, 'exception_code' => $e->getCode(), 'api_response_raw' => json_encode($apiResponse)]);
            } catch (\Exception $e) {
                \Log::error('Error general en Webhook al procesar: ' . $e->getMessage());
            }
        }
        return response()->json(['status' => 'ok'], 200);
    }
}

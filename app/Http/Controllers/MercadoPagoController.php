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
use Illuminate\Support\Facades\Log; // Asegúrate de tener esto

class MercadoPagoController extends Controller
{
    protected $cartController;
    private $ivaRate = 0.19; // Asegurarse de que la tasa de IVA sea la misma aquí

    /**
     * Constructor para inicializar el SDK de Mercado Pago.
     * Inyecta el CartController.
     */
    public function __construct(CartController $cartController)
    {
        $this->cartController = $cartController;

        $accessToken = config('services.mercadopago.access_token');

        // ===================================================================
        // LÍNEA DE DEPURACIÓN TEMPORAL - DESCOMENTA PARA VER EL ACCESS TOKEN
        // Una vez que verifiques que el token se carga, VUELVE A COMENTAR O ELIMINAR ESTA LÍNEA
        // ===================================================================
        dd('Access Token que Laravel está cargando: ' . ($accessToken ?? 'NULO/VACÍO'));
        // ===================================================================

        if (empty($accessToken)) {
            \Log::critical('Mercado Pago Access Token no configurado o es nulo. Verifique su .env y config/services.php');
            // Si quieres que la aplicación se detenga aquí si el token es nulo, puedes descomentar el siguiente dd:
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
        $mpItems = collect(); // Ítems formateados específicamente para el payload de Mercado Pago

        if (Auth::check()) {
            $user = Auth::user();
            // Carga la relación 'cartItems' (CORREGIDO)
            $cart = $user->cart()->with('cartItems.product')->first();
            if (!$cart) {
                return back()->with('error', 'Tu carrito no existe o está vacío.');
            }

            // Construir los ítems para Mercado Pago (con precios brutos)
            $mpItems = ($cart->cartItems ?? collect())->filter(fn($item) => $item->product)->map(function($item) {
                $productPriceNet = $item->product->price; // Precio NETO de la DB
                $productPriceGross = round($productPriceNet * (1 + $this->ivaRate), 2); // Calcular el precio BRUTO para MP

                return [
                    'id' => $item->product_id,
                    'title' => $item->product->name,
                    'description' => Str::limit($item->product->description ?? $item->product->name, 250),
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $productPriceGross, // ENVIAR EL PRECIO BRUTO A MERCADO PAGO
                    'currency_id' => "COP",
                    'picture_url' => $item->product->image_url ?? asset('images/default_product.png'),
                ];
            });

        } else {
            // Lógica para carrito de sesión (guest)
            $sessionCart = Session::get('cart', []);
            $mpItems = collect($sessionCart)->map(function($item) {
                $product = Product::find($item['id']);
                $priceNet = $product ? $product->price : ($item['price'] ?? 0); // Asumiendo que el precio del producto es NETO
                $priceGross = round($priceNet * (1 + $this->ivaRate), 2); // Calcular el precio BRUTO para MP

                return [
                    'id' => $item['id'],
                    'title' => $item['name'] ?? 'Producto Desconocido',
                    'description' => Str::limit($product->description ?? $item['name'] ?? 'Producto', 250),
                    'quantity' => $item['quantity'],
                    'unit_price' => (float) $priceGross, // ENVIAR EL PRECIO BRUTO A MERCADO PAGO
                    'currency_id' => "COP",
                    'picture_url' => $item['image'] ?? asset('images/default_product.png'),
                ];
            });
        }

        if ($mpItems->isEmpty()) {
            return back()->with('error', 'Tu carrito está vacío. No se puede proceder con el pago.');
        }

        // ===================================================================
        // MODIFICACIÓN CRÍTICA:
        // Obtener los ítems formateados del CartController para un cálculo de totales consistente.
        // ===================================================================
        $actualCartItemsForTotals = $this->cartController->getFormattedCartItems();
        $totals = $this->cartController->calculateCartTotals($actualCartItemsForTotals);

        // ===================================================================
        // AQUÍ ES DONDE AÑADES LA COMISIÓN DE MP COMO UN ITEM ADICIONAL
        // ===================================================================
        if ($totals['mp_fee_amount'] > 0) {
            $mpItems->push([
                'id' => 'MP_FEE',
                'title' => 'Comisión de Mercado Pago',
                'description' => 'Costo por servicio de procesamiento de pago',
                'quantity' => 1,
                'unit_price' => (float) $totals['mp_fee_amount'], // La comisión se añade como un único ítem con su valor total
                'currency_id' => "COP",
                // Opcional: una imagen genérica si tienes para fees/impuestos
                // 'picture_url' => asset('images/mercadopago_fee.png'),
            ]);
        }
        // ===================================================================

        // El final_total_to_pay ya no es estrictamente necesario para el parámetro transaction_amount si los ítems suman correctamente.
        // Sin embargo, mantenerlo para doble verificación o si MP lo requiriera para otros propósitos.
        $final_total_to_pay = $totals['final_total'];


        // ===================================================================
        // AÑADE ESTO PARA DEPURACIÓN (MANTENER)
        // ===================================================================
        \Log::info('Detalles de totales antes de enviar a Mercado Pago:', [
            'subtotal_net_productos' => $totals['subtotal_net_products'],
            'iva_productos_calculado' => $totals['iva_products_amount'],
            'subtotal_gross_productos' => $totals['subtotal_gross_products'],
            'comision_mp_total' => $totals['mp_fee_amount'],
            'total_final_a_pagar_cartcontroller' => $totals['final_total'], // Este es el calculado en el CartController
            'items_enviados_a_mp' => $mpItems->toArray(), // Para ver los precios unitarios enviados a MP (ahora brutos)
            'total_sum_of_mp_items' => $mpItems->sum(function($item){ return $item['unit_price'] * $item['quantity']; }), // Suma de los items que se enviarán a MP
        ]);
        // ===================================================================

        // Asegurarse de que el monto sea válido
        if ($final_total_to_pay <= 0) {
            \Log::error('Intento de crear preferencia con monto total <= 0: ' . $final_total_to_pay);
            return back()->with('error', 'El monto total a pagar debe ser positivo.');
        }

        $preferenceClient = new PreferenceClient();
        try {
            $preferenceData = [
                "items" => $mpItems->toArray(), // Aquí se usan los $mpItems que AHORA incluyen la comisión
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
                    "email" => Auth::check() ? Auth::user()->email : 'invitado@ejemplo.com',
                ],
                // Es buena práctica mantener el transaction_amount para consistencia,
                // aunque Mercado Pago a menudo usa la suma de los ítems si están presentes.
                // Asegúrate de que este valor coincida con la suma de los items que estás enviando.
                "transaction_amount" => (float) $final_total_to_pay,
            ];

            \Log::info('Mercado Pago Preference Payload (Final):', $preferenceData);

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

            \Log::error('Error de API de Mercado Pago al crear preferencia: ' . $errorMessage, ['details' => $errorDetails, 'exception_code' => $e->getCode(), 'api_response_raw' => json_encode($apiResponse)]);

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
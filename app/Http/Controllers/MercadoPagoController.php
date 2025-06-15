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
            $cart = $user->cart()->with('cartItems.product')->first();
            if (!$cart) {
                return back()->with('error', 'Tu carrito no existe o está vacío.');
            }

            // Construir los ítems para Mercado Pago (con precios brutos)
            $mpItems = ($cart->cartItems ?? collect())->filter(fn($item) => $item->product)->map(function($item) {
                $productPriceNet = (float) $item->product->price; // Precio NETO de la DB
                // Calcular el precio bruto y redondearlo para asegurar 2 decimales
                $productPriceGross = round($productPriceNet * (1 + $this->ivaRate), 2);

                return [
                    'id' => (string) $item->product_id, // Asegurar que sea string
                    'title' => $item->product->name,
                    'description' => Str::limit($item->product->description ?? $item->product->name, 250),
                    'quantity' => (int) $item->quantity, // Asegurar que sea int
                    'unit_price' => (float) $productPriceGross, // Lo pasamos como float al final, asegurando el formato
                    'currency_id' => "COP",
                    'picture_url' => $item->product->image_url ?? asset('images/default_product.png'),
                ];
            });

        } else {
            // Lógica para carrito de sesión (guest)
            $sessionCart = Session::get('cart', []);
            $mpItems = collect($sessionCart)->map(function($item) {
                $product = Product::find($item['id']);
                $priceNet = $product ? (float) $product->price : (float) ($item['price'] ?? 0); // Asumiendo que el precio del producto es NETO
                // Calcular el precio bruto y redondearlo para asegurar 2 decimales
                $priceGross = round($priceNet * (1 + $this->ivaRate), 2);

                return [
                    'id' => (string) ($item['id'] ?? uniqid()), // Asegurar que sea string y un fallback
                    'title' => $item['name'] ?? 'Producto Desconocido',
                    'description' => Str::limit($product->description ?? $item['name'] ?? 'Producto', 250),
                    'quantity' => (int) ($item['quantity'] ?? 0), // Asegurar que sea int
                    'unit_price' => (float) $priceGross, // Lo pasamos como float al final, asegurando el formato
                    'currency_id' => "COP",
                    'picture_url' => $item['image'] ?? asset('images/default_product.png'),
                ];
            });
        }

        if ($mpItems->isEmpty()) {
            return back()->with('error', 'Tu carrito está vacío. No se puede proceder con el pago.');
        }

        // Obtener los ítems formateados del CartController para un cálculo de totales consistente.
        $actualCartItemsForTotals = $this->cartController->getFormattedCartItems();
        $totals = $this->cartController->calculateCartTotals($actualCartItemsForTotals);

        // AÑADIR LA COMISIÓN DE MP COMO UN ITEM ADICIONAL
        if ($totals['mp_fee_amount'] > 0) {
            $mpItems->push([
                'id' => 'MP_FEE',
                'title' => 'Comisión de Mercado Pago',
                'description' => 'Costo por servicio de procesamiento de pago',
                'quantity' => 1,
                // Usamos round directamente para la comisión
                'unit_price' => (float) round($totals['mp_fee_amount'], 2),
                'currency_id' => "COP",
            ]);
        }

        // ===================================================================
        // LOGS IMPORTANTES (mantener para monitoreo)
        // ===================================================================
        \Log::info('Detalles de totales antes de enviar a Mercado Pago (FINAL):', [
            'subtotal_net_productos' => $totals['subtotal_net_products'],
            'iva_productos_calculado' => $totals['iva_products_amount'],
            'subtotal_gross_productos' => $totals['subtotal_gross_products'],
            'comision_mp_total' => $totals['mp_fee_amount'],
            'total_final_a_pagar_cartcontroller' => $totals['final_total'],
            'items_enviados_a_mp' => $mpItems->toArray(),
            'total_sum_of_mp_items' => $mpItems->sum(function($item){ return $item['unit_price'] * $item['quantity']; }),
        ]);
        // ===================================================================

        // Asegurarse de que el monto sea válido (aunque ya no se envía transaction_amount, la suma de ítems debe ser > 0)
        if ($mpItems->sum(function($item){ return $item['unit_price'] * $item['quantity']; }) <= 0) {
            \Log::error('Intento de crear preferencia con monto total de ítems <= 0.');
            return back()->with('error', 'El monto total a pagar debe ser positivo.');
        }

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
                    "email" => Auth::check() ? Auth::user()->email : 'invitado@ejemplo.com',
                ],
                // ===============================================================================
                // CAMBIO CLAVE: Se ha eliminado 'transaction_amount'.
                // Mercado Pago calculará el total basándose en la suma de los 'items'.
                // Esto ayuda a evitar inconsistencias por redondeo.
                // ===============================================================================
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;
use Illuminate\Support\Str;

class MercadoPagoController extends Controller
{
    /**
     * Constructor para inicializar el SDK de Mercado Pago.
     * El access token se obtiene de la configuración de servicios.
     */
    public function __construct()
    {
        $accessToken = config('services.mercadopago.access_token');

        if (empty($accessToken)) {
            // Esto es un error crítico; en producción, el token nunca debería ser nulo.
            \Log::critical('Mercado Pago Access Token no configurado o es nulo. Verifique su .env y config/services.php');
            // Opcionalmente, puedes lanzar una excepción para detener la aplicación si el token es vital.
            // throw new \Exception('Mercado Pago Access Token no configurado.');
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
        $request->validate([
            'total_amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
        ]);

        $amount = $request->input('total_amount');
        $description = $request->input('description');
        $externalReference = 'ORDER-' . uniqid(); // Referencia única para tu orden

        $preferenceClient = new PreferenceClient();
        try {
            $response = $preferenceClient->create([
                "items" => [
                    [
                        "title" => $description,
                        "quantity" => 1,
                        "unit_price" => (float) $amount,
                        "currency_id" => "COP" // Moneda de Colombia
                    ]
                ],
                "back_urls" => [
                    "success" => route('mercadopago.success'),
                    "failure" => route('mercadopago.failure'),
                    "pending" => route('mercadopago.pending')
                ],
                "auto_return" => "approved", // Redirige automáticamente al éxito
                "notification_url" => route('mercadopago.webhook') . '?source_news=webhooks',
                "external_reference" => $externalReference,
                "statement_descriptor" => "TIENDAJD", // Descriptor en el extracto de tarjeta
            ]);

            // Redirige al usuario al init_point de Mercado Pago
            return redirect()->away($response->init_point);

        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $errorMessage = $e->getMessage();
            $errorDetails = [];

            if ($apiResponse && property_exists($apiResponse, 'error') && $apiResponse->error) {
                $errorMessage = $apiResponse->error->message ?? $errorMessage;
                $errorDetails = $apiResponse->error->cause ?? [];
            }

            \Log::error('Error de API de Mercado Pago al crear preferencia: ' . $errorMessage, ['details' => $errorDetails, 'exception_code' => $e->getCode()]);

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
            \Log::error('Error general al crear preferencia de pago: ' . $e->getMessage());
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

        // Siempre devuelve un 200 OK a Mercado Pago rápidamente
        return response()->json(['status' => 'ok'], 200);

        if ($topic === 'payment') {
            try {
                $paymentClient = new PaymentClient();
                $payment = $paymentClient->get($id);

                if ($payment) {
                    $paymentId = $payment->id;
                    $paymentStatus = $payment->status;
                    $externalReference = $payment->external_reference;

                    \Log::info("Webhook: Procesando pago MP ID: {$paymentId}, Estado: {$paymentStatus}, Ref Externa: {$externalReference}");

                    // === TU LÓGICA PARA ACTUALIZAR EL ESTADO DE LA ORDEN AQUÍ ===
                    // $order = Order::where('external_reference', $externalReference)->first();
                    // if ($order) {
                    //     $order->mp_payment_id = $paymentId;
                    //     $order->status = $this->mapMercadoPagoStatusToOrderStatus($paymentStatus);
                    //     $order->save();
                    //     \Log::info("Orden {$order->id} actualizada a estado: {$order->status}");
                    // } else {
                    //     \Log::warning("Webhook: Orden no encontrada para referencia externa: {$externalReference}");
                    // }

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

                \Log::error('Error de API en Webhook al obtener pago: ' . $errorMessage, ['details' => $errorDetails, 'exception_code' => $e->getCode()]);
            } catch (\Exception $e) {
                \Log::error('Error general en Webhook al procesar: ' . $e->getMessage());
            }
        }
    }

    // private function mapMercadoPagoStatusToOrderStatus(string $mpStatus): string
    // {
    //     switch ($mpStatus) {
    //         case 'approved':
    //             return 'paid';
    //         case 'pending':
    //             return 'pending_payment';
    //         case 'rejected':
    //         case 'cancelled':
    //             return 'cancelled';
    //         case 'refunded':
    //             return 'refunded';
    //         case 'charged_back':
    //             return 'charged_back';
    //         default:
    //             return 'unknown';
    //     }
    // }
}
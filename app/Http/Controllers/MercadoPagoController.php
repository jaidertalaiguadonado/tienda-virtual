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
        // Obtiene el access token de la configuración de Laravel (config/services.php)
        $accessToken = config('services.mercadopago.access_token');

        // Establece el access token para Mercado Pago SDK
        if (empty($accessToken)) {
            // Esto es una medida de seguridad crítica. En producción, un token nulo debería ser un error fatal.
            \Log::critical('Mercado Pago Access Token no configurado o es nulo. Verifique .env y config/services.php');
            // Opcional: Si quieres que la aplicación falle ruidosamente en desarrollo si no hay token:
            // throw new \Exception('Mercado Pago Access Token no configurado. Por favor, verifique su archivo .env y config/services.php');
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
        // Validación de los datos de entrada
        $request->validate([
            'total_amount' => 'required|numeric|min:1', // Asumo 'total_amount' de la vista del carrito
            'description' => 'required|string|max:255',
        ]);

        $amount = $request->input('total_amount');
        $description = $request->input('description');
        $externalReference = 'ORDER-' . uniqid(); // Genera una referencia única para tu orden

        $preferenceClient = new PreferenceClient(); // Instancia del cliente de preferencias
        try {
            $response = $preferenceClient->create([
                "items" => [
                    [
                        "title" => $description,
                        "quantity" => 1,
                        "unit_price" => (float) $amount,
                        "currency_id" => "COP" // ¡Asegúrate que esta sea la moneda correcta! (COP para Colombia)
                    ]
                ],
                "back_urls" => [
                    "success" => route('mercadopago.success'),
                    "failure" => route('mercadopago.failure'),
                    "pending" => route('mercadopago.pending')
                ],
                "auto_return" => "approved", // Redirige automáticamente al usuario al éxito
                // notification_url debe ser accesible públicamente por Mercado Pago
                // y no debe tener redirecciones.
                "notification_url" => route('mercadopago.webhook') . '?source_news=webhooks', // Añade parámetro para identificar webhooks
                "external_reference" => $externalReference, // Pasa la referencia de tu orden
                "statement_descriptor" => "TIENDAJD", // Reemplaza con un descriptor corto para el extracto de tarjeta
            ]);

            // Redirige al usuario al init_point de Mercado Pago para completar el pago
            return redirect()->away($response->init_point);

        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            // Manejo de errores específicos de la API de Mercado Pago
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
            // Manejo de errores generales (conexión, etc.)
            \Log::error('Error general al crear preferencia de pago: ' . $e->getMessage());
            return back()->with('error', 'Error interno al procesar el pago. Por favor, intenta de nuevo.');
        }
    }

    /**
     * Maneja la redirección cuando el pago es exitoso.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function paymentSuccess(Request $request)
    {
        $paymentId = $request->query('payment_id');
        $externalReference = $request->query('external_reference');
        // Aquí podrías validar el paymentId y actualizar el estado de tu orden.
        \Log::info('Mercado Pago Success Callback:', $request->all());
        return view('mercadopago.success', compact('paymentId', 'externalReference'))->with('message', '¡Gracias por tu compra! Tu pago ha sido recibido y está siendo procesado.');
    }

    /**
     * Maneja la redirección cuando el pago falla.
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
     * Maneja la redirección cuando el pago está pendiente.
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
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request)
    {
        $topic = $request->input('topic');
        $id = $request->input('id');

        \Log::info('Webhook de Mercado Pago recibido:', $request->all());

        // Es crucial que devuelvas un 200 OK a Mercado Pago rápidamente
        // antes de hacer lógica pesada. Si tu lógica es compleja, considera usar colas.

        if ($topic === 'payment') {
            try {
                $paymentClient = new PaymentClient();
                $payment = $paymentClient->get($id); // Consulta la API de MP para obtener detalles del pago

                if ($payment) {
                    $paymentId = $payment->id;
                    $paymentStatus = $payment->status; // 'approved', 'pending', 'rejected', etc.
                    $externalReference = $payment->external_reference;

                    \Log::info("Webhook: Procesando pago MP ID: {$paymentId}, Estado: {$paymentStatus}, Ref Externa: {$externalReference}");

                    // =========================================================================
                    // === TU LÓGICA CRÍTICA PARA ACTUALIZAR EL ESTADO DE LA ORDEN AQUÍ ===
                    // =========================================================================
                    // Ejemplo (descomenta y adapta):
                    // $order = Order::where('external_reference', $externalReference)->first();
                    // if ($order) {
                    //     $order->mp_payment_id = $paymentId;
                    //     $order->status = $this->mapMercadoPagoStatusToOrderStatus($paymentStatus); // Mapea el estado de MP a tus estados internos
                    //     $order->save();
                    //     \Log::info("Orden {$order->id} actualizada a estado: {$order->status}");

                    //     // Opcional: Enviar email de confirmación al cliente, despachar producto, etc.
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
        // Siempre devuelve un 200 OK a Mercado Pago para indicar que la notificación fue recibida.
        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Mapea los estados de pago de Mercado Pago a tus estados internos de orden.
     * (Ejemplo, puedes adaptar esto a tus necesidades)
     *
     * @param string $mpStatus
     * @return string
     */
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
    //         // Agrega más estados según sean relevantes para tu flujo
    //         default:
    //             return 'unknown';
    //     }
    // }
}
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
        // LÍNEA DE DEPURACIÓN TEMPORAL - DESCOMENTADA PARA VER EL ACCESS TOKEN
        // DEBES COMENTARLA O ELIMINARLA UNA VEZ QUE VERIFIQUES QUE EL TOKEN SE CARGA.
        // ===================================================================
        //dd('Access Token que Laravel está cargando: ' . ($accessToken ?? 'NULO/VACÍO'));
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
        // ===================================================================
        // INICIO DE LA MODIFICACIÓN TEMPORAL PARA DEPURACIÓN
        // TODO el código ORIGINAL que calculaba $cart, $mpItems y $totals
        // FUE ELIMINADO/SOBREESCRITO por el siguiente bloque TEMPORAL.
        // ===================================================================

        $preferenceData = [
            "items" => [
                [
                    "title" => "Producto de Prueba",
                    "quantity" => 1,
                    "unit_price" => 100.00, // Un precio simple, positivo
                    "currency_id" => "COP",
                ]
            ],
            "back_urls" => [
                "success" => route('mercadopago.success'),
                "failure" => route('mercadopago.failure'),
                "pending" => route('mercadopago.pending')
            ],
            "auto_return" => "approved",
            "notification_url" => route('mercadopago.webhook') . '?source_news=webhooks',
            "external_reference" => 'ORDER-TEST-' . uniqid(),
            "payer" => [
                "email" => Auth::check() ? Auth::user()->email : 'invitado@ejemplo.com',
            ],
            "statement_descriptor" => "TIENDAJD",
            "transaction_amount" => 100.00, // Debe coincidir con la suma de los ítems
        ];
        // ===================================================================
        // FIN DE LA MODIFICACIÓN TEMPORAL PARA DEPURACIÓN
        // ===================================================================


        // ===================================================================
        // AÑADE ESTO PARA DEPURACIÓN (MANTENER)
        // ===================================================================
        \Log::info('Mercado Pago Preference Payload (FINAL - TEMP TEST):', $preferenceData);
        // ===================================================================

        // Asegurarse de que el monto sea válido (este check ahora se hace sobre $preferenceData['transaction_amount'])
        if ($preferenceData['transaction_amount'] <= 0) {
            \Log::error('Intento de crear preferencia con monto total <= 0: ' . $preferenceData['transaction_amount']);
            return back()->with('error', 'El monto total a pagar debe ser positivo.');
        }

        $preferenceClient = new PreferenceClient();
        try {
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
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;
use Illuminate\Support\Str;

class MercadoPagoController extends Controller
{
    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
    }

    public function createPaymentPreference(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
        ]);

        $amount = $request->input('amount');
        $description = $request->input('description');
        $externalReference = 'ORDER-' . uniqid();

        $preference = new PreferenceClient();
        try {
            $response = $preference->create([
                "items" => [
                    [
                        "title" => $description,
                        "quantity" => 1,
                        "unit_price" => (float) $amount,
                        "currency_id" => "COP"
                    ]
                ],
                "back_urls" => [
                    "success" => route('mercadopago.success'),
                    "failure" => route('mercadopago.failure'),
                    "pending" => route('mercadopago.pending')
                ],
                "auto_return" => "approved",
                "notification_url" => route('mercadopago.webhook') . '?source_news=webhooks',
                "external_reference" => $externalReference,
                "statement_descriptor" => "TIENDAJD",
            ]);

            return redirect($response->init_point);

        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            // **CORRECCIÓN AQUÍ:** Manejo de errores de la API de Mercado Pago
            $apiResponse = $e->getApiResponse();
            $errorMessage = $e->getMessage(); // Mensaje de la excepción por defecto
            $errorDetails = [];

            // Intenta extraer detalles del error de la respuesta de la API si están disponibles
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
            } elseif ($errorMessage !== $e->getMessage()) { // Si se obtuvo un mensaje más específico de la API
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
        return view('mercadopago.success', compact('paymentId', 'externalReference'))->with('message', '¡Gracias por tu compra! Tu pago ha sido recibido y está siendo procesado.');
    }

    public function paymentFailure(Request $request)
    {
        $paymentId = $request->query('payment_id');
        $externalReference = $request->query('external_reference');
        return view('mercadopago.failure', compact('paymentId', 'externalReference'))->with('message', 'Lo sentimos, tu pago no pudo ser procesado. Por favor, inténtalo de nuevo.');
    }

    public function paymentPending(Request $request)
    {
        $paymentId = $request->query('payment_id');
        $externalReference = $request->query('external_reference');
        return view('mercadopago.pending', compact('paymentId', 'externalReference'))->with('message', 'Tu pago está pendiente de confirmación. Te notificaremos cuando se apruebe.');
    }

    public function handleWebhook(Request $request)
    {
        $topic = $request->input('topic');
        $id = $request->input('id');

        \Log::info('Webhook de Mercado Pago recibido:', $request->all());

        if ($topic === 'payment') {
            try {
                $paymentClient = new PaymentClient();
                $payment = $paymentClient->get($id);

                if ($payment) {
                    $paymentId = $payment->id;
                    $paymentStatus = $payment->status;
                    $externalReference = $payment->external_reference;

                    \Log::info("Webhook: Procesando pago MP ID: {$paymentId}, Estado: {$paymentStatus}, Ref Externa: {$externalReference}");

                    // ... (Tu lógica para actualizar la orden) ...

                } else {
                    \Log::warning("Webhook: No se pudo obtener el objeto de pago para ID: {$id}");
                }

            } catch (\MercadoPago\Exceptions\MPApiException $e) {
                // **CORRECCIÓN AQUÍ:** Manejo de errores de la API de Mercado Pago en el webhook
                $apiResponse = $e->getApiResponse();
                $errorMessage = $e->getMessage(); // Mensaje de la excepción por defecto
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
        return response()->json(['status' => 'ok'], 200);
    }
}
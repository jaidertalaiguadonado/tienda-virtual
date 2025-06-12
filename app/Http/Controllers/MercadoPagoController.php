<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth; // Necesario para Auth::user()
use Illuminate\Support\Facades\Session; // Necesario para Session::get()
use App\Models\Product; // Asegurarse de que este modelo esté importado para los productos en sesión

class MercadoPagoController extends Controller
{
    protected $cartController;

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
            // En un entorno de producción, podrías querer lanzar una excepción aquí.
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
        // NO VALIDAR total_amount aquí, se obtendrá del carrito
        // La validación de stock y cantidad ya se hizo en el CartController
        // $request->validate([
        //     'total_amount' => 'required|numeric|min:1',
        //     'description' => 'required|string|max:255',
        // ]);

        // 1. Obtener los ítems y totales calculados por el CartController
        // Simulamos la lógica de show() para obtener los datos más recientes del carrito
        $cart = null;
        $subtotal_products_only = 0; // Este será el 'subtotal' de los productos, asumiendo que incluye IVA.
        $formattedCartItems = collect();

        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart()->with('items.product')->first(); // No firstOrCreate aquí, ya debería existir
            if ($cart) {
                foreach ($cart->items as $item) {
                    $subtotal_products_only += $item->quantity * $item->product->price;
                }
            }
            $formattedCartItems = $cart ? $cart->items->map(function($item) {
                return [
                    'id' => $item->product_id, // Usamos product_id para Mercado Pago item ID
                    'title' => $item->product->name,
                    'description' => Str::limit($item->product->description ?? $item->product->name, 250), // Breve descripción
                    'quantity' => $item->quantity,
                    'unit_price' => (float) round($item->product->price, 2), // Precio unitario del producto, redondeado
                    'currency_id' => "COP",
                    'picture_url' => $item->product->image_url ?? asset('images/default_product.png'),
                ];
            }) : collect();

        } else {
            $sessionCart = Session::get('cart', []);
            $formattedCartItems = collect($sessionCart)->map(function($item) {
                // Para invitados, item['id'] ya es el product_id
                $product = Product::find($item['id']); // Obtener el producto para detalles actualizados
                $price = $product ? $product->price : $item['price']; // Usar precio actual si el producto existe
                return [
                    'id' => $item['id'], // product_id
                    'title' => $item['name'],
                    'description' => Str::limit($product->description ?? $item['name'], 250),
                    'quantity' => $item['quantity'],
                    'unit_price' => (float) round($price, 2),
                    'currency_id' => "COP",
                    'picture_url' => $item['image'] ?? asset('images/default_product.png'),
                ];
            });
            $subtotal_products_only = $formattedCartItems->sum(function($item) {
                return $item['quantity'] * $item['unit_price'];
            });
        }

        if ($formattedCartItems->isEmpty()) {
            return back()->with('error', 'Tu carrito está vacío. No se puede proceder con el pago.');
        }

        // 2. Calcular los totales finales para la preferencia
        $totals = $this->cartController->calculateCartTotals($subtotal_products_only);
        $final_total_to_pay = $totals['final_total'];

        // Asegurarse de que el monto sea válido
        if ($final_total_to_pay <= 0) {
            \Log::error('Intento de crear preferencia con monto total <= 0: ' . $final_total_to_pay);
            return back()->with('error', 'El monto total a pagar debe ser positivo.');
        }

        $preferenceClient = new PreferenceClient();
        try {
            $preferenceData = [
                "items" => $formattedCartItems->toArray(), // Pasar el array de ítems formateado
                "back_urls" => [
                    "success" => route('mercadopago.success'),
                    "failure" => route('mercadopago.failure'),
                    "pending" => route('mercadopago.pending')
                ],
                "auto_return" => "approved",
                "notification_url" => route('mercadopago.webhook') . '?source_news=webhooks',
                "external_reference" => 'ORDER-' . uniqid(), // Generar una nueva referencia única aquí
                "statement_descriptor" => "TIENDAJD",
                "payer" => [
                    "email" => Auth::check() ? Auth::user()->email : 'invitado@example.com', // Usar el email del usuario o un placeholder para invitados
                    // Agrega más datos del pagador si los tienes (nombre, apellido, teléfono, etc.)
                    // "name" => Auth::check() ? Auth::user()->name : null,
                    // "surname" => Auth::check() ? Auth::user()->last_name : null,
                ],
                // El transaction_amount se calcula automáticamente si envías los items,
                // pero puedes forzarlo si lo necesitas y si los items individuales lo justifican.
                "transaction_amount" => (float) $final_total_to_pay,
            ];

            // Opcional: Loguear el payload de la preferencia antes de enviarlo
            \Log::info('Mercado Pago Preference Payload:', $preferenceData);

            $response = $preferenceClient->create($preferenceData);

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

        // Siempre devuelve un 200 OK a Mercado Pago rápidamente
        // Mover la lógica de procesamiento real a una cola o Job si es compleja
        if ($topic === 'payment' && !empty($id)) {
            // Esto es crucial: devuelve el 200 OK inmediatamente ANTES de procesar
            // para evitar reintentos de Mercado Pago.
            // La lógica de procesamiento debe ir en un Job o al final del método
            // después de asegurar la respuesta 200.
            
            // Si el procesamiento es rápido, puedes dejarlo aquí, pero si no, es mejor un Job.
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
                    //      $order->mp_payment_id = $paymentId;
                    //      $order->status = $this->mapMercadoPagoStatusToOrderStatus($paymentStatus);
                    //      $order->save();
                    //      \Log::info("Orden {$order->id} actualizada a estado: {$order->status}");
                    // } else {
                    //      \Log::warning("Webhook: Orden no encontrada para referencia externa: {$externalReference}");
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
        return response()->json(['status' => 'ok'], 200); // Siempre devolver 200 OK
    }

    // private function mapMercadoPagoStatusToOrderStatus(string $mpStatus): string
    // {
    //      switch ($mpStatus) {
    //          case 'approved':
    //              return 'paid';
    //          case 'pending':
    //              return 'pending_payment';
    //          case 'rejected':
    //          case 'cancelled':
    //              return 'cancelled';
    //          case 'refunded':
    //              return 'refunded';
    //          case 'charged_back':
    //              return 'charged_back';
    //          default:
    //              return 'unknown';
    //      }
    // }
}
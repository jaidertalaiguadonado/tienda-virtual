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
        $mpItems = collect();

        if (Auth::check()) {
            $user = Auth::user();
            $cart = $user->cart()->with('cartItems.product')->first();
            if (!$cart) {
                // Aquí usamos dd() para ver si el carrito es nulo o vacío
                dd('ERROR: Carrito no encontrado para usuario logueado o está vacío.');
            }

            $mpItems = ($cart->cartItems ?? collect())->filter(fn($item) => $item->product)->map(function($item) {
                $productPriceNet = $item->product->price;
                $productPriceGross = round($productPriceNet * (1 + $this->ivaRate), 2);

                // Aquí puedes agregar un dd() temporal para ver un solo ítem si hay dudas
                // dd(['Item de carrito original' => $item->toArray(), 'Item para MP' => [
                //     'id' => $item->product_id,
                //     'title' => $item->product->name,
                //     'description' => Str::limit($item->product->description ?? $item->product->name, 250),
                //     'quantity' => $item->quantity,
                //     'unit_price' => (float) $productPriceGross,
                //     'currency_id' => "COP",
                //     'picture_url' => $item->product->image_url ?? asset('images/default_product.png'),
                // ]]);

                return [
                    'id' => $item->product_id,
                    'title' => $item->product->name,
                    'description' => Str::limit($item->product->description ?? $item->product->name, 250),
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $productPriceGross,
                    'currency_id' => "COP",
                    'picture_url' => $item->product->image_url ?? asset('images/default_product.png'),
                ];
            });

        } else {
            $sessionCart = Session::get('cart', []);
            if (empty($sessionCart)) {
                dd('ERROR: Carrito de sesión vacío.');
            }
            $mpItems = collect($sessionCart)->map(function($item) {
                $product = Product::find($item['id']);
                $priceNet = $product ? $product->price : ($item['price'] ?? 0);
                $priceGross = round($priceNet * (1 + $this->ivaRate), 2);

                return [
                    'id' => $item['id'],
                    'title' => $item['name'] ?? 'Producto Desconocido',
                    'description' => Str::limit($product->description ?? $item['name'] ?? 'Producto', 250),
                    'quantity' => $item['quantity'],
                    'unit_price' => (float) $priceGross,
                    'currency_id' => "COP",
                    'picture_url' => $item['image'] ?? asset('images/default_product.png'),
                ];
            });
        }

        if ($mpItems->isEmpty()) {
            dd('ERROR: mpItems está vacío después de procesar el carrito. Revisa la lógica de mapeo.');
        }

        $actualCartItemsForTotals = $this->cartController->getFormattedCartItems();
        $totals = $this->cartController->calculateCartTotals($actualCartItemsForTotals);

        if ($totals['mp_fee_amount'] > 0) {
            $mpItems->push([
                'id' => 'MP_FEE',
                'title' => 'Comisión de Mercado Pago',
                'description' => 'Costo por servicio de procesamiento de pago',
                'quantity' => 1,
                'unit_price' => (float) $totals['mp_fee_amount'],
                'currency_id' => "COP",
            ]);
        }

        $final_total_to_pay = $totals['final_total'];

        // ===================================================================
        // PUNTO CRÍTICO DE DEPURACIÓN: dd() de los datos antes de enviar a MP
        // ===================================================================
        dd([
            'subtotal_net_productos' => $totals['subtotal_net_products'],
            'iva_productos_calculado' => $totals['iva_products_amount'],
            'subtotal_gross_productos' => $totals['subtotal_gross_products'],
            'comision_mp_total' => $totals['mp_fee_amount'],
            'total_final_a_pagar_cartcontroller' => $totals['final_total'],
            'items_enviados_a_mp' => $mpItems->toArray(),
            'total_sum_of_mp_items' => $mpItems->sum(function($item){ return $item['unit_price'] * $item['quantity']; }),
            'Tipo de total_final_a_pagar' => gettype($final_total_to_pay),
            'Tipo de items_enviados_a_mp[0][unit_price]' => !empty($mpItems) ? gettype($mpItems->first()['unit_price']) : 'N/A',
            'Valor de total_final_a_pagar' => $final_total_to_pay,
            'Valores de unit_price en mpItems' => $mpItems->pluck('unit_price')->toArray(),
            'Valores de quantity en mpItems' => $mpItems->pluck('quantity')->toArray(),
        ]);
        // ===================================================================

        // El código de Mercado PagoClient::create y los try/catch va después de este dd().
        // No deberíamos llegar a esta parte del código con este dd() activo.

        if ($final_total_to_pay <= 0) {
            \Log::error('Intento de crear preferencia con monto total <= 0: ' . $final_total_to_pay);
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
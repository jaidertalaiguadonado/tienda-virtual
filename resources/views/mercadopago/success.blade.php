<h1>¡Pago Exitoso!</h1>
<p>Gracias por tu compra. Tu pago ha sido recibido y está siendo procesado.</p>
<p>ID de pago de Mercado Pago: {{ $paymentId ?? 'N/A' }}</p>
<p>Referencia de tu orden: {{ $externalReference ?? 'N/A' }}</p>
<p>Revisa tu correo electrónico para la confirmación final de tu orden.</p>
<a href="{{ url('/') }}">Volver al inicio</a>
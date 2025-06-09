<h1>Pago Fallido</h1>
<p>Lo sentimos, tu pago no pudo ser procesado.</p>
<p>Por favor, inténtalo de nuevo o contacta a soporte si el problema persiste.</p>
<p>ID de pago de Mercado Pago: {{ $paymentId ?? 'N/A' }}</p>
<p>Referencia de tu orden: {{ $externalReference ?? 'N/A' }}</p>
<a href="{{ url('/checkout') }}">Volver al carrito</a>
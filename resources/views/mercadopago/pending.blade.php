<h1>Pago Pendiente</h1>
<p>Tu pago está pendiente de confirmación.</p>
<p>Te notificaremos por correo electrónico cuando el pago se apruebe.</p>
<p>ID de pago de Mercado Pago: {{ $paymentId ?? 'N/A' }}</p>
<p>Referencia de tu orden: {{ $externalReference ?? 'N/A' }}</p>
<a href="{{ url('/') }}">Volver al inicio</a>
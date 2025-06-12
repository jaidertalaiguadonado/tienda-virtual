<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Tienda JD') }} - Carrito</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/js/app.js'])
    {{-- Si tienes un archivo CSS para el carrito, agrégalo aquí --}}
    {{-- <link rel="stylesheet" href="{{ asset('css/cart.css') }}"> --}}
    <style>
        /* Estilos básicos para el carrito para que se vea algo */
        body { font-family: 'Open Sans', sans-serif; margin: 0; background-color: #f4f4f4; }
        .navbar { background-color: #333; color: white; padding: 1em; display: flex; justify-content: space-between; align-items: center; }
        .navbar-brand { color: white; text-decoration: none; font-size: 1.5em; }
        .navbar-link { color: white; text-decoration: none; margin-left: 1em; }
        .navbar-links { display: flex; }
        .navbar-links-mobile { display: none; /* Oculto por defecto, se muestra con JS para móviles */ }
        .menu-toggle { display: none; /* Oculto por defecto, se muestra con JS para móviles */ }
        .logout-button-navbar { background: none; border: none; color: white; cursor: pointer; font-size: 1em; margin-left: 1em; }

        .cart-container { max-width: 960px; margin: 2em auto; padding: 1em; background-color: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #333; margin-bottom: 1.5em; }
        .cart-table { width: 100%; border-collapse: collapse; margin-bottom: 2em; }
        .cart-table th, .cart-table td { border: 1px solid #ddd; padding: 0.8em; text-align: left; }
        .cart-table th { background-color: #f8f8f8; }
        .cart-table img { vertical-align: middle; margin-right: 0.5em; }
        .quantity-control { display: flex; align-items: center; }
        .quantity-control button { background-color: #007bff; color: white; border: none; padding: 0.5em 0.8em; cursor: pointer; border-radius: 4px; font-size: 1em; }
        .quantity-control input { width: 40px; text-align: center; margin: 0 0.5em; border: 1px solid #ccc; border-radius: 4px; padding: 0.4em; }
        .remove-item { background-color: #dc3545; color: white; border: none; padding: 0.5em 1em; cursor: pointer; border-radius: 4px; }
        .cart-summary { background-color: #f9f9f9; border: 1px solid #eee; padding: 1.5em; border-radius: 8px; margin-top: 1em; display: flex; flex-direction: column; align-items: flex-end; }
        .cart-summary p { margin: 0.5em 0; font-size: 1.1em; }
        .cart-summary strong { font-size: 1.3em; color: #007bff; }
        .empty-cart-message { text-align: center; margin-top: 2em; font-size: 1.2em; color: #666; }
        .cart-actions { display: flex; justify-content: space-between; margin-top: 2em; }
        .continue-shopping-button, .mercadopago-pay-button { background-color: #28a745; color: white; padding: 0.8em 1.5em; text-decoration: none; border-radius: 4px; font-size: 1.1em; border: none; cursor: pointer; }
        .mercadopago-pay-button { background-color: #007bff; }
        .footer { text-align: center; padding: 1em; background-color: #333; color: white; margin-top: 2em; }
        /* Media queries para móviles */
        @media (max-width: 768px) {
            .navbar-links { display: none; }
            .navbar-links-mobile { display: flex; flex-direction: column; position: absolute; top: 60px; left: 0; width: 100%; background-color: #333; padding: 1em 0; z-index: 1000; }
            .navbar-links-mobile.active { display: flex; }
            .navbar-link, .logout-button-navbar { width: 100%; text-align: center; padding: 0.5em 0; }
            .menu-toggle { display: block; background: none; border: none; color: white; font-size: 1.5em; cursor: pointer; }
            .cart-actions { flex-direction: column; align-items: center; }
            .continue-shopping-button, .mercadopago-pay-button { width: 80%; margin-bottom: 1em; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="{{ route('welcome') }}" class="navbar-brand">{{ config('app.name', 'Mi Tienda') }}</a>
        <button class="menu-toggle" aria-label="Toggle navigation menu">
            ☰
        </button>
        <div class="navbar-links">
            <a href="{{ route('cart.show') }}" class="navbar-link">
                Carrito (<span id="cart-item-count">0</span>)
            </a>
            @auth
                @if (Auth::user()?->isAdmin())
                    <a href="{{ url('/dashboard') }}" class="navbar-link">Dashboard</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-button-navbar">
                        {{ __('Cerrar Sesión') }}
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="navbar-link">Iniciar Sesión</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="navbar-link">Registrarse</a>
                @endif
            @endauth
        </div>

        <div class="navbar-links-mobile">
            <a href="{{ route('cart.show') }}" class="navbar-link">
                Carrito (<span id="cart-item-count-mobile">0</span>)
            </a>
            @auth
                @if (Auth::user()?->isAdmin())
                    <a href="{{ url('/dashboard') }}" class="navbar-link">Dashboard</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-button-navbar">
                        {{ __('Cerrar Sesión') }}
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="navbar-link">Iniciar Sesión</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="navbar-link">Registrarse</a>
                @endif
            @endauth
        </div>
    </nav>

    <div class="cart-container">
        <h1>Tu Carrito de Compras</h1>

        {{-- La tabla del carrito se mostrará/ocultará con JavaScript --}}
        <table class="cart-table" style="display: {{ empty($cartItems) ? 'none' : 'table' }};">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio Unitario (IVA Inc.)</th>
                    <th>Cantidad</th>
                    <th>Subtotal (IVA Inc.)</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="cart-items-body">
                @foreach($cartItems as $item)
                    <tr data-id="{{ $item['id'] }}"> {{-- data-id es el ID del CartItem (logueado) o product_id (invitado) --}}
                        <td>
                            <img src="{{ asset($item['image_url']) }}" alt="{{ $item['name'] }}" width="50">
                            {{ $item['name'] }}
                        </td>
                        {{-- Utiliza $item['subtotal_item_gross'] / $item['quantity'] para el precio unitario bruto --}}
                        <td>${{ number_format($item['subtotal_item_gross'] / $item['quantity'], 2, ',', '.') }}</td>
                        <td>
                            <div class="quantity-control">
                                <button type="button" class="decrease-quantity" data-id="{{ $item['id'] }}">-</button>
                                <input type="number" value="{{ $item['quantity'] }}" min="0" class="item-quantity-input" data-id="{{ $item['id'] }}">
                                <button type="button" class="increase-quantity" data-id="{{ $item['id'] }}">+</button>
                            </div>
                        </td>
                        <td class="item-subtotal-gross" data-id="{{ $item['id'] }}">${{ number_format($item['subtotal_item_gross'], 2, ',', '.') }}</td>
                        <td>
                            <button type="button" class="remove-item" data-id="{{ $item['id'] }}">Eliminar</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- El resumen del carrito se mostrará/ocultará con JavaScript --}}
        <div class="cart-summary" style="display: {{ empty($cartItems) ? 'none' : 'flex' }};">
            <p>Subtotal Productos (sin IVA): $<span id="subtotal_net_products">{{ number_format($subtotal_net_products, 2, ',', '.') }}</span></p>
            <p>IVA Productos ({{ \App\Http\Controllers\CartController::IVA_RATE * 100 }}%): $<span id="iva_products_amount">{{ number_format($iva_products_amount, 2, ',', '.') }}</span></p>
            <p>Subtotal Productos (con IVA): $<span id="subtotal_gross_products">{{ number_format($subtotal_gross_products, 2, ',', '.') }}</span></p>
            <p>Comisión Mercado Pago: $<span id="mp_fee_amount">{{ number_format($mp_fee_amount, 2, ',', '.') }}</span></p>
            <p><strong>Total Final a Pagar: $<span id="final_total">{{ number_format($final_total, 2, ',', '.') }}</span></strong></p>
        </div>

        {{-- Mensaje de carrito vacío --}}
        <p class="empty-cart-message" style="display: {{ empty($cartItems) ? 'block' : 'none' }};">Tu carrito está vacío. ¡Empieza a añadir productos!</p>

        {{-- Acciones del carrito (Pagar, Seguir Comprando) - se ocultan/muestran con JS --}}
        <div class="cart-actions" style="display: {{ empty($cartItems) ? 'none' : 'flex' }};">
            <a href="{{ route('welcome') }}" class="continue-shopping-button">Seguir Comprando</a>

            <form action="{{ route('mercadopago.pay') }}" method="POST" id="mercadopago-checkout-form">
                @csrf
                <input type="hidden" name="total_amount" id="mercadopago-amount" value="{{ $final_total }}">
                <input type="hidden" name="description" id="mercadopago-description" value="Compra en Tienda JD">
                <button type="submit" class="mercadopago-pay-button">Pagar con Mercado Pago</button>
            </form>
        </div>
        
        {{-- ESTE DIV ES SOLO PARA EL BOTÓN "SEGUIR COMPRANDO" CUANDO EL CARRITO ESTÁ VACÍO --}}
        <div class="empty-cart-actions" style="display: {{ empty($cartItems) ? 'flex' : 'none' }}; justify-content: center;">
            <a href="{{ route('welcome') }}" class="continue-shopping-button">Seguir Comprando</a>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'Mi Tienda') }}. Todos los derechos reservados.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const mobileMenu = document.querySelector('.navbar-links-mobile');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const cartItemCountElement = document.getElementById('cart-item-count');
            const cartItemCountMobileElement = document.getElementById('cart-item-count-mobile');
            
            // Elementos de la tabla y resumen del carrito
            const cartItemsBody = document.getElementById('cart-items-body');
            const cartTable = document.querySelector('.cart-table');
            const cartSummary = document.querySelector('.cart-summary');
            const emptyCartMessage = document.querySelector('.empty-cart-message');
            const cartActionsContainer = document.querySelector('.cart-actions');
            const emptyCartActionsContainer = document.querySelector('.empty-cart-actions');

            // Elementos del desglose de totales
            const subtotalNetProductsElement = document.getElementById('subtotal_net_products');
            const ivaProductsAmountElement = document.getElementById('iva_products_amount');
            const subtotalGrossProductsElement = document.getElementById('subtotal_gross_products');
            const mpFeeAmountElement = document.getElementById('mp_fee_amount');
            const finalTotalElement = document.getElementById('final_total');

            const mercadopagoAmountInput = document.getElementById('mercadopago-amount');
            const mercadopagoDescriptionInput = document.getElementById('mercadopago-description');
            
            // Toggle para el menú móvil
            if (menuToggle && mobileMenu) {
                menuToggle.addEventListener('click', function() {
                    mobileMenu.classList.toggle('active');
                });
            }
            
            // Función para formatear números para la interfaz de usuario
            function formatNumberForUI(number) {
                return parseFloat(number).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            // Función para obtener y actualizar el contador del carrito en el navbar
            function getCartCount() {
                fetch('{{ route('api.cart.count') }}', {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    if (cartItemCountElement) {
                        cartItemCountElement.textContent = data.cartCount;
                    }
                    if (cartItemCountMobileElement) {
                        cartItemCountMobileElement.textContent = data.cartCount;
                    }
                })
                .catch(error => {
                    console.error('Error al obtener el contador del carrito:', error);
                    if (cartItemCountElement) cartItemCountElement.textContent = '0';
                    if (cartItemCountMobileElement) cartItemCountMobileElement.textContent = '0';
                });
            }
            
            // Llamar al conteo del carrito al cargar la página
            getCartCount();

            // Función para actualizar toda la UI del carrito
            function updateCartUI(data) {
                // Actualizar los elementos del resumen del carrito
                if (subtotalNetProductsElement) {
                    subtotalNetProductsElement.textContent = formatNumberForUI(data.subtotal_net_products);
                }
                if (ivaProductsAmountElement) {
                    ivaProductsAmountElement.textContent = formatNumberForUI(data.iva_products_amount);
                }
                if (subtotalGrossProductsElement) {
                    subtotalGrossProductsElement.textContent = formatNumberForUI(data.subtotal_gross_products);
                }
                if (mpFeeAmountElement) {
                    mpFeeAmountElement.textContent = formatNumberForUI(data.mp_fee_amount);
                }
                if (finalTotalElement) {
                    finalTotalElement.textContent = formatNumberForUI(data.final_total);
                }

                // Actualizar el monto para Mercado Pago
                if (mercadopagoAmountInput) {
                    mercadopagoAmountInput.value = data.final_total; 
                }

                // Actualizar la visibilidad de la tabla y los botones
                const cartIsEmpty = data.cartCount === 0;

                if (cartTable) {
                    cartTable.style.display = cartIsEmpty ? 'none' : 'table';
                }
                if (cartSummary) {
                    cartSummary.style.display = cartIsEmpty ? 'none' : 'flex';
                }
                if (emptyCartMessage) {
                    emptyCartMessage.style.display = cartIsEmpty ? 'block' : 'none';
                }
                if (cartActionsContainer) {
                    cartActionsContainer.style.display = cartIsEmpty ? 'none' : 'flex';
                }
                if (emptyCartActionsContainer) {
                    emptyCartActionsContainer.style.display = cartIsEmpty ? 'flex' : 'none';
                }

                // Si hay un ítem específico actualizado en la respuesta (desde update)
                // Esto se usa cuando se actualiza solo un ítem y se devuelven sus detalles.
                if (data.item && data.item.id) {
                    const itemSubtotalGrossElement = document.querySelector(`.item-subtotal-gross[data-id="${data.item.id}"]`);
                    if (itemSubtotalGrossElement) {
                        itemSubtotalGrossElement.textContent = formatNumberForUI(data.item.subtotal_item_gross);
                    }
                    const itemQuantityInput = document.querySelector(`.item-quantity-input[data-id="${data.item.id}"]`);
                    if (itemQuantityInput) {
                        itemQuantityInput.value = data.item.quantity;
                    }
                }
                // Si el carrito está vacío y la tabla se oculta, también limpiar el tbody
                if (cartIsEmpty && cartItemsBody) {
                    cartItemsBody.innerHTML = '';
                }
            }
            
            // Función para enviar peticiones AJAX (generalizada)
            async function sendCartRequest(url, method, payloadData) {
                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payloadData)
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        // Mostrar mensaje de error del backend si hay un status 'error'
                        alert(errorData.message || 'Error en la petición.');
                        return;
                    }

                    const responseData = await response.json();
                    console.log('Respuesta del servidor:', responseData);
                    
                    // Actualizar la interfaz con los nuevos totales
                    updateCartUI(responseData);

                    // Lógica para eliminar la fila si la cantidad es 0 o se eliminó
                    // Ahora se basa en el 'cartCount' en la respuesta, y si el item específico fue eliminado.
                    if (responseData.cartCount === 0 || (responseData.item && responseData.item.quantity === 0)) {
                        // Si el carrito está vacío, updateCartUI ya lo maneja.
                        // Si un item específico fue eliminado (cantidad 0), remover su fila.
                        const rowToRemove = document.querySelector(`tr[data-id="${payloadData.id}"]`);
                        if (rowToRemove) {
                            rowToRemove.remove();
                        }
                    }
                    
                    getCartCount(); // Actualizar el contador del navbar
                    
                } catch (error) {
                    console.error('Error:', error);
                    alert('Ocurrió un error al procesar la solicitud: ' + error.message);
                }
            }

            // Manejadores de eventos usando delegación en cartItemsBody
            if (cartItemsBody) {
                cartItemsBody.addEventListener('click', function(event) {
                    const target = event.target;
                    // Obtener el data-id de la fila más cercana (que es el ID del CartItem o product_id)
                    const itemId = target.closest('tr')?.dataset.id; 

                    if (!itemId) return; 

                    if (target.classList.contains('increase-quantity') || target.classList.contains('decrease-quantity')) {
                        const quantityInput = document.querySelector(`.item-quantity-input[data-id="${itemId}"]`);
                        let newQuantity = parseInt(quantityInput.value);

                        if (target.classList.contains('increase-quantity')) {
                            newQuantity++;
                        } else if (target.classList.contains('decrease-quantity')) {
                            newQuantity--;
                        }

                        // No permitir cantidades negativas, si es < 0, se procesa como 0 (eliminar)
                        if (newQuantity < 0) newQuantity = 0; 

                        sendCartRequest('{{ route('cart.update') }}', 'POST', { id: itemId, quantity: newQuantity });
                    }
                    
                    if (target.classList.contains('remove-item')) {
                        // No usar confirm() directamente. Si necesitas confirmación, usa un modal.
                        // Por ahora, solo envía la petición. El backend maneja el resultado.
                        sendCartRequest('{{ route('cart.remove') }}', 'POST', { id: itemId });
                    }
                });
                
                cartItemsBody.addEventListener('change', function(event) {
                    const target = event.target;
                    if (target.classList.contains('item-quantity-input')) {
                        const itemId = target.dataset.id;
                        let newQuantity = parseInt(target.value);

                        if (isNaN(newQuantity) || newQuantity < 0) {
                            newQuantity = 1; 
                            target.value = 1; // Revertir el valor en el input
                        }

                        // Si la cantidad cambia a 0, se procesa como eliminación
                        if (newQuantity === 0) {
                            // No usar confirm() directamente. Si necesitas confirmación, usa un modal.
                            // Por ahora, solo envía la petición. El backend maneja el resultado.
                        }
                        sendCartRequest('{{ route('cart.update') }}', 'POST', { id: itemId, quantity: newQuantity });
                    }
                });
            }

            // Inicializar la UI con los datos que vienen del servidor (al cargar la página)
            // Aseguramos que los valores sean números.
            updateCartUI({
                cartItems: @json($cartItems),
                subtotal_net_products: parseFloat({{ $subtotal_net_products }}),
                iva_products_amount: parseFloat({{ $iva_products_amount }}),
                subtotal_gross_products: parseFloat({{ $subtotal_gross_products }}),
                mp_fee_amount: parseFloat({{ $mp_fee_amount }}),
                final_total: parseFloat({{ $final_total }}),
                cartCount: parseInt({{ $cartCount }})
            });
        });
    </script>
</body>
</html>